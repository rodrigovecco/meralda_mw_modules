<?php
/**
 * OAuth Client Manager.
 *
 * CRUD for the `oauth_clients` table (Dynamic Client Registration, RFC 7591).
 *
 * Public clients only: no secret is ever issued. The client_id is the only
 * persistent credential; authorization is enforced per-request via PKCE and
 * the user's own permission scope at consent time.
 *
 * @extends mwmod_mw_manager_man<mwmod_mw_oauth_client_item>
 */
class mwmod_mw_oauth_client_man extends mwmod_mw_manager_man {

	/** @var mwmod_mw_oauth_man */
	private $oauthMan;

	function __construct($oauthMan) {
		$this->setOauthMan($oauthMan);
		$this->init('oauth_clients', $oauthMan->mainap, 'oauth_clients');
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
	 * @return mwmod_mw_oauth_client_item
	 */
	function create_item($tblitem) {
		return new mwmod_mw_oauth_client_item($tblitem, $this);
	}

	function get_item_name($item) {
		return $item->getName();
	}

	/**
	 * Look up a client by its client_id (the table's primary key).
	 *
	 * @param string $clientId
	 * @return mwmod_mw_oauth_client_item|false
	 */
	function findByClientId($clientId) {
		$clientId = (string) $clientId;
		if ($clientId === '') {
			return false;
		}
		return $this->get_item_by_keys(['id' => $clientId]);
	}

	/**
	 * Register a new public client (RFC 7591 DCR).
	 *
	 * @param string   $clientName    Human-readable name.
	 * @param string[] $redirectUris  Non-empty list of allowed redirect URIs.
	 * @return mwmod_mw_oauth_client_item|false
	 */
	function registerClient($clientName, array $redirectUris) {
		$clientName = is_string($clientName) ? trim($clientName) : '';
		if ($clientName === '') {
			$clientName = 'Unnamed Client';
		}
		// Reject empty redirect_uri list.
		$cleanUris = [];
		foreach ($redirectUris as $uri) {
			$uri = is_string($uri) ? trim($uri) : '';
			if ($uri === '') continue;
			// Only http/https are accepted; loopback/localhost allowed.
			if (!filter_var($uri, FILTER_VALIDATE_URL)) continue;
			$scheme = strtolower((string) parse_url($uri, PHP_URL_SCHEME));
			if (!in_array($scheme, ['http', 'https'], true)) continue;
			$cleanUris[$uri] = true;
		}
		$cleanUris = array_keys($cleanUris);
		if (empty($cleanUris)) {
			return false;
		}

		// Generate a unique client_id (retry on the unlikely collision).
		for ($attempt = 0; $attempt < 3; $attempt++) {
			$clientId = mwmod_mw_oauth_helper::generateClientId();
			if ($this->findByClientId($clientId)) {
				continue; // collision, retry
			}
			return $this->insert_item_strict([
				'id'             => $clientId,
				'client_name'    => mb_substr($clientName, 0, 200),
				'redirect_uris'  => json_encode(array_values($cleanUris)),
			]);
		}
		return false;
	}
}
