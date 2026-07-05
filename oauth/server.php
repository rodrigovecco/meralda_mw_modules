<?php
/**
 * OAuth 2.1 authorization-server endpoints — Meralda core.
 *
 * Public service root that groups the machine-to-machine OAuth endpoints under
 * a single base URL. It carries NO user session: the token and register
 * endpoints are unauthenticated by design (a public client exchanges a PKCE
 * code, or registers itself via DCR). Per-request security lives inside each
 * child endpoint (PKCE verification, single-use codes, redirect_uri binding).
 *
 * Mounted once per site by a thin public_html wrapper, e.g.:
 *     $svc = new mwmod_mw_oauth_server("oauth");
 *     $svc->execServiceByREQUEST_URI();
 *
 * Routes (relative to the mount base):
 *     POST  <base>/token      → mwmod_mw_oauth_endpoints_token
 *     POST  <base>/register   → mwmod_mw_oauth_endpoints_register
 *
 * Lives in the mw package so any Meralda project can expose OAuth without
 * duplicating the flow.
 *
 * @property-read mw_app $mainap
 */
class mwmod_mw_oauth_server extends mwmod_mw_service_base {

	function __construct($baseurl = false) {
		$this->initAsRoot($baseurl);
	}

	/**
	 * The server root itself exposes nothing; only its children are callable.
	 * Children are reached via createChildByMethod_<cod>().
	 */
	function isAllowed() {
		return false;
	}

	/** Enable child creation for the token/register method dispatch. */
	function childrenCreationEnabled() {
		return true;
	}

	/** @return mwmod_mw_oauth_endpoints_token */
	function createChildByMethod_token($cod) {
		return new mwmod_mw_oauth_endpoints_token();
	}

	/** @return mwmod_mw_oauth_endpoints_register */
	function createChildByMethod_register($cod) {
		return new mwmod_mw_oauth_endpoints_register();
	}

	/**
	 * OAuth error responses are JSON with an explicit UTF-8 charset.
	 * @param array $data
	 */
	function outputJSON($data) {
		ob_end_clean();
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
	}
}
