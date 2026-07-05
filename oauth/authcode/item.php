<?php
/**
 * OAuth Authorization Code Item.
 *
 * Represents a single row from `oauth_auth_codes`.
 *
 * Codes are short-lived (10 min max), single-use, and tied to:
 *   - the OAuth client that requested the code (client_id)
 *   - the master API token the user created at consent (api_token_id)
 *     — this token_id is the HMAC anchor for every derived access/refresh token
 *   - the PKCE challenge to verify at /token time
 *   - the registered redirect_uri (defense against code leakage)
 *
 * The plaintext code is NEVER stored — only its SHA-256 hash (code_hash).
 *
 * @extends mwmod_mw_manager_item
 */
class mwmod_mw_oauth_authcode_item extends mwmod_mw_manager_item {

	function __construct($tblitem, $man) {
		$this->init($tblitem, $man);
	}

	// get_id() is final on the base class — do NOT override it here.

	/** @return string SHA-256 hex of the plaintext code. */
	function getCodeHash() {
		return (string) $this->get_data('code_hash');
	}

	/** @return string client_id (OAuth client). */
	function getClientId() {
		return (string) $this->get_data('client_id');
	}

	/** @return int user_api_tokens.id — HMAC anchoring the derived tokens. */
	function getApiTokenId() {
		return (int) $this->get_data('api_token_id');
	}

	/** @return string Redirect URI registered at /authorize. */
	function getRedirectUri() {
		return (string) $this->get_data('redirect_uri');
	}

	/** @return string PKCE S256 challenge. */
	function getCodeChallenge() {
		return (string) $this->get_data('code_challenge');
	}

	/** @return string ISO timestamp. */
	function getExpiresAt() {
		return (string) $this->get_data('expires_at');
	}

	/** @return bool Whether the code has expired. */
	function isExpired() {
		$exp = $this->getExpiresAt();
		return ($exp !== '' && strtotime($exp) <= time());
	}
}
