<?php
/**
 * MCP tool layer for a static-files area.
 *
 * Turns mwmod_mw_staticfiles_man into a set of flat, JSON-friendly tools so an
 * agent (the project's own MCP server) can drive the same operations the admin
 * UI offers. It is NOT the MCP server itself: it is one tool group inside it,
 * next to others such as `submits.*`, `content.*`, `users.*`.
 *
 * Every tool receives a plain array and returns a plain array:
 *
 *   ['ok' => bool, 'msg' => string, ...payload]
 *
 * so the caller can json_encode it directly.
 *
 * Usage from an MCP server:
 *
 *   $tools = new mwmod_mw_staticfiles_tools($man);
 *   $result = $tools->exec_tool('files.list', ['path' => 'css']);
 *   echo json_encode($result);
 *
 * Tool catalogue (prefix "files." because several areas may be registered):
 *
 *   files.info    {}                          → area configuration + policy
 *   files.list    {path}                      → entries of a folder
 *   files.stat    {path}                      → metadata of one entry
 *   files.read    {path}                      → text content (editable only)
 *   files.write   {path, content}             → save text content
 *   files.upload  {path, filename, base64}    → write binary/text from base64
 *   files.mkdir   {path}                      → create a folder
 *   files.rename  {from, to}                  → rename/move inside sub-folders
 *   files.delete  {path, recursive?}          → delete inside sub-folders
 */
class mwmod_mw_staticfiles_tools {

	/** @var mwmod_mw_staticfiles_man */
	private $man;

	function __construct($man) {
		$this->man = $man;
	}

	/** @return mwmod_mw_staticfiles_man */
	function get_man() {
		return $this->man;
	}

	/**
	 * Tool catalogue, for MCP `tools/list`.
	 *
	 * @return array
	 */
	function get_tool_definitions() {
		$s = function ($desc, $props = array(), $required = array()) {
			return array(
				'type'       => 'object',
				'properties' => $props,
				'required'   => $required,
				'description' => $desc,
			);
		};
		$str = function ($d) { return array('type' => 'string', 'description' => $d); };

		return array(
			array(
				'name'        => 'files.info',
				'description' => 'Return the configuration of the static files area: root path, public URL, editable root files, limits and the extension policy.',
				'inputSchema' => $s('No parameters.'),
			),
			array(
				'name'        => 'files.list',
				'description' => 'List the entries of a folder inside the area. Returns name, path, type, size, mtime, public URL and the allowed actions per entry.',
				'inputSchema' => $s('Folder to list. Empty string lists the area root.', array(
					'path' => $str('Folder path relative to the area root. "" or "/" for the root.'),
				)),
			),
			array(
				'name'        => 'files.stat',
				'description' => 'Return the metadata of a single file or folder.',
				'inputSchema' => $s('Path to stat.', array(
					'path' => $str('Path relative to the area root.'),
				), array('path')),
			),
			array(
				'name'        => 'files.read',
				'description' => 'Read a text file. Only files the area considers editable (text extensions and writable) can be read.',
				'inputSchema' => $s('File to read.', array(
					'path' => $str('File path relative to the area root.'),
				), array('path')),
			),
			array(
				'name'        => 'files.write',
				'description' => 'Create or overwrite a text file with the given content.',
				'inputSchema' => $s('File to write.', array(
					'path'    => $str('Destinaton path relative to the area root.'),
					'content' => $str('Full file content.'),
				), array('path', 'content')),
			),
			array(
				'name'        => 'files.upload',
				'description' => 'Write a file from base64 content. Use for binary files or when the caller already has the bytes.',
				'inputSchema' => $s('File to create from base64.', array(
					'path'     => $str('Destination path relative to the area root.'),
					'base64'   => $str('Base64-encoded file content.'),
				), array('path', 'base64')),
			),
			array(
				'name'        => 'files.mkdir',
				'description' => 'Create a folder inside a sub-folder of the area.',
				'inputSchema' => $s('Folder to create.', array(
					'path' => $str('Folder path relative to the area root.'),
				), array('path')),
			),
			array(
				'name'        => 'files.rename',
				'description' => 'Rename or move a file/folder. Root-level entries cannot be renamed.',
				'inputSchema' => $s('Rename operation.', array(
					'from' => $str('Current path.'),
					'to'   => $str('New path.'),
				), array('from', 'to')),
			),
			array(
				'name'        => 'files.delete',
				'description' => 'Delete a file or folder. Root-level entries cannot be deleted.',
				'inputSchema' => $s('Delete operation.', array(
					'path'      => $str('Path to delete.'),
					'recursive' => array('type' => 'boolean', 'description' => 'Delete folders recursively. Default false.'),
				), array('path')),
			),
		);
	}

	/**
	 * Dispatch a tool call.
	 *
	 * @param string $name
	 * @param array  $params
	 * @return array
	 */
	function exec_tool($name, $params = array()) {
		if (!is_array($params)) {
			$params = array();
		}
		$get = function ($k, $def = null) use ($params) {
			return array_key_exists($k, $params) ? $params[$k] : $def;
		};

		switch ($name) {
			case 'files.info':
				return array('ok' => true) + $this->man->get_data();

			case 'files.list':
				$path  = (string)$get('path', '');
				$items = $this->man->list_dir($path);
				if ($items === false) {
					return $this->man->err("Folder not found.");
				}
				return array(
					'ok'    => true,
					'path'  => $this->man->check_relpath($path),
					'url'   => $this->man->get_url($path),
					'count' => count($items),
					'items' => $items,
				);

			case 'files.stat':
				$item = $this->man->get_item((string)$get('path', ''));
				if (!$item) {
					return $this->man->err("Invalid path.");
				}
				if (!$item->exists) {
					return $this->man->err("Not found.");
				}
				return array('ok' => true, 'item' => $item->get_data(true));

			case 'files.read':
				$path = (string)$get('path', '');
				$txt  = $this->man->read_file($path);
				if ($txt === false) {
					return $this->man->err("Cannot read that file (not found, binary, or not editable).");
				}
				return array('ok' => true, 'path' => $this->man->check_relpath($path), 'content' => $txt);

			case 'files.write':
				$r = $this->man->write_file((string)$get('path', ''), (string)$get('content', ''));
				return $r;

			case 'files.upload':
				$b64 = $get('base64', '');
				if (!is_string($b64) || $b64 === '') {
					return $this->man->err("base64 content is required.");
				}
				$bytes = base64_decode($b64, true);
				if ($bytes === false) {
					return $this->man->err("Invalid base64 content.");
				}
				return $this->man->write_bytes((string)$get('path', ''), $bytes);

			case 'files.mkdir':
				return $this->man->mkdir((string)$get('path', ''));

			case 'files.rename':
				return $this->man->rename((string)$get('from', ''), (string)$get('to', ''));

			case 'files.delete':
				return $this->man->delete((string)$get('path', ''), (bool)$get('recursive', false));

			default:
				return $this->man->err("Unknown tool: " . $name);
		}
	}
}
?>
