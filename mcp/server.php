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
	 * Server identification returned by the `initialize` handshake.
	 * Subclasses may override to expose their own name/version.
	 * @return array{name:string,version:string}
	 */
	protected function getServerInfo() {
		return array(
			"name"    => "meralda-mcp",
			"version" => "1.0",
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
		http_response_code($this->authFailCode);
		$this->outputJSON(array(
			"jsonrpc" => "2.0",
			"id"      => null,
			"error"   => array(
				"code"    => -32001,
				"message" => "Unauthorized: valid Bearer token required",
			),
		));
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
