<?php

/**
 * OAuth 2.1 + PKCE consent screen (authorization endpoint, human step).
 *
 * This is the interactive half of the OAuth authorization-code flow. A public
 * client (e.g. a Claude custom connector) sends the user here with the standard
 * authorization-request query params; after the user signs in (admin login
 * wall) and approves, we mint a master API token and a one-time authorization
 * code, then redirect back to the client's redirect_uri with ?code=...
 *
 * The machine half (code -> token exchange, refresh) lives in the public OAuth
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
 *   resource=<protected resource URI>          (optional, displayed for UX)
 *
 * UX flow (client-side, driven by mw_ui_oauthconsent):
 *   1. GET renders the consent form. The user edits the token name, the token
 *      expiration and the exact permissions to grant.
 *   2. On "Authorize" the JS posts to the `authorize` sxml endpoint, which
 *      mints the master token + one-time authorization code (no full page
 *      reload). The response carries the created token, the destination URL
 *      and where the code will be delivered.
 *   3. The result panel shows the created token and the destination; a SECOND
 *      confirmation ("Send code") performs the actual redirect to the client.
 *   4. "Deny" posts action=deny and the JS redirects to redirect_uri with
 *      error=access_denied.
 *
 * Security:
 *   - client_id must exist and redirect_uri must be registered for it
 *     (exact match, no wildcards) BEFORE anything is shown or redirected.
 *   - Only PKCE S256 is accepted (plain is rejected).
 *   - The whole OAuth request context is stored server-side in the session and
 *     keyed by a single-use nonce. The AJAX endpoint NEVER trusts a
 *     client-submitted redirect_uri / code_challenge: it reads them back from
 *     that server-side context and re-validates the client on every call.
 *   - The minted token is scoped to the intersection of the permissions the
 *     user checked AND the permissions the user actually holds AND the
 *     permissions originally requested by the client.
 *   - Reuses mwmod_mw_users_apitoken_man::createToken() and
 *     mwmod_mw_oauth_authcode_man::create() - no business logic duplicated here.
 */
class mwmod_mw_users_ui_oauthconsent_main extends mwmod_mw_ui_base_basesubuia {

	/** Session key holding the pending consent request contexts, keyed by nonce. */
	const CTX_SESSION_KEY = '_mw_oauthconsent_ctx';

	/** Lifetime (seconds) of a pending consent request context. */
	const CTX_TTL = 600;

	function __construct($cod, $parent) {
		$this->init_as_main_or_sub($cod, $parent);
		$this->set_def_title($this->lng_get_msg_txt('authorize_access_title', 'Autorizar acceso'));
		$this->js_ui_class_name = 'mw_ui_oauthconsent';
	}

	function is_allowed() {
		if (!$this->allow('owntoken')) {
			return false;
		}
		if (!$user = $this->get_current_user()) {
			return false;
		}
		return $user->man->getApitokenMan();
	}

	// ------------------------------------------------------------------
	// Request parameter access
	// ------------------------------------------------------------------

	/**
	 * Read an OAuth authorization-request parameter from the GET query string.
	 * The consent form is always reached via a GET authorization request.
	 */
	private function reqParam($name) {
		$v = $_GET[$name] ?? '';
		return is_string($v) ? trim($v) : '';
	}

	/** Read a POST field submitted by the AJAX consent form. */
	private function postParam($name) {
		$v = $_POST[$name] ?? '';
		return is_string($v) ? trim($v) : '';
	}

	private function ensureSession() {
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}
	}

	// ------------------------------------------------------------------
	// UI preparation (load JS)
	// ------------------------------------------------------------------

	function prepare_before_exec_no_sub_interface() {
		$jsman = $this->maininterface->jsmanager;
		$jsman->add_item_by_cod('/res/js/util.js');
		$jsman->add_item_by_cod('/res/js/url.js');
		$jsman->add_item_by_cod('/res/js/ajax.js');
		$jsman->add_item_by_cod('/res/js/ui/mwui.js');
		$jsman->add_item_by_cod('/res/js/ui/mwui_oauthconsent.js');

		$item = $this->create_js_man_ui_header_declaration_item();
		$jsman->add_item_by_item($item);
	}

	// ------------------------------------------------------------------
	// Execution (GET: render the consent form)
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
		$resource      = $this->reqParam('resource');

		$container = $this->get_ui_dom_elem_container();

		// OAuth must be enabled on this site (userman overrides createOauthMan()).
		$oauthMan = $user->man->getOauthMan();
		if (!$oauthMan) {
			$this->fatal($container, $this->lng_get_msg_txt('oauth_disabled', 'OAuth no está habilitado en este sitio.'));
			return;
		}

		// --- Validate the client and redirect_uri FIRST (no open redirect) ---
		$clientMan = $oauthMan->getClientMan();
		$client = $clientMan->findByClientId($clientId);
		if (!$client) {
			$this->fatal($container, $this->lng_get_msg_txt('unknown_client', 'Cliente OAuth desconocido o no registrado.'));
			return;
		}
		if (!$client->allowsRedirectUri($redirectUri)) {
			// redirect_uri not registered -> NEVER redirect there; show an error.
			$this->fatal($container, $this->lng_get_msg_txt('invalid_redirect_uri', 'La URL de redirección no está registrada para este cliente.'));
			return;
		}

		// From here a bad request can be reported to the client via redirect.
		if ($responseType !== 'code') {
			$this->renderClientErrorRedirect($container, $redirectUri, 'unsupported_response_type', $state);
			return;
		}
		if ($codeChallenge === '' || strtoupper($challengeMeth) !== 'S256') {
			$this->renderClientErrorRedirect($container, $redirectUri, 'invalid_request', $state);
			return;
		}

		$requestedScope = $this->parseScope($scopeRaw);

		// Persist the validated OAuth request context server-side, keyed by a
		// single-use nonce. The AJAX endpoint reads it back from here so the
		// browser can never tamper with redirect_uri / code_challenge / scope.
		$nonce = $this->storeContext([
			'client_id'      => $client->getClientId(),
			'redirect_uri'   => $redirectUri,
			'code_challenge' => $codeChallenge,
			'scope'          => $requestedScope,
			'state'          => $state,
			'resource'       => $resource,
		]);

		$this->renderConsent($container, $user, $client, $redirectUri,
			$requestedScope, $resource, $nonce);
	}

	// ------------------------------------------------------------------
	// AJAX endpoint: mint token + code (approve) or build deny redirect
	// ------------------------------------------------------------------

	/**
	 * Consent decision endpoint. POST fields:
	 *   _oauth_nonce             single-use nonce identifying the pending request
	 *   _oauth_action            "approve" | "deny"
	 *   _oauth_scope             comma-separated permission codes the user checked
	 *   _oauth_token_label       custom token name
	 *   _oauth_token_expiry_days preset expiry (0 = no expiry)
	 *
	 * On approve the response carries: token (plaintext JWT), token_label,
	 * expires_at, redirect_uri and redirect_url (the final URL with ?code=...).
	 * The browser then performs the redirect after a second confirmation.
	 */
	function execfrommain_getcmd_sxml_authorize($params = array(), $filename = false) {
		$xml = $this->new_getcmd_sxml_answer(false);

		$user = $this->get_current_user();
		if (!$user) {
			$xml->set_prop('error', 'not_authenticated');
			$xml->set_prop('msg', $this->lng_get_msg_txt('session_required', 'Debes iniciar sesión para continuar.'));
			$xml->root_do_all_output();
			return;
		}

		$this->ensureSession();

		$nonce = $this->postParam('_oauth_nonce');
		$ctx = $this->getContext($nonce);
		if (!$ctx) {
			$xml->set_prop('error', 'nonce_error');
			$xml->set_prop('msg', $this->lng_get_msg_txt('nonce_error', 'Error de seguridad (nonce). Vuelve a intentarlo.'));
			$xml->root_do_all_output();
			return;
		}

		$redirectUri = $ctx['redirect_uri'];
		$state       = $ctx['state'];

		// Re-validate OAuth + client + redirect_uri from the trusted context.
		$oauthMan = $user->man->getOauthMan();
		if (!$oauthMan) {
			$xml->set_prop('error', 'oauth_disabled');
			$xml->set_prop('msg', $this->lng_get_msg_txt('oauth_disabled', 'OAuth no está habilitado en este sitio.'));
			$xml->root_do_all_output();
			return;
		}
		$client = $oauthMan->getClientMan()->findByClientId($ctx['client_id']);
		if (!$client || !$client->allowsRedirectUri($redirectUri)) {
			$xml->set_prop('error', 'invalid_client');
			$xml->set_prop('msg', $this->lng_get_msg_txt('unknown_client', 'Cliente OAuth desconocido o no registrado.'));
			$xml->root_do_all_output();
			return;
		}

		$action = $this->postParam('_oauth_action');

		// --- Explicit denial: consume the nonce and hand the error back. ---
		if ($action === 'deny') {
			$this->consumeContext($nonce);
			$xml->set_prop('ok', true);
			$xml->set_prop('action', 'deny');
			$xml->set_prop('redirect_uri', $redirectUri);
			$xml->set_prop('redirect_url', $this->buildErrorUrl($redirectUri, 'access_denied', $state));
			$xml->root_do_all_output();
			return;
		}

		if ($action !== 'approve') {
			$xml->set_prop('error', 'invalid_action');
			$xml->set_prop('msg', $this->lng_get_msg_txt('invalid_action', 'Acción no válida.'));
			$xml->root_do_all_output();
			return;
		}

		// Scope: intersection of (checked) ∩ (originally requested) ∩ (user holds).
		$requestedScope = is_array($ctx['scope']) ? $ctx['scope'] : [];
		$submittedScope = $this->parseScope($this->postParam('_oauth_scope'), ',');
		$grantedScope = [];
		foreach ($submittedScope as $code) {
			if (in_array($code, $requestedScope, true) && $user->man->allow($code)) {
				$grantedScope[] = $code;
			}
		}
		if (empty($grantedScope)) {
			$xml->set_prop('error', 'no_permissions');
			$xml->set_prop('msg', $this->lng_get_msg_txt('no_permissions_granted', 'Debes conceder al menos un permiso para continuar.'));
			$xml->root_do_all_output();
			return;
		}

		$apitokenMan = $user->man->getApitokenMan();
		if (!$apitokenMan) {
			$xml->set_prop('error', 'token_system_unavailable');
			$xml->set_prop('msg', $this->lng_get_msg_txt('token_system_unavailable', 'El sistema de tokens no está disponible.'));
			$xml->root_do_all_output();
			return;
		}

		// Token label: user can customize it.
		$rawLabel = $this->postParam('_oauth_token_label');
		$label = ($rawLabel !== '') ? mb_substr($rawLabel, 0, 200) : ('OAuth: ' . $client->getName());

		// Expiration: preset days (0 or missing = unlimited).
		$rawExpiry = intval($_POST['_oauth_token_expiry_days'] ?? 0);
		$expiresInDays = ($rawExpiry > 0) ? $rawExpiry : null;

		// Master API token: the HMAC anchor for all derived access/refresh tokens.
		$created = $apitokenMan->createToken($user->get_id(), $label, $grantedScope, $expiresInDays);
		if (!$created || empty($created['item'])) {
			$xml->set_prop('error', 'token_creation_failed');
			$xml->set_prop('msg', $this->lng_get_msg_txt('token_creation_failed', 'No se pudo crear el token de acceso.'));
			$xml->root_do_all_output();
			return;
		}
		$masterToken = $created['item'];

		// One-time authorization code bound to client + redirect_uri + PKCE.
		$authCodeMan = $oauthMan->getAuthCodeMan();
		$code = $authCodeMan->create(
			$client->getClientId(),
			$masterToken->get_id(),
			$redirectUri,
			$ctx['code_challenge']
		);
		if (!$code) {
			$xml->set_prop('error', 'authcode_creation_failed');
			$xml->set_prop('msg', $this->lng_get_msg_txt('authcode_creation_failed', 'No se pudo generar el código de autorización.'));
			$xml->root_do_all_output();
			return;
		}

		// Success: the nonce is single-use, consume it now.
		$this->consumeContext($nonce);

		$expiresAt = ($expiresInDays !== null)
			? date('Y-m-d H:i:s', strtotime('+' . (int) $expiresInDays . ' days'))
			: '';

		$xml->set_prop('ok', true);
		$xml->set_prop('action', 'approve');
		$xml->set_prop('token', $created['token']);
		$xml->set_prop('token_label', $label);
		$xml->set_prop('expires_at', $expiresAt);
		$xml->set_prop('granted_scope', implode(' ', $grantedScope));
		$xml->set_prop('redirect_uri', $redirectUri);
		$xml->set_prop('redirect_url', $this->buildCodeUrl($redirectUri, $code, $state));
		$xml->root_do_all_output();
	}

	// ------------------------------------------------------------------
	// Session-side request context (single-use, nonce-keyed)
	// ------------------------------------------------------------------

	private function storeContext(array $ctx) {
		$nonce = bin2hex(random_bytes(16));
		$ctx['created'] = time();

		$all = $_SESSION[self::CTX_SESSION_KEY] ?? [];
		if (!is_array($all)) {
			$all = [];
		}
		// Prune expired contexts to keep the session small.
		$now = time();
		foreach ($all as $k => $c) {
			if (!isset($c['created']) || ($now - $c['created']) > self::CTX_TTL) {
				unset($all[$k]);
			}
		}
		$all[$nonce] = $ctx;
		$_SESSION[self::CTX_SESSION_KEY] = $all;
		return $nonce;
	}

	private function getContext($nonce) {
		if ($nonce === '' || !isset($_SESSION[self::CTX_SESSION_KEY][$nonce])) {
			return false;
		}
		$ctx = $_SESSION[self::CTX_SESSION_KEY][$nonce];
		if (!isset($ctx['created']) || (time() - $ctx['created']) > self::CTX_TTL) {
			$this->consumeContext($nonce);
			return false;
		}
		return $ctx;
	}

	private function consumeContext($nonce) {
		if (isset($_SESSION[self::CTX_SESSION_KEY][$nonce])) {
			unset($_SESSION[self::CTX_SESSION_KEY][$nonce]);
		}
	}

	// ------------------------------------------------------------------
	// Redirect URL builders (return strings; the browser does the redirect)
	// ------------------------------------------------------------------

	private function buildCodeUrl($redirectUri, $code, $state) {
		$sep = (strpos($redirectUri, '?') === false) ? '?' : '&';
		$url = $redirectUri . $sep . 'code=' . urlencode($code);
		if ($state !== '') {
			$url .= '&state=' . urlencode($state);
		}
		return $url;
	}

	private function buildErrorUrl($redirectUri, $error, $state) {
		$sep = (strpos($redirectUri, '?') === false) ? '?' : '&';
		$url = $redirectUri . $sep . 'error=' . urlencode($error);
		if ($state !== '') {
			$url .= '&state=' . urlencode($state);
		}
		return $url;
	}

	// ------------------------------------------------------------------
	// Rendering
	// ------------------------------------------------------------------

	private function renderConsent($container, $user, $client, $redirectUri,
			array $requestedScope, $resource, $nonce) {

		$scopeInfo = $this->buildScopeInfo($requestedScope, $user);

		$hasGrantable = false;
		$hasUnavailable = false;
		foreach ($scopeInfo as $info) {
			if ($info['status'] === 'ok') {
				$hasGrantable = true;
			} else {
				$hasUnavailable = true;
			}
		}

		$safeClient   = htmlspecialchars($client->getName());
		$safeRedirect = htmlspecialchars($redirectUri);
		$safeResource = htmlspecialchars($resource);
		$defaultLabel = 'OAuth: ' . $client->getName();

		$card = $container->add_cont_elem(false, 'div');
		$card->set_att('class', 'card');
		$cardBody = $card->add_cont_elem(false, 'div');
		$cardBody->set_att('class', 'card-body');

		// ---- Consent form panel (step 1) ----
		$formPanel = $this->set_ui_dom_elem_id('formpanel', 'div');
		$cardBody->add_cont($formPanel);

		$title = $formPanel->add_cont_elem(false, 'h5');
		$title->set_att('class', 'card-title');
		$title->addCont('<i class="fa fa-key me-2"></i>'
			. $this->lng_get_msg_txt('access_request_title', 'Solicitud de acceso'));

		$info = $formPanel->add_cont_elem(false, 'p');
		$info->set_att('class', 'mb-2');
		$info->addCont('<strong>' . $safeClient . '</strong> '
			. $this->lng_get_msg_txt('requests_access', 'solicita acceso a tu cuenta.'));

		if ($resource !== '') {
			$resourceBlock = $formPanel->add_cont_elem(false, 'div');
			$resourceBlock->set_att('class', 'alert alert-info py-2 mb-2');
			$resourceBlock->addCont(
				'<i class="fa fa-server me-2"></i>'
				. '<strong>' . $this->lng_get_msg_txt('resource_label', 'Acceso a:') . '</strong> '
				. '<code>' . $safeResource . '</code>'
			);
		}

		$dest = $formPanel->add_cont_elem(false, 'p');
		$dest->set_att('class', 'text-muted small mb-3');
		$dest->addCont(
			'<i class="fa fa-arrow-right me-1"></i>'
			. $this->lng_get_msg_txt('redirect_to_label', 'El código de autorización se enviará a:')
			. ' <code>' . $safeRedirect . '</code>'
		);

		// Permissions section with individual checkboxes.
		$listTitle = $formPanel->add_cont_elem(false, 'p');
		$listTitle->set_att('class', 'fw-bold mb-1');
		$listTitle->addCont($this->lng_get_msg_txt('requested_permissions', 'Permisos solicitados:'));

		if ($hasUnavailable) {
			$warnNote = $formPanel->add_cont_elem(false, 'p');
			$warnNote->set_att('class', 'text-muted small mb-1');
			$warnNote->addCont('<i class="fa fa-info-circle me-1"></i>'
				. $this->lng_get_msg_txt('unavailable_permissions_note',
					'Los permisos en gris no están disponibles para tu cuenta y no se concederán.'));
		}

		$scopeList = $this->set_ui_dom_elem_id('scopelist', 'ul');
		$scopeList->set_att('class', 'list-group mb-3');
		$formPanel->add_cont($scopeList);

		if (empty($scopeInfo)) {
			$li = $scopeList->add_cont_elem(false, 'li');
			$li->set_att('class', 'list-group-item text-muted small');
			$li->addCont($this->lng_get_msg_txt('no_specific_permissions', 'El cliente no solicitó permisos específicos.'));
		}

		foreach ($scopeInfo as $si) {
			$safeCode = htmlspecialchars($si['code']);
			$isGrantable = ($si['status'] === 'ok');

			$li = $scopeList->add_cont_elem(false, 'li');
			$li->set_att('class', 'list-group-item' . ($isGrantable ? '' : ' text-muted'));

			$label = $li->add_cont_elem(false, 'label');
			$label->set_att('class', 'd-flex align-items-center gap-2 mb-0'
				. ($isGrantable ? '' : ' opacity-50'));
			$label->set_att('style', 'cursor:' . ($isGrantable ? 'pointer' : 'not-allowed'));

			$cb = $label->add_cont_elem(false, 'input');
			$cb->set_att('type', 'checkbox');
			$cb->set_att('name', '_oauth_scope[]');
			$cb->set_att('value', $si['code']);
			if ($isGrantable) {
				$cb->set_att('checked', 'checked');
			} else {
				$cb->set_att('disabled', 'disabled');
			}

			switch ($si['status']) {
				case 'ok':
					$label->addCont('<i class="fa fa-check-circle text-success"></i> <code>' . $safeCode . '</code>');
					break;
				case 'warning':
					$label->addCont('<i class="fa fa-times-circle text-danger"></i> <code>' . $safeCode . '</code>'
						. ' <span class="small">('
						. $this->lng_get_msg_txt('permission_not_available', 'no disponible para tu cuenta')
						. ')</span>');
					break;
				default:
					$label->addCont('<i class="fa fa-question-circle text-warning"></i> <code>' . $safeCode . '</code>'
						. ' <span class="small">('
						. $this->lng_get_msg_txt('unknown_permission', 'permiso desconocido')
						. ')</span>');
			}
		}

		// Token customization card.
		$tokenCard = $formPanel->add_cont_elem(false, 'div');
		$tokenCard->set_att('class', 'card bg-light mb-3');
		$tokenCardBody = $tokenCard->add_cont_elem(false, 'div');
		$tokenCardBody->set_att('class', 'card-body py-2');

		$tokenTitle = $tokenCardBody->add_cont_elem(false, 'p');
		$tokenTitle->set_att('class', 'fw-bold mb-2 small text-muted');
		$tokenTitle->addCont('<i class="fa fa-tag me-1"></i>'
			. $this->lng_get_msg_txt('token_settings_title', 'Configuración del token'));

		// Token name.
		$labelGroup = $tokenCardBody->add_cont_elem(false, 'div');
		$labelGroup->set_att('class', 'mb-2');
		$labelLbl = $labelGroup->add_cont_elem(false, 'label');
		$labelLbl->set_att('class', 'form-label form-label-sm mb-1');
		$labelLbl->addCont($this->lng_get_msg_txt('token_name_label', 'Nombre del token'));
		$labelInput = $labelGroup->add_cont_elem(false, 'input');
		$labelInput->set_att('id', $this->get_ui_elem_id_and_set_js_init_param('tokenlabel'));
		$labelInput->set_att('type', 'text');
		$labelInput->set_att('class', 'form-control form-control-sm');
		$labelInput->set_att('value', htmlspecialchars($defaultLabel));
		$labelInput->set_att('maxlength', '200');

		// Expiration.
		$expiryGroup = $tokenCardBody->add_cont_elem(false, 'div');
		$expiryGroup->set_att('class', 'mb-0');
		$expiryLbl = $expiryGroup->add_cont_elem(false, 'label');
		$expiryLbl->set_att('class', 'form-label form-label-sm mb-1');
		$expiryLbl->addCont($this->lng_get_msg_txt('token_expiry_label', 'Expiración'));
		$expirySelect = $expiryGroup->add_cont_elem(false, 'select');
		$expirySelect->set_att('id', $this->get_ui_elem_id_and_set_js_init_param('tokenexpiry'));
		$expirySelect->set_att('class', 'form-select form-select-sm');
		$expiryOptions = [
			0   => $this->lng_get_msg_txt('expiry_no_expiry', 'Sin expiración'),
			7   => $this->lng_get_msg_txt('expiry_7_days', '7 días'),
			30  => $this->lng_get_msg_txt('expiry_30_days', '30 días'),
			90  => $this->lng_get_msg_txt('expiry_90_days', '90 días'),
			365 => $this->lng_get_msg_txt('expiry_365_days', '1 año'),
		];
		foreach ($expiryOptions as $days => $text) {
			$opt = $expirySelect->add_cont_elem(false, 'option');
			$opt->set_att('value', (string) $days);
			$opt->addCont(htmlspecialchars($text));
		}

		// Action buttons (wired by the JS UI, no native form submit).
		$btns = $formPanel->add_cont_elem(false, 'div');
		$btns->set_att('class', 'd-flex gap-2');

		$btnApprove = $btns->add_cont_elem(false, 'button');
		$btnApprove->set_att('id', $this->get_ui_elem_id_and_set_js_init_param('btnapprove'));
		$btnApprove->set_att('type', 'button');
		$btnApprove->set_att('class', 'btn btn-primary' . ($hasGrantable ? '' : ' disabled'));
		if (!$hasGrantable) {
			$btnApprove->set_att('disabled', 'disabled');
		}
		$btnApprove->addCont('<i class="fa fa-check me-1"></i>'
			. $this->lng_get_msg_txt('authorize_btn', 'Autorizar'));

		$btnDeny = $btns->add_cont_elem(false, 'button');
		$btnDeny->set_att('id', $this->get_ui_elem_id_and_set_js_init_param('btndeny'));
		$btnDeny->set_att('type', 'button');
		$btnDeny->set_att('class', 'btn btn-outline-secondary');
		$btnDeny->addCont('<i class="fa fa-times me-1"></i>'
			. $this->lng_get_msg_txt('deny_btn', 'Denegar'));

		// ---- Result panel (step 2, hidden until the token is minted) ----
		$resultPanel = $this->set_ui_dom_elem_id('resultpanel', 'div');
		$resultPanel->set_style('display', 'none');
		$cardBody->add_cont($resultPanel);

		$resTitle = $resultPanel->add_cont_elem(false, 'h5');
		$resTitle->set_att('class', 'card-title');
		$resTitle->addCont('<i class="fa fa-check-circle text-success me-2"></i>'
			. $this->lng_get_msg_txt('token_created_title', 'Token creado'));

		$resIntro = $resultPanel->add_cont_elem(false, 'p');
		$resIntro->set_att('class', 'mb-2');
		$resIntro->addCont($this->lng_get_msg_txt('token_created_intro',
			'Guarda este token en un lugar seguro. No podrás volver a verlo.'));

		// Token value + copy button.
		$tokenBox = $resultPanel->add_cont_elem(false, 'div');
		$tokenBox->set_att('class', 'input-group input-group-sm mb-3');
		$tokenOut = $tokenBox->add_cont_elem(false, 'input');
		$tokenOut->set_att('id', $this->get_ui_elem_id_and_set_js_init_param('tokenout'));
		$tokenOut->set_att('type', 'text');
		$tokenOut->set_att('class', 'form-control font-monospace');
		$tokenOut->set_att('readonly', 'readonly');
		$btnCopy = $tokenBox->add_cont_elem(false, 'button');
		$btnCopy->set_att('id', $this->get_ui_elem_id_and_set_js_init_param('btncopy'));
		$btnCopy->set_att('type', 'button');
		$btnCopy->set_att('class', 'btn btn-outline-secondary');
		$btnCopy->addCont('<i class="fa fa-copy"></i>');

		// Destination hint (the URL text is injected by the JS from the response).
		$destBlock = $resultPanel->add_cont_elem(false, 'div');
		$destBlock->set_att('class', 'alert alert-info py-2 mb-3');
		$destBlock->addCont('<i class="fa fa-arrow-right me-2"></i>'
			. $this->lng_get_msg_txt('code_will_be_sent_to', 'El código de autorización se enviará a:')
			. ' <code id="' . htmlspecialchars($this->get_ui_elem_id_and_set_js_init_param('desturl')) . '"></code>');

		// Second confirmation button.
		$resBtns = $resultPanel->add_cont_elem(false, 'div');
		$resBtns->set_att('class', 'd-flex gap-2');

		$btnSend = $resBtns->add_cont_elem(false, 'button');
		$btnSend->set_att('id', $this->get_ui_elem_id_and_set_js_init_param('btnsend'));
		$btnSend->set_att('type', 'button');
		$btnSend->set_att('class', 'btn btn-primary');
		$btnSend->addCont('<i class="fa fa-paper-plane me-1"></i>'
			. $this->lng_get_msg_txt('send_code_btn', 'Enviar código a la aplicación'));

		$container->set_att('id', $this->get_ui_elem_id_and_set_js_init_param('container'));
		$container->do_output();

		// ---- Init the JS UI ----
		$js = new mwmod_mw_jsobj_jquery_docreadyfnc();
		$p = $this->__get_priv_ui_js_init_params();
		$p->set_prop('nonce', $nonce);
		$p->set_prop('msg_select_one', $this->lng_get_msg_txt('no_permissions_granted', 'Debes conceder al menos un permiso para continuar.'));
		$p->set_prop('msg_creating', $this->lng_get_msg_txt('creating_token', 'Creando token...'));
		$p->set_prop('msg_error_generic', $this->lng_get_msg_txt('error_generic', 'Ocurrió un error. Vuelve a intentarlo.'));
		$p->set_prop('msg_copied', $this->lng_get_msg_txt('token_copied', 'Token copiado al portapapeles.'));
		$p->set_prop('msg_copy_fail', $this->lng_get_msg_txt('token_copy_fail', 'No se pudo copiar el token.'));

		$var = $this->get_js_ui_man_name();
		$js->add_cont($var . '.init(' . $p->get_as_js_val() . ");\n");
		echo $js->get_js_script_html();
	}

	/**
	 * Render a page that redirects to the client with an OAuth error. Used only
	 * for malformed authorization requests where the client + redirect_uri are
	 * already validated but the request itself is invalid.
	 */
	private function renderClientErrorRedirect($container, $redirectUri, $error, $state) {
		$url = $this->buildErrorUrl($redirectUri, $error, $state);
		$jsonUrl = json_encode($url, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$container->addCont(
			'<div class="alert alert-warning">'
			. htmlspecialchars($this->lng_get_msg_txt('redirecting', 'Redirigiendo, por favor espera...'))
			. '</div>'
			. '<script>window.location.replace(' . $jsonUrl . ');</script>'
		);
		$container->do_output();
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
	 * Split a scope string into a de-duplicated code list.
	 *
	 * @param string $scopeRaw  Raw scope string.
	 * @param string $sep       "space" splits on whitespace (OAuth wire format);
	 *                          any other value is treated as a literal delimiter.
	 * @return string[]
	 */
	private function parseScope($scopeRaw, $sep = 'space') {
		if ($scopeRaw === '') {
			return [];
		}
		if ($sep === 'space') {
			$parts = preg_split('/\s+/', $scopeRaw);
		} else {
			$parts = explode($sep, $scopeRaw);
		}
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
	 *   'ok'      — user holds the permission (checkbox enabled, checked by default)
	 *   'warning' — code exists in the catalog but the user lacks it (disabled)
	 *   'unknown' — code is not in the catalog (disabled)
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
