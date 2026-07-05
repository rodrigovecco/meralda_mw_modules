<?php
/**
 * OAuth 2.1 Token Endpoint — Meralda core.
 *
 * RFC 6749 §3.2 token endpoint for public clients. Accepts
 * application/x-www-form-urlencoded POST and returns a JSON token response.
 *
 * Supported grants:
 *   - authorization_code : exchange a one-time code (+ PKCE verifier) for a
 *                          fresh access/refresh token pair.
 *   - refresh_token      : mint a new access token from a valid refresh token.
 *
 * Tokens are stateless HMAC tokens derived from the master user_api_token that
 * was created at consent time (mwmod_mw_oauth_tokenhelper). No token rows are
 * written here: the authorization-code row is consumed (deleted) and the
 * derived tokens are signed against user_api_tokens.token_hash.
 *
 * Security:
 *   - Only POST is accepted.
 *   - authorization_code is single-use; consume() deletes it and verifies the
 *     bound client_id, redirect_uri and PKCE challenge (S256).
 *   - Errors follow RFC 6749 §5.2 ({"error": "...","error_description": "..."}).
 *
 * @property-read mw_app $mainap
 */
class mwmod_mw_oauth_endpoints_token extends mwmod_mw_service_base {

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

		$grantType = isset($_POST['grant_type']) ? (string) $_POST['grant_type'] : '';
		switch ($grantType) {
			case 'authorization_code':
				$this->handleAuthorizationCode();
				break;
			case 'refresh_token':
				$this->handleRefreshToken();
				break;
			default:
				$this->sendError('unsupported_grant_type',
					'grant_type must be authorization_code or refresh_token');
		}
	}

	// ------------------------------------------------------------------
	// grant_type=authorization_code
	// ------------------------------------------------------------------

	private function handleAuthorizationCode() {
		$code         = isset($_POST['code']) ? (string) $_POST['code'] : '';
		$clientId     = isset($_POST['client_id']) ? (string) $_POST['client_id'] : '';
		$redirectUri  = isset($_POST['redirect_uri']) ? (string) $_POST['redirect_uri'] : '';
		$codeVerifier = isset($_POST['code_verifier']) ? (string) $_POST['code_verifier'] : '';

		if ($code === '' || $clientId === '' || $redirectUri === '' || $codeVerifier === '') {
			$this->sendError('invalid_request',
				'code, client_id, redirect_uri and code_verifier are required');
			return;
		}

		// The client must exist and own the redirect_uri.
		$clientMan = $this->getClientMan();
		if (!$clientMan) {
			$this->sendError('server_error', 'OAuth is not enabled on this site', 500);
			return;
		}
		$client = $clientMan->findByClientId($clientId);
		if (!$client || !$client->allowsRedirectUri($redirectUri)) {
			$this->sendError('invalid_client', 'Unknown client or redirect_uri mismatch');
			return;
		}

		// Consume the one-time code: validates hash, client binding, redirect_uri
		// and PKCE (S256), then deletes the row. Any mismatch → false.
		$authCodeMan = $this->getAuthCodeMan();
		if (!$authCodeMan) {
			$this->sendError('server_error', 'OAuth is not enabled on this site', 500);
			return;
		}
		$codeItem = $authCodeMan->consume($code, $clientId, $redirectUri, $codeVerifier);
		if (!$codeItem) {
			$this->sendError('invalid_grant', 'Authorization code invalid, expired or already used');
			return;
		}

		// The code carries the master user_api_token id (HMAC anchor).
		$apiTokenId = $codeItem->getApiTokenId();
		$this->issueTokensForApiToken($apiTokenId);
	}

	// ------------------------------------------------------------------
	// grant_type=refresh_token
	// ------------------------------------------------------------------

	private function handleRefreshToken() {
		$refreshToken = isset($_POST['refresh_token']) ? (string) $_POST['refresh_token'] : '';
		if ($refreshToken === '') {
			$this->sendError('invalid_request', 'refresh_token is required');
			return;
		}

		$db = isset($this->mainap->db) ? $this->mainap->db : null;
		if (!$db) {
			$this->sendError('server_error', 'Database unavailable', 500);
			return;
		}

		// Verify the refresh token against the live user_api_token (revocation-aware).
		$result = mwmod_mw_oauth_tokenhelper::verify(
			$refreshToken, mwmod_mw_oauth_tokenhelper::REFRESH_PREFIX, $db);
		if (!$result) {
			$this->sendError('invalid_grant', 'Refresh token invalid, expired or revoked');
			return;
		}

		$this->issueTokensForApiToken($result['token_id']);
	}

	// ------------------------------------------------------------------
	// Shared token minting
	// ------------------------------------------------------------------

	/**
	 * Mint an access/refresh pair anchored to a master user_api_token id.
	 * Re-reads the token to confirm it is still active before signing.
	 */
	private function issueTokensForApiToken($apiTokenId) {
		$apiTokenId = (int) $apiTokenId;
		if ($apiTokenId <= 0) {
			$this->sendError('invalid_grant', 'No API token bound to this grant');
			return;
		}

		$uman = $this->mainap->get_user_manager();
		if (!$uman || !($apitokenMan = $uman->getApitokenMan())) {
			$this->sendError('server_error', 'Token subsystem unavailable', 500);
			return;
		}

		$tokenItem = $apitokenMan->get_item($apiTokenId);
		if (!$tokenItem || !$tokenItem->isActive()) {
			$this->sendError('invalid_grant', 'Master API token revoked');
			return;
		}
		if ($exp = $tokenItem->getExpiresAt()) {
			if (strtotime($exp) <= time()) {
				$this->sendError('invalid_grant', 'Master API token expired');
				return;
			}
		}

		$userId    = $tokenItem->getUserId();
		$tokenHash = $tokenItem->getTokenHash();

		$accessToken  = mwmod_mw_oauth_tokenhelper::createAccessToken($apiTokenId, $tokenHash, $userId);
		// Refresh token lives as long as the master token: 30 days by default.
		$refreshToken = mwmod_mw_oauth_tokenhelper::createRefreshToken(
			$apiTokenId, $tokenHash, $userId, 30 * 24 * 3600);

		$this->sendTokenResponse($accessToken, $refreshToken,
			$tokenItem->getPermissions());
	}

	// ------------------------------------------------------------------
	// Responses
	// ------------------------------------------------------------------

	/**
	 * RFC 6749 §5.1 success response. Cache headers disabled per spec.
	 *
	 * @param string   $accessToken
	 * @param string   $refreshToken
	 * @param string[] $scopes
	 */
	private function sendTokenResponse($accessToken, $refreshToken, $scopes) {
		ob_end_clean();
		header('Content-Type: application/json; charset=utf-8');
		header('Cache-Control: no-store');
		header('Pragma: no-cache');
		echo json_encode([
			'access_token'  => $accessToken,
			'token_type'    => 'Bearer',
			'expires_in'    => mwmod_mw_oauth_tokenhelper::ACCESS_TTL,
			'refresh_token' => $refreshToken,
			'scope'         => is_array($scopes) ? implode(' ', $scopes) : '',
		]);
		exit;
	}

	/**
	 * RFC 6749 §5.2 error response.
	 *
	 * @param string $error       OAuth error code.
	 * @param string $description Human-readable detail.
	 * @param int    $status      HTTP status (400 by default; 401 for invalid_client).
	 */
	private function sendError($error, $description = '', $status = 400) {
		if ($error === 'invalid_client' && $status === 400) {
			$status = 401;
		}
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
	// Manager accessors (delegated to the central OAuth manager)
	// ------------------------------------------------------------------

	/**
	 * Central OAuth manager for this site, or false when OAuth is disabled.
	 * @return mwmod_mw_oauth_man|false
	 */
	private function getOauthMan() {
		$uman = $this->mainap->get_user_manager();
		return $uman ? $uman->getOauthMan() : false;
	}

	/** @return mwmod_mw_oauth_client_man|false */
	private function getClientMan() {
		$oauthMan = $this->getOauthMan();
		return $oauthMan ? $oauthMan->getClientMan() : false;
	}

	/** @return mwmod_mw_oauth_authcode_man|false */
	private function getAuthCodeMan() {
		$oauthMan = $this->getOauthMan();
		return $oauthMan ? $oauthMan->getAuthCodeMan() : false;
	}
}
