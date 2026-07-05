<?php
/**
 * OAuth 2.0 Dynamic Client Registration Endpoint — Meralda core.
 *
 * RFC 7591 registration endpoint for PUBLIC clients (no secret issued). A
 * client (e.g. a Claude custom connector) POSTs a JSON body with its
 * redirect_uris and, optionally, a client_name; the server persists a new
 * oauth_clients row and returns the generated client_id.
 *
 * Public-client model (OAuth 2.1): token_endpoint_auth_method = "none". The
 * client authenticates at /token purely via the PKCE code_verifier, so no
 * client_secret is ever generated or stored.
 *
 * Request  (application/json):
 *   { "client_name": "...", "redirect_uris": ["https://.../callback"] }
 *
 * Response (201, application/json):
 *   { "client_id": "...", "client_name": "...", "redirect_uris": [...],
 *     "token_endpoint_auth_method": "none",
 *     "grant_types": ["authorization_code","refresh_token"],
 *     "response_types": ["code"] }
 *
 * Security:
 *   - Only POST is accepted.
 *   - redirect_uris is mandatory and validated (http/https only) by the client
 *     manager; an empty/invalid list is rejected.
 *   - Errors follow RFC 7591 §3.2.2 ({"error": "...","error_description": ...}).
 *
 * @property-read mw_app $mainap
 */
class mwmod_mw_oauth_endpoints_register extends mwmod_mw_service_base {

	public $isfinal = true;

	/** Public endpoint: request-level checks happen in doExecOk(). */
	function isAllowed() {
		return true;
	}

	function doExecOk($path = false) {
		if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
			$this->sendError('invalid_request', 'POST required', 405);
			return;
		}

		$body = $this->getJsonRequestBody();
		if (!is_array($body)) {
			$this->sendError('invalid_client_metadata', 'Request body must be a JSON object');
			return;
		}

		$clientName   = isset($body['client_name']) ? (string) $body['client_name'] : '';
		$redirectUris = isset($body['redirect_uris']) ? $body['redirect_uris'] : null;

		if (!is_array($redirectUris) || empty($redirectUris)) {
			$this->sendError('invalid_redirect_uri', 'redirect_uris is required and must be a non-empty array');
			return;
		}

		$clientMan = $this->getClientMan();
		if (!$clientMan) {
			$this->sendError('invalid_client_metadata', 'OAuth is not enabled on this site', 500);
			return;
		}
		$client = $clientMan->registerClient($clientName, $redirectUris);
		if (!$client) {
			$this->sendError('invalid_redirect_uri', 'No valid http/https redirect_uris provided');
			return;
		}

		$this->sendCreated($client);
	}

	// ------------------------------------------------------------------
	// Responses
	// ------------------------------------------------------------------

	/**
	 * RFC 7591 §3.2.1 success response (201 Created).
	 * @param mwmod_mw_oauth_client_item $client
	 */
	private function sendCreated($client) {
		ob_end_clean();
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-store');
		http_response_code(201);
		echo json_encode([
			'client_id'                  => $client->getClientId(),
			'client_name'                => $client->getName(),
			'redirect_uris'              => $client->getRedirectUris(),
			'token_endpoint_auth_method' => 'none',
			'grant_types'                => ['authorization_code', 'refresh_token'],
			'response_types'             => ['code'],
		]);
		exit;
	}

	/**
	 * RFC 7591 §3.2.2 error response.
	 * @param string $error       OAuth registration error code.
	 * @param string $description Human-readable detail.
	 * @param int    $status      HTTP status (400 by default).
	 */
	private function sendError($error, $description = '', $status = 400) {
		ob_end_clean();
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-store');
		http_response_code($status);
		echo json_encode([
			'error'             => $error,
			'error_description' => $description,
		]);
		exit;
	}

	// ------------------------------------------------------------------
	// Manager accessor (delegated to the central OAuth manager)
	// ------------------------------------------------------------------

	/** @return mwmod_mw_oauth_client_man|false */
	private function getClientMan() {
		$uman = $this->mainap->get_user_manager();
		$oauthMan = $uman ? $uman->getOauthMan() : false;
		return $oauthMan ? $oauthMan->getClientMan() : false;
	}
}
