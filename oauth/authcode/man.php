<?php
/**
 * OAuth Authorization Code Manager.
 *
 * CRUD for the `oauth_auth_codes` table. Codes are created at /authorize
 * (after the user consents) and consumed exactly once at /token. They are
 * deleted on use; any unused code expires in 10 minutes. Lazy GC of expired
 * codes runs inside create().
 *
 * The plaintext code returned to the client is never persisted: only its
 * SHA-256 hash (code_hash) is stored.
 *
 * @extends mwmod_mw_manager_man<mwmod_mw_oauth_authcode_item>
 */
class mwmod_mw_oauth_authcode_man extends mwmod_mw_manager_man {

	/** @var mwmod_mw_oauth_man */
	private $oauthMan;

	function __construct($oauthMan) {
		$this->setOauthMan($oauthMan);
		$this->init('oauth_auth_codes', $oauthMan->mainap, 'oauth_auth_codes');
	}

	/**
	 * Set the owning OAuth manager. Kept final so subclasses that override the
	 * constructor can still wire the (private) back-reference.
	 * @param mwmod_mw_oauth_man $oauthMan
	 */
	final function setOauthMan($oauthMan) {
		$this->oauthMan = $oauthMan;
	}

	/** @return mwmod_mw_oauth_man */
	final function __get_priv_oauthMan() {
		return $this->oauthMan;
	}
	/** @return mwmod_mw_oauth_man */
	final function getOauthMan() {
		return $this->__get_priv_oauthMan();
	}

	/**
	 * @param mwmod_mw_db_row $tblitem
	 * @return mwmod_mw_oauth_authcode_item
	 */
	function create_item($tblitem) {
		return new mwmod_mw_oauth_authcode_item($tblitem, $this);
	}

	function get_item_name($item) {
		return 'authcode #' . $item->get_id();
	}

	/**
	 * Create a new authorization code and persist its hash.
	 *
	 * @param string $clientId      Client requesting the code.
	 * @param int    $apiTokenId    user_api_tokens.id of the master token created at consent.
	 * @param string $redirectUri   Exact redirect_uri to bind to the code.
	 * @param string $codeChallenge PKCE S256 challenge.
	 * @return string|false Plaintext code, or false on failure.
	 */
	function create($clientId, $apiTokenId, $redirectUri, $codeChallenge) {
		$clientId      = (string) $clientId;
		$apiTokenId    = (int) $apiTokenId;
		$redirectUri   = (string) $redirectUri;
		$codeChallenge = (string) $codeChallenge;

		if ($clientId === '' || $apiTokenId <= 0 || $redirectUri === '' || $codeChallenge === '') {
			return false;
		}

		$this->gcExpired();

		$plainCode = mwmod_mw_oauth_helper::generateAuthCode();
		$codeHash  = mwmod_mw_oauth_helper::hashCode($plainCode);
		$expiresAt = date('Y-m-d H:i:s', time() + mwmod_mw_oauth_helper::AUTH_CODE_TTL);

		$item = $this->insert_item([
			'code_hash'      => $codeHash,
			'client_id'      => $clientId,
			'api_token_id'   => $apiTokenId,
			'redirect_uri'   => $redirectUri,
			'code_challenge' => $codeChallenge,
			'expires_at'     => $expiresAt,
		]);

		return $item ? $plainCode : false;
	}

	/**
	 * Consume a plaintext authorization code: look it up by hash, validate the
	 * bound client_id and redirect_uri, verify PKCE, check expiry, then DELETE
	 * it (single-use). Returns the matching item on success.
	 *
	 * @param string $plainCode    Plaintext code as received at /token.
	 * @param string $clientId     client_id claiming the code.
	 * @param string $redirectUri  redirect_uri sent at /token (MUST match).
	 * @param string $codeVerifier PKCE verifier sent at /token.
	 * @return mwmod_mw_oauth_authcode_item|false
	 */
	function consume($plainCode, $clientId, $redirectUri, $codeVerifier) {
		if (!is_string($plainCode) || $plainCode === '') return false;
		$codeHash = mwmod_mw_oauth_helper::hashCode($plainCode);

		// Meralda manager API: single-row lookup on a unique column.
		$item = $this->get_item_by_keys(['code_hash' => $codeHash]);
		if (!$item) return false;

		// Code is bound to client_id + redirect_uri to prevent injection.
		if (!hash_equals($item->getClientId(), (string) $clientId)) return false;
		if (!hash_equals($item->getRedirectUri(), (string) $redirectUri)) return false;

		// PKCE S256 verifier must match the stored challenge.
		if (!mwmod_mw_oauth_helper::verifyPkce($codeVerifier, $item->getCodeChallenge())) {
			return false;
		}

		if ($item->isExpired()) {
			$item->delete();
			return false;
		}

		// Single-use: delete before returning so a replay finds nothing.
		$result = $item;
		$item->delete();
		return $result;
	}

	/**
	 * Lazy garbage collector. Removes codes past their expiry.
	 * Runs on every create() so the table stays small without a cron.
	 */
	function gcExpired() {
		$tblman = $this->get_tblman();
		if (!$tblman || !$tblman->dbman) {
			return;
		}
		// Bulk delete of expired rows (same pattern the framework uses for
		// housekeeping, e.g. rols/rolsman). NOW() is evaluated server-side.
		$tblman->dbman->query('DELETE FROM oauth_auth_codes WHERE expires_at < NOW()');
	}
}
