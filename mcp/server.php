<?php
/**
 * MCP Server
 *
 * Handles Model Context Protocol requests (JSON-RPC 2.0) over HTTP POST.
 * Authentication is performed via user API tokens (Bearer scheme).
 *
 * Supported MCP methods:
 *  - initialize         → server capabilities handshake
 *  - tools/list         → enumerate registered tools
 *  - tools/call         → invoke a named tool
 *
 * Error codes follow the JSON-RPC 2.0 standard:
 *  -32700  Parse error
 *  -32600  Invalid request
 *  -32601  Method not found
 *  -32602  Invalid params
 *  -32000  Server error (custom)
 *  -32001  Unauthorized
 *  -32003  Permission denied
 */
class mwmod_mw_mcp_server {

	/** @var mw_application */
	private $mainap;

	/** @var mwmod_mw_mcp_tool[] name → tool */
	private $tools = [];

	function __construct($mainap) {
		$this->mainap = $mainap;
	}

	/**
	 * Register a tool so it becomes callable via MCP.
	 * @param mwmod_mw_mcp_tool $tool
	 */
	function registerTool(mwmod_mw_mcp_tool $tool) {
		$this->tools[$tool->getName()] = $tool;
	}

	// --------------------------------------------------------
	// Request dispatch
	// --------------------------------------------------------

	/**
	 * Read stdin, parse JSON-RPC, dispatch and output the response.
	 * Exits after output (send-and-done pattern for service endpoints).
	 */
	function handleRequest() {
		$raw = file_get_contents("php://input");
		if (!$raw) {
			$this->sendError(null, -32700, "Empty request body");
			return;
		}

		$req = json_decode($raw, true);
		if ($req === null) {
			$this->sendError(null, -32700, "Parse error: invalid JSON");
			return;
		}

		$id     = $req["id"]     ?? null;
		$method = $req["method"] ?? null;
		$params = $req["params"] ?? [];

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
		$this->sendResult($id, [
			"protocolVersion" => "2024-11-05",
			"serverInfo" => [
				"name"    => "meralda-mcp",
				"version" => "1.0",
			],
			"capabilities" => [
				"tools" => new stdClass(),
			],
		]);
	}

	private function handleToolsList($id, $params) {
		$definitions = [];
		foreach ($this->tools as $tool) {
			$definitions[] = $tool->getDefinition();
		}
		$this->sendResult($id, ["tools" => $definitions]);
	}

	private function handleToolsCall($id, $params) {
		$toolName = $params["name"]      ?? null;
		$args     = $params["arguments"] ?? [];

		if (!$toolName) {
			$this->sendError($id, -32602, "Invalid params: missing tool name");
			return;
		}

		if (!isset($this->tools[$toolName])) {
			$this->sendError($id, -32601, "Tool not found: " . $toolName);
			return;
		}

		$tool = $this->tools[$toolName];

		// Authentication: every tools/call requires a valid API token
		$user = mwmod_mw_users_apitoken_serviceauth::authenticateRequest($this->mainap);
		if (!$user) {
			$this->sendError($id, -32001, "Unauthorized: valid Bearer token required");
			return;
		}

		// Authorization: check if the token/user has the required permission
		$requiredPerm = $tool->getRequiredPermission();
		if ($requiredPerm && !$this->mainap->get_submanager("users")->allow($requiredPerm)) {
			$this->sendError($id, -32003, "Permission denied: " . $requiredPerm . " required");
			return;
		}

		// Execute
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
		echo json_encode([
			"jsonrpc" => "2.0",
			"id"      => $id,
			"result"  => $result,
		], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit;
	}

	private function sendError($id, $code, $message) {
		http_response_code($code === -32001 ? 401 : 200);
		echo json_encode([
			"jsonrpc" => "2.0",
			"id"      => $id,
			"error"   => [
				"code"    => $code,
				"message" => $message,
			],
		], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		exit;
	}
}
?>
