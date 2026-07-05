<?php

/**
 * OAuth 2.1 + PKCE consent screen (authorization endpoint, human step).
 *
 * This is the interactive half of the OAuth authorization-code flow. A public
 * client (e.g. a Claude custom connector) sends the user here with the standard
 * authorization-request query params; after the user signs in (admin login
 * wall) and approves, we mint a master API token and a one-time authorization
 * code, then 302-redirect back to the client's redirect_uri with ?code=...
 *
 * The machine half (code → token exchange, refresh) lives in the public OAuth
 * service tree: mwmod_mw_oauth_endpoints_token.
 *
 * Registered as subinterface 'oauthconsent' on the admin UI, e.g.:
 *     function create_subinterface_oauthconsent() {
 *         return new mwmod_mw_users_ui_oauthconsent_main("oauthconsent", $this);
 *     }
 *
 * Request (GET, OAuth authorization request):
 *   response_type=code
 *   client_id=<registered client_id>
 *   redirect_uri=<must be registered for the client>
 *   code_challenge=<PKCE S256 challenge>
 *   code_challenge_method=S256
 *   scope=<space-separated permission codes>   (optional)
 *   state=<opaque, echoed back>                (optional but recommended)
 *
 * On approve  → 302 redirect_uri?code=<code>&state=<state>
 * On deny     → 302 redirect_uri?error=access_denied&state=<state>
 *
 * Security:
 *   - client_id must exist and redirect_uri must be registered for it
 *     (exact match, no wildcards) BEFORE anything is shown or redirected.
 *   - Only PKCE S256 is accepted (plain is rejected).
 *   - CSRF-protected via a per-request session nonce.
 *   - The minted token is scoped to the intersection of the requested scope and
 *     the permissions the signed-in user actually holds; createToken() re-checks
 *     every code against the permission catalog.
 *   - Reuses mwmod_mw_users_apitoken_man::createToken() and
 *     mwmod_mw_oauth_authcode_man::create() — no business logic duplicated here.
 */
class mwmod_mw_users_ui_oauthconsent_main extends mwmod_mw_ui_base_basesubuia {

	/** Session key holding the CSRF nonce for the pending consent form. */
	const NONCE_SESSION_KEY = '_mw_oauthconsent_nonce';

	function __construct($cod, $parent) {
		$this->init_as_main_or_sub($cod, $parent);
		$this->set_def_title("Autorizar acceso");
	}

	function is_allowed() {
		return (bool) $this->get_current_user();
	}

	// ------------------------------------------------------------------
	// Request parameter access
	// ------------------------------------------------------------------

	private function reqParam($name) {
		$v = $_REQUEST[$name] ?? '';
		return is_string($v) ? trim($v) : '';
	}

	private function ensureSession() {
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
	}

	// ------------------------------------------------------------------
	// Execution
	// ------------------------------------------------------------------

	function do_exec_page_in() {
		$user = $this->get_current_user();
		if (!$user) {
			return; // login wall handled by the admin UI shell
		}

		$this->ensureSession();

		$clientId      = $this->reqParam('client_id');
		$redirectUri   = $this->reqParam('redirect_uri');
		$responseType  = $this->reqParam('response_type');
		$codeChallenge = $this->reqParam('code_challenge');
		$challengeMeth = $this->reqParam('code_challenge_method');
		$scopeRaw      = $this->reqParam('scope');
		$state         = $this->reqParam('state');

		$container = $this->get_ui_dom_elem_container();

		// OAuth must be enabled on this site (userman overrides createOauthMan()).
		$oauthMan = $user->man->getOauthMan();
		if (!$oauthMan) {
			$this->fatal($container, 'OAuth no está habilitado en este sitio.');
			return;
		}

		// --- Validate the client and redirect_uri FIRST (no open redirect) ---
		$clientMan = $oauthMan->getClientMan();
		$client = $clientMan->findByClientId($clientId);
		if (!$client) {
			$this->fatal($container, 'Cliente OAuth desconocido o no registrado.');
			return;
		}
		if (!$client->allowsRedirectUri($redirectUri)) {
			// redirect_uri not registered → NEVER redirect there; show an error.
			$this->fatal($container, 'La URL de redirección no está registrada para este cliente.');
			return;
		}

		// From here a bad request can be reported to the client via redirect.
		if ($responseType !== 'code') {
			$this->redirectError($redirectUri, 'unsupported_response_type', $state);
			return;
		}
		if ($codeChallenge === '' || strtoupper($challengeMeth) !== 'S256') {
			$this->redirectError($redirectUri, 'invalid_request', $state);
			return;
		}

		$requestedScope = $this->parseScope($scopeRaw);

		// --- Handle POST (user decision) ---
		$action = $_POST['_oauth_action'] ?? '';
		if ($action !== '') {
			$this->handleDecision($container, $user, $client, $redirectUri,
				$codeChallenge, $requestedScope, $state, $action);
			return;
		}

		// --- GET: render the consent form ---
		$this->renderConsent($container, $user, $client, $redirectUri,
			$requestedScope, $state);
	}

	// ------------------------------------------------------------------
	// Decision handling (POST)
	// ------------------------------------------------------------------

	private function handleDecision($container, $user, $client, $redirectUri,
			$codeChallenge, array $requestedScope, $state, $action) {

		// CSRF check.
		$nonce = $_POST['_oauth_nonce'] ?? '';
		$expected = $_SESSION[self::NONCE_SESSION_KEY] ?? '';
		unset($_SESSION[self::NONCE_SESSION_KEY]);
		if ($nonce === '' || $expected === '' || !hash_equals($expected, $nonce)) {
			$this->fatal($container, 'Error de seguridad (nonce). Vuelve a intentarlo.');
			return;
		}

		if ($action !== 'approve') {
			// Explicit denial → tell the client per RFC 6749 §4.1.2.1.
			$this->redirectError($redirectUri, 'access_denied', $state);
			return;
		}

		// Scope actually granted = requested ∩ user's real permissions.
		$grantedScope = [];
		foreach ($requestedScope as $code) {
			if ($user->man->allow($code)) {
				$grantedScope[] = $code;
			}
		}
		if (empty($grantedScope)) {
			$this->fatal($container, 'No tienes ninguno de los permisos solicitados.');
			return;
		}

		$apitokenMan = $user->man->getApitokenMan();
		if (!$apitokenMan) {
			$this->fatal($container, 'El sistema de tokens no está disponible.');
			return;
		}

		// Master API token: the HMAC anchor for all derived access/refresh tokens.
		$label = 'OAuth: ' . $client->getName();
		$created = $apitokenMan->createToken($user->get_id(), $label, $grantedScope);
		if (!$created || empty($created['item'])) {
			$this->fatal($container, 'No se pudo crear el token de acceso.');
			return;
		}
		$masterToken = $created['item'];

		// One-time authorization code bound to client + redirect_uri + PKCE.
		$oauthMan = $user->man->getOauthMan();
		if (!$oauthMan) {
			$this->fatal($container, 'OAuth no está habilitado en este sitio.');
			return;
		}
		$authCodeMan = $oauthMan->getAuthCodeMan();
		$code = $authCodeMan->create(
			$client->getClientId(),
			$masterToken->get_id(),
			$redirectUri,
			$codeChallenge
		);
		if (!$code) {
			$this->fatal($container, 'No se pudo generar el código de autorización.');
			return;
		}

		// Success: hand the code back to the client.
		$this->redirectCode($redirectUri, $code, $state);
	}

	// ------------------------------------------------------------------
	// Redirect helpers
	// ------------------------------------------------------------------

	private function redirectCode($redirectUri, $code, $state) {
		$sep = (strpos($redirectUri, '?') === false) ? '?' : '&';
		$url = $redirectUri . $sep . 'code=' . urlencode($code);
		if ($state !== '') {
			$url .= '&state=' . urlencode($state);
		}
		ob_end_clean();
		header('Location: ' . $url);
		exit;
	}

	private function redirectError($redirectUri, $error, $state) {
		$sep = (strpos($redirectUri, '?') === false) ? '?' : '&';
		$url = $redirectUri . $sep . 'error=' . urlencode($error);
		if ($state !== '') {
			$url .= '&state=' . urlencode($state);
		}
		ob_end_clean();
		header('Location: ' . $url);
		exit;
	}

	// ------------------------------------------------------------------
	// Rendering
	// ------------------------------------------------------------------

	private function renderConsent($container, $user, $client, $redirectUri,
			array $requestedScope, $state) {

		// Fresh CSRF nonce for this form.
		$nonce = bin2hex(random_bytes(16));
		$_SESSION[self::NONCE_SESSION_KEY] = $nonce;

		$scopeInfo  = $this->buildScopeInfo($requestedScope, $user);
		$hasWarning = false;
		foreach ($scopeInfo as $info) {
			if ($info['status'] !== 'ok') {
				$hasWarning = true;
			}
		}

		$safeClient   = htmlspecialchars($client->getName());
		$safeRedirect = htmlspecialchars($redirectUri);

		$card = $container->add_cont_elem(false, 'div');
		$card->set_att('class', 'card');
		$cardBody = $card->add_cont_elem(false, 'div');
		$cardBody->set_att('class', 'card-body');

		$title = $cardBody->add_cont_elem(false, 'h5');
		$title->set_att('class', 'card-title');
		$title->addCont('<i class="fa fa-key me-2"></i>Solicitud de acceso');

		$info = $cardBody->add_cont_elem(false, 'p');
		$info->set_att('class', 'text-muted small mb-2');
		$info->addCont('<strong>' . $safeClient . '</strong> solicita acceso a tu cuenta.');

		$dest = $cardBody->add_cont_elem(false, 'p');
		$dest->set_att('class', 'text-muted small mb-3');
		$dest->addCont('Se redirigirá a: <code>' . $safeRedirect . '</code>');

		if ($hasWarning) {
			$w = $cardBody->add_cont_elem(false, 'div');
			$w->set_att('class', 'alert alert-warning small');
			$w->addCont('<i class="fa fa-exclamation-triangle me-1"></i>'
				. 'Algunos permisos solicitados no están disponibles para tu cuenta. '
				. 'El acceso se concederá solo con los permisos que sí tienes.');
		}

		$listTitle = $cardBody->add_cont_elem(false, 'p');
		$listTitle->set_att('class', 'fw-bold mb-1');
		$listTitle->addCont('Permisos solicitados:');

		$ul = $cardBody->add_cont_elem(false, 'ul');
		$ul->set_att('class', 'list-group mb-3');
		if (empty($scopeInfo)) {
			$li = $ul->add_cont_elem(false, 'li');
			$li->set_att('class', 'list-group-item text-muted small');
			$li->addCont('El cliente no solicitó permisos específicos.');
		}
		foreach ($scopeInfo as $si) {
			$safeCode = htmlspecialchars($si['code']);
			$li = $ul->add_cont_elem(false, 'li');
			$li->set_att('class', 'list-group-item d-flex align-items-center gap-2');
			switch ($si['status']) {
				case 'ok':
					$li->addCont('<i class="fa fa-check-circle text-success"></i> <code>' . $safeCode . '</code>');
					break;
				case 'warning':
					$li->addCont('<i class="fa fa-times-circle text-danger"></i> <code>' . $safeCode . '</code>'
						. ' <span class="text-danger small">(no disponible para tu cuenta)</span>');
					break;
				default:
					$li->addCont('<i class="fa fa-question-circle text-warning"></i> <code>' . $safeCode . '</code>'
						. ' <span class="text-warning small">(permiso desconocido)</span>');
			}
		}

		$form = $cardBody->add_cont_elem(false, 'form');
		$form->set_att('method', 'post');

		// Preserve the OAuth request params across the POST.
		$this->addHidden($form, '_oauth_nonce', $nonce);
		$this->addHidden($form, 'client_id', $client->getClientId());
		$this->addHidden($form, 'redirect_uri', $redirectUri);
		$this->addHidden($form, 'response_type', 'code');
		$this->addHidden($form, 'code_challenge', $this->reqParam('code_challenge'));
		$this->addHidden($form, 'code_challenge_method', 'S256');
		$this->addHidden($form, 'scope', $this->reqParam('scope'));
		$this->addHidden($form, 'state', $state);

		$btns = $form->add_cont_elem(false, 'div');
		$btns->set_att('class', 'd-flex gap-2');

		$btnApprove = $btns->add_cont_elem(false, 'button');
		$btnApprove->set_att('type', 'submit');
		$btnApprove->set_att('name', '_oauth_action');
		$btnApprove->set_att('value', 'approve');
		$btnApprove->set_att('class', 'btn btn-primary');
		$btnApprove->addCont('<i class="fa fa-check me-1"></i>Autorizar');

		$btnDeny = $btns->add_cont_elem(false, 'button');
		$btnDeny->set_att('type', 'submit');
		$btnDeny->set_att('name', '_oauth_action');
		$btnDeny->set_att('value', 'deny');
		$btnDeny->set_att('class', 'btn btn-outline-secondary');
		$btnDeny->addCont('<i class="fa fa-times me-1"></i>Denegar');

		$container->do_output();
	}

	private function addHidden($form, $name, $value) {
		$input = $form->add_cont_elem(false, 'input');
		$input->set_att('type', 'hidden');
		$input->set_att('name', $name);
		$input->set_att('value', htmlspecialchars((string) $value));
	}

	private function fatal($container, $msg) {
		$alert = $container->add_cont_elem(false, 'div');
		$alert->set_att('class', 'alert alert-danger');
		$alert->addCont(htmlspecialchars($msg));
		$container->do_output();
	}

	// ------------------------------------------------------------------
	// Scope helpers
	// ------------------------------------------------------------------

	/**
	 * Split a space-separated scope string into a de-duplicated code list.
	 * @return string[]
	 */
	private function parseScope($scopeRaw) {
		if ($scopeRaw === '') {
			return [];
		}
		$parts = preg_split('/\s+/', $scopeRaw);
		$out = [];
		foreach ($parts as $p) {
			$p = trim($p);
			if ($p !== '') {
				$out[$p] = true;
			}
		}
		return array_keys($out);
	}

	/**
	 * Classify each requested scope for display:
	 *   'ok'      — user holds the permission
	 *   'warning' — code exists in the catalog but the user lacks it
	 *   'unknown' — code is not in the catalog
	 *
	 * @param string[] $scope
	 * @param mwmod_mw_users_user $user
	 * @return array<int, array{code:string, status:string}>
	 */
	private function buildScopeInfo(array $scope, $user) {
		$permMan = $user->man->get_permission_man();
		$catalog = [];
		if ($permMan && ($items = $permMan->get_items())) {
			foreach ($items as $p) {
				$catalog[$p->get_code()] = true;
			}
		}
		$result = [];
		foreach ($scope as $code) {
			if (!isset($catalog[$code])) {
				$status = 'unknown';
			} elseif ($user->man->allow($code)) {
				$status = 'ok';
			} else {
				$status = 'warning';
			}
			$result[] = ['code' => $code, 'status' => $status];
		}
		return $result;
	}
}
