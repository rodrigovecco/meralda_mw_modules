<?php
/**
 * Generic MCP mount point — Meralda core.
 *
 * Groups one or more MCP servers under a single base URL, mirroring the OAuth
 * server pattern (mwmod_mw_oauth_server). Each MCP server is exposed as a
 * VIRTUAL child path (e.g. <base>/mtsx), NOT a real directory. This matters:
 * Apache's mod_dir issues a trailing-slash 301 whenever a real directory is
 * requested without the slash, and that redirect drops the POST body and the
 * Authorization header — breaking the MCP handshake. Because the child paths
 * here are dispatched in PHP (no filesystem directory exists for them), mod_dir
 * never fires and POST /<base>/<server> works with or without a trailing slash,
 * exactly like /oauth/token does.
 *
 * Mounted once per site by a thin public_html wrapper, e.g.:
 *     $svc = new mwmod_mtsx_mcpserver_serverroot("service/mcp");
 *     $svc->execServiceByREQUEST_URI();
 *
 * Subclasses declare the available servers via createChildByMethod_<cod>(),
 * which makes it trivial to expose several MCP servers under one mount.
 *
 * @property-read mw_app $mainap
 */
abstract class mwmod_mw_mcp_serverroot extends mwmod_mw_service_base {

	function __construct($baseurl = false) {
		$this->initAsRoot($baseurl);
	}

	/**
	 * Routing to children is always allowed; each child MCP server enforces its
	 * own bearer-token authentication and per-tool permissions.
	 */
	function validateAllowedAsRoot() {
		return true;
	}
	function doExecOk($path=false){
		$info=array(
			"msg"=>"Hello! This site is powered by Meralda",



			);


		$this->outputJSON($info);

	}

	/** The mount root itself exposes nothing; only its children are callable. */
	function isAllowed() {
		return true;
	}

	/** Enable child creation for the per-server method dispatch. */
	function childrenCreationEnabled() {
		return true;
	}

	/** MCP responses are JSON with an explicit UTF-8 charset. */
	function outputJSON($data) {
		ob_end_clean();
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
	}

}
