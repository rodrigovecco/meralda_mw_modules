<?php
/**
 * MCP Tool Base Class
 *
 * Abstract base for all Model Context Protocol (MCP) tools in Meralda.
 * Each subclass represents one callable tool exposed by the MCP server.
 *
 * MCP protocol spec: https://spec.modelcontextprotocol.io/ (JSON-RPC 2.0)
 *
 * Subclasses must implement:
 *  - getName()              → unique tool name (e.g. "hbeat_list_ecosystems")
 *  - getDescription()       → human-readable description for the AI
 *  - getInputSchema()       → JSON Schema properties array (assoc)
 *  - getRequiredProperties() → array of required property names
 *  - getRequiredPermission() → permission code string, or empty string for public tools
 *  - execute(array $args, $mainap) → do the work, return result array
 */
abstract class mwmod_mw_mcp_tool {

	/**
	 * Unique tool name. Must be a valid identifier (letters, digits, underscore).
	 * @return string
	 */
	abstract function getName();

	/**
	 * Human-readable description for the AI client.
	 * @return string
	 */
	abstract function getDescription();

	/**
	 * JSON Schema properties for the input object.
	 * Return an associative array of {propertyName: {type, description, ...}} entries.
	 * @return array
	 */
	abstract function getInputSchema();

	/**
	 * List of required property names (subset of getInputSchema() keys).
	 * @return string[]
	 */
	function getRequiredProperties() {
		return [];
	}

	/**
	 * Permission code the caller must hold to invoke this tool.
	 * Return an empty string for tools that need no specific permission.
	 * @return string
	 */
	abstract function getRequiredPermission();

	/**
	 * Execute the tool and return the result as an associative array.
	 * The array will be JSON-encoded as the tool response text.
	 *
	 * @param  array        $args   Validated input arguments from the AI
	 * @param  mw_application $mainap  Meralda application instance
	 * @return array
	 */
	abstract function execute(array $args, $mainap);

	// --------------------------------------------------------
	// Helpers for subclasses
	// --------------------------------------------------------

	/**
	 * Wrap a result value in the MCP tool content envelope.
	 * @param  array|mixed $data
	 * @return array  MCP-format response
	 */
	function wrapResult($data) {
		return [
			"content" => [
				[
					"type" => "text",
					"text" => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				],
			],
			"isError" => false,
		];
	}

	/**
	 * Wrap an error in the MCP tool content envelope.
	 * @param  string $message
	 * @return array
	 */
	function wrapError($message) {
		return [
			"content" => [
				[
					"type" => "text",
					"text" => json_encode(["ok" => false, "error" => $message],
						JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
				],
			],
			"isError" => true,
		];
	}

	/**
	 * Build the MCP tools/list definition for this tool.
	 * @return array
	 */
	function getDefinition() {
		return [
			"name"        => $this->getName(),
			"description" => $this->getDescription(),
			"inputSchema" => [
				"type"       => "object",
				"properties" => $this->getInputSchema(),
				"required"   => $this->getRequiredProperties(),
			],
		];
	}
}
?>
