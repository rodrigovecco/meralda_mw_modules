<?php
/**
 * MCP Server
 *
 * Handles Model Context Protocol requests (JSON-RPC 2.0) over HTTP POST.
 * Built on the meralda service tree (mwmod_mw_service_user_root): the bearer
 * token is validated by the framework before doExecOk() runs, so any request
 * (initialize, tools/list, tools/call) requires a valid API token.
 *
 * Supported MCP methods (single-endpoint JSON-RPC):
 *  - initialize         → server capabilities handshake
 *  - tools/list         → enumerate registered tools
 *  - tools/call         → invoke a named tool
 *
 * JSON-RPC error codes used:
 *  -32700  Parse error
 *  -32600  Invalid request
 *  -32601  Method not found / tool not found
 *  -32602  Invalid params
 *  -32000  Server error (tool exception)
 *  -32003  Permission denied
 */
abstract class mwmod_mw_mcp_server extends mwmod_mw_service_user_root {

	/** API tokens are the only supported credential. */
	public $authApiToken = true;

	/** @var mwmod_mw_mcp_tool[] name → tool */
	private $tools = array();

	/**
	 * Emit a JSON body for MCP. Overrides the base helper so every MCP response
	 * (including the 401 challenge) carries an explicit UTF-8 charset.
	 */
	function outputJSON($data) {
		ob_end_clean();
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
	}

	function __construct($baseurl = false) {
		$this->initAsRoot($baseurl);
		$this->registerAllTools();
	}

	/**
	 * Root-level auth: any authenticated API-token user is allowed to talk to
	 * the MCP endpoint. Per-tool permission scoping happens later in
	 * handleToolsCall() via getRequiredPermission() + allow().
	 */
	function isAllowed() {
		return (bool) $this->get_current_user();
	}

	/**
	 * Subclasses must register their tools here.
	 */
	abstract protected function registerAllTools();

	/**
	 * Generic server-identity defaults. These live as class properties so a
	 * concrete MCP server (or its manager) can override them in code instead of
	 * relying on cfg.ini. Subclasses typically override the getters below to
	 * pull the values from their own manager.
	 */
	protected $serverName    = "";
	protected $serverVersion = "1.0.0";
	protected $authRealm     = "meralda";
	protected $tokenUiUrl     = "/admin/index.php?ui=myaccount&sui=apitokens&sui1=api";

	/** Suggested label for the API token the client should create (empty = derive from server name). */
	protected $tokenLabel = "";

	/**
	 * Server identification returned by the `initialize` handshake.
	 * @return array{name:string,version:string}
	 */
	protected function getServerInfo() {
		$name = $this->serverName !== "" ? $this->serverName : "Meralda MCP";
		return array(
			"name"    => $name,
			"version" => $this->serverVersion,
		);
	}

	/**
	 * Register a tool so it becomes callable via MCP.
	 * @param mwmod_mw_mcp_tool $tool
	 */
	function registerTool(mwmod_mw_mcp_tool $tool) {
		$this->tools[$tool->getName()] = $tool;
	}

	// --------------------------------------------------------
	// Auth-failure response (overrides service_base to emit JSON-RPC)
	// --------------------------------------------------------

	function execNotAllowed($path = false) {
		$statusCode = (int) $this->authFailCode;
		$realm = $this->getAuthRealm();
		$tokenUiUrl = $this->getTokenUiUrl();
		$authUrl = $this->getAuthorizationUrl();
		$tokenLabel = $this->getRecommendedTokenLabel();
		$scopes = $this->getRequiredScopes();
		if ($statusCode === 401) {
			header('WWW-Authenticate: Bearer realm="' . $realm . '", authorization_uri="' . $authUrl . '"');
		}
		header('Content-Type: application/json; charset=utf-8');
		http_response_code($this->authFailCode);
		$this->outputJSON(array(
			"jsonrpc" => "2.0",
			"id"      => null,
			"error"   => array(
				"code"    => -32001,
				"message" => "Unauthorized: a valid Bearer API token is required.",
				"data"    => array(
					"auth_scheme"             => "Bearer",
					"auth_realm"              => $realm,
					"how_to_authenticate"     => "Send your API token in the 'Authorization: Bearer <token>' header.",
					"how_to_get_token"        => "Open authorization_url, then create an API token named '" . $tokenLabel . "' with the scopes listed in required_scopes, and use it as the Bearer token.",
					"recommended_token_label" => $tokenLabel,
					"required_scopes"         => $scopes,
					"authorization_url"       => $authUrl,
					"token_ui_url"            => $tokenUiUrl,
				),
			),
		));
		exit;
	}

	/**
	 * Distinct permission scopes the registered tools require. Used to tell the
	 * client exactly which scopes to request when creating the API token.
	 * @return string[]
	 */
	protected function getRequiredScopes() {
		$scopes = array();
		foreach ($this->tools as $tool) {
			$perm = $tool->getRequiredPermission();
			if ($perm) {
				$scopes[$perm] = true;
			}
		}
		return array_keys($scopes);
	}

	/**
	 * Suggested label/name for the API token the client should create.
	 * Falls back to the server name so the token is easy to identify later.
	 */
	protected function getRecommendedTokenLabel() {
		if ($this->tokenLabel !== "") {
			return $this->tokenLabel;
		}
		return $this->serverName !== "" ? $this->serverName : "Meralda MCP";
	}

	/**
	 * Token-creation UI URL pre-filled with the recommended token label and the
	 * exact scopes to request, so the user lands on the create form ready to go.
	 * Relies on the account UI prefill params (_apitk_open_create / _apitk_label
	 * / _apitk_prefill_perms).
	 */
	protected function getAuthorizationUrl() {
		$base = $this->getTokenUiUrl();
		$sep  = (strpos($base, '?') === false) ? '?' : '&';
		$params = array(
			'_apitk_open_create=1',
			'_apitk_label=' . urlencode($this->getRecommendedTokenLabel()),
			'_apitk_prefill_perms=' . urlencode(implode(',', $this->getRequiredScopes())),
		);
		return $base . $sep . implode('&', $params);
	}

	/**
	 * Realm used in the WWW-Authenticate challenge and the 401 payload.
	 */
	protected function getAuthRealm() {
		return $this->authRealm;
	}

	/**
	 * URL of the existing in-app UI where the user signs in and generates an
	 * API token. No dedicated MCP auth/token endpoint exists: the 401 simply
	 * points the user to the account UI that already ships with the app.
	 */
	protected function getTokenUiUrl() {
		return $this->toAbsoluteUrl($this->tokenUiUrl);
	}

	/**
	 * Resolve a possibly-relative URL against the current host/scheme.
	 */
	protected function toAbsoluteUrl($url) {
		if ($url === "" || preg_match('#^https?://#i', $url)) {
			return $url;
		}
		$host = $_SERVER['HTTP_HOST'] ?? '';
		if ($host === '') {
			return $url;
		}
		$scheme = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
		if ($url[0] !== '/') {
			$url = '/' . $url;
		}
		return $scheme . '://' . $host . $url;
	}

	// --------------------------------------------------------
	// Request dispatch (runs only after auth succeeds)
	// --------------------------------------------------------

	function doExecOk($path = false) {
		$raw = $this->getJsonRequestBody();
		if (!$raw || !is_array($raw)) {
			$this->sendError(null, -32700, "Parse error: invalid or empty JSON body");
			return;
		}

		$id     = $raw["id"]     ?? null;
		$method = $raw["method"] ?? null;
		$params = $raw["params"] ?? array();

		if (!$method) {
			$this->sendError($id, -32600, "Invalid request: missing method");
			return;
		}

		switch ($method) {
			case "initialize":
				$this->handleInitialize($id, $params);
				break;

			case "tools/list":
				$this->handleToolsList($id, $params);
				break;

			case "tools/call":
				$this->handleToolsCall($id, $params);
				break;

			default:
				$this->sendError($id, -32601, "Method not found: " . $method);
		}
	}

	// --------------------------------------------------------
	// Method handlers
	// --------------------------------------------------------

	private function handleInitialize($id, $params) {
		$this->sendResult($id, array(
			"protocolVersion" => "2024-11-05",
			"serverInfo"      => $this->getServerInfo(),
			"capabilities"    => array(
				"tools" => new stdClass(),
			),
		));
	}

	private function handleToolsList($id, $params) {
		$definitions = array();
		foreach ($this->tools as $tool) {
			$definitions[] = $tool->getDefinition();
		}
		$this->sendResult($id, array("tools" => $definitions));
	}

	private function handleToolsCall($id, $params) {
		$toolName = $params["name"]      ?? null;
		$args     = $params["arguments"] ?? array();

		if (!$toolName) {
			$this->sendError($id, -32602, "Invalid params: missing tool name");
			return;
		}

		if (!isset($this->tools[$toolName])) {
			$this->sendError($id, -32601, "Tool not found: " . $toolName);
			return;
		}

		$tool = $this->tools[$toolName];

		// Per-tool authorization. Auth itself already ran in validateAllowedAsRoot();
		// here we only check the specific permission the tool declares.
		$requiredPerm = $tool->getRequiredPermission();
		if ($requiredPerm && !$this->allow($requiredPerm)) {
			$this->sendError($id, -32003, "Permission denied: " . $requiredPerm . " required");
			return;
		}

		try {
			$result = $tool->execute($args, $this->mainap);
			$this->sendResult($id, $result);
		} catch (Exception $e) {
			$this->sendError($id, -32000, "Internal error: " . $e->getMessage());
		}
	}

	// --------------------------------------------------------
	// JSON-RPC response helpers
	// --------------------------------------------------------

	private function sendResult($id, $result) {
		$this->outputJSON(array(
			"jsonrpc" => "2.0",
			"id"      => $id,
			"result"  => $result,
		));
		exit;
	}

	private function sendError($id, $code, $message) {
		$this->outputJSON(array(
			"jsonrpc" => "2.0",
			"id"      => $id,
			"error"   => array(
				"code"    => $code,
				"message" => $message,
			),
		));
		exit;
	}
}
?>
