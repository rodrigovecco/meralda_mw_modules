<?php
/**
 * OAuth 2.1 authorization-server manager — Meralda core.
 *
 * Central holder for the OAuth sub-managers (clients and authorization codes).
 * It is the single place through which the consent UI and the public token /
 * register endpoints obtain their managers, so nothing instantiates the CRUD
 * managers ad hoc.
 *
 * Lifecycle: this manager is a private, lazy-loaded property of the users
 * manager. It is DISABLED by default — an app enables OAuth by overriding
 * mwmod_mw_users_base_usersmanabs::createOauthMan() in its own userman to
 * return new mwmod_mw_oauth_man($this). Reach it via $usersMan->getOauthMan().
 *
 * Sub-managers are themselves lazy-loaded and overridable (createClientMan /
 * createAuthCodeMan), following the same pattern as jwtMan / apitokenMan.
 *
 * @property-read mw_app $mainap
 */
class mwmod_mw_oauth_man extends mw_apsubbaseobj {

	/** @var mwmod_mw_users_base_usersmanabs */
	private $usersMan;

	/** @var mwmod_mw_oauth_client_man|null */
	private $clientMan;

	/** @var mwmod_mw_oauth_authcode_man|null */
	private $authCodeMan;

	function __construct($usersMan) {
		$this->setUsersMan($usersMan);
		$this->set_mainap($usersMan->mainap);
	}

	/**
	 * Set the owning users manager. Kept final so subclasses that override the
	 * constructor can still wire the (private) back-reference.
	 * @param mwmod_mw_users_base_usersmanabs $usersMan
	 */
	final function setUsersMan($usersMan) {
		$this->usersMan = $usersMan;
	}

	final function __get_priv_usersMan() {
		return $this->usersMan;
	}

	/** @return mwmod_mw_users_base_usersmanabs */
	final function getUsersMan() {
		return $this->usersMan;
	}

	// --------------------------------------------------------
	// Client manager (oauth_clients)
	// --------------------------------------------------------

	function createClientMan() {
		return new mwmod_mw_oauth_client_man($this);
	}
	final function __get_priv_clientMan() {
		if (!isset($this->clientMan)) {
			$this->clientMan = $this->createClientMan();
		}
		return $this->clientMan;
	}
	/** @return mwmod_mw_oauth_client_man */
	final function getClientMan() {
		return $this->__get_priv_clientMan();
	}

	// --------------------------------------------------------
	// Authorization-code manager (oauth_auth_codes)
	// --------------------------------------------------------

	function createAuthCodeMan() {
		return new mwmod_mw_oauth_authcode_man($this);
	}
	final function __get_priv_authCodeMan() {
		if (!isset($this->authCodeMan)) {
			$this->authCodeMan = $this->createAuthCodeMan();
		}
		return $this->authCodeMan;
	}
	/** @return mwmod_mw_oauth_authcode_man */
	final function getAuthCodeMan() {
		return $this->__get_priv_authCodeMan();
	}
}
