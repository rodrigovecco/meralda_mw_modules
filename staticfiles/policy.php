<?php
/**
 * Extension policy for a static-files area.
 *
 * A static-files area serves user-managed assets (a hand-made website, images,
 * css, js…) that live INSIDE the document root. That is exactly the situation
 * where a bad extension becomes remote code execution, so this class uses an
 * ALLOW list, not a deny list: anything not explicitly listed is rejected.
 *
 * Extends/overrides:
 *   - get_allowed_exts()   → add project-specific types
 *   - get_denied_exts()    → add hard blocks (checked after the allow list)
 *   - is_ext_allowed()     → replace the whole decision
 *
 * Design note: the deny list exists even though the allow list already covers
 * the dangerous cases, because it also blocks files that *look* harmless but
 * are executed or interpreted by the server (`.htaccess`, `.user.ini`, `.phar`).
 */
class mwmod_mw_staticfiles_policy {

	/** Cache of the normalised lists. */
	private $_allowed;
	private $_denied;

	function __construct() {
	}

	// --------------------------------------------------------------------
	// Override points
	// --------------------------------------------------------------------

	/**
	 * Extensions that MAY be written/served. Lower-case, no dots.
	 *
	 * Keep this list explicit: adding "html" means someone can inject markup
	 * (and therefore JS) into the page. For a website area that is intended.
	 *
	 * @return string[]
	 */
	function get_init_allowed_exts() {
		return array(
			// markup / styles / scripts (a static site)
			'html', 'htm', 'css', 'js', 'mjs', 'json', 'map',
			// text
			'txt', 'md', 'csv', 'xml',
			// images
			'png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'ico', 'svg',
			// fonts
			'woff', 'woff2', 'ttf', 'otf', 'eot',
			// documents / media
			'pdf', 'mp4', 'webm', 'mp3', 'wav',
		);
	}

	/**
	 * Extensions blocked even if they appear in the allow list.
	 * These are executable, server-interpreted, or config files.
	 *
	 * @return string[]
	 */
	function get_init_denied_exts() {
		return array(
			// php and friends
			'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8',
			'phps', 'pht', 'phtml', 'phar', 'inc',
			// other server-side languages
			'cgi', 'pl', 'pm', 'py', 'rb', 'jsp', 'jspx', 'asp', 'aspx',
			'shtml', 'shtm', 'cfm', 'cfml', 'lua',
			// shell / binaries
			'sh', 'bash', 'zsh', 'csh', 'ksh', 'bat', 'cmd', 'ps1', 'exe',
			'dll', 'so', 'dylib', 'bin', 'com', 'jar', 'war',
			// apache / server config
			'htaccess', 'htpasswd', 'user', 'ini', 'conf', 'config', 'env',
			// database dumps
			'sql', 'sqlite', 'db',
		);
	}

	/**
	 * Whole-file-name deny list (a file called exactly this is refused even if
	 * its extension would be allowed).
	 *
	 * @return string[]
	 */
	function get_denied_filenames() {
		return array(
			'.htaccess', '.htpasswd', '.user.ini', 'php.ini', '.env',
			'web.config', 'composer.json', 'composer.lock',
		);
	}

	// --------------------------------------------------------------------
	// Cached accessors
	// --------------------------------------------------------------------

	/** @return string[] */
	final function get_allowed_exts() {
		if (!isset($this->_allowed)) {
			$r = array();
			foreach ((array)$this->get_init_allowed_exts() as $e) {
				if ($e = $this->normalize_ext($e)) {
					$r[$e] = $e;
				}
			}
			$this->_allowed = array_values($r);
		}
		return $this->_allowed;
	}

	/** @return string[] */
	final function get_denied_exts() {
		if (!isset($this->_denied)) {
			$r = array();
			foreach ((array)$this->get_init_denied_exts() as $e) {
				if ($e = $this->normalize_ext($e)) {
					$r[$e] = $e;
				}
			}
			$this->_denied = array_values($r);
		}
		return $this->_denied;
	}

	/**
	 * Lower-case extension, no leading dot. Returns false for empty/invalid.
	 *
	 * @param string $ext
	 * @return string|false
	 */
	function normalize_ext($ext) {
		if (!is_string($ext)) {
			return false;
		}
		$ext = ltrim(trim($ext), '.');
		if ($ext === '' || strlen($ext) > 20) {
			return false;
		}
		if (!preg_match('/^[A-Za-z0-9]+$/', $ext)) {
			return false;
		}
		return strtolower($ext);
	}

	/**
	 * Extension of a file name. Returns false when the name has no extension.
	 *
	 * @param string $filename
	 * @return string|false
	 */
	function get_ext_from_filename($filename) {
		if (!is_string($filename)) {
			return false;
		}
		$base = basename($filename);
		$pos  = strrpos($base, '.');
		if ($pos === false || $pos === 0 || $pos === strlen($base) - 1) {
			return false;
		}
		return $this->normalize_ext(substr($base, $pos + 1));
	}

	// --------------------------------------------------------------------
	// Decision
	// --------------------------------------------------------------------

	/**
	 * True when the file name may be created/served by this area.
	 *
	 * @param string $filename  file name or relative path
	 * @return bool
	 */
	function is_filename_allowed($filename) {
		if (!is_string($filename) || trim($filename) === '') {
			return false;
		}

		$base = basename($filename);

		// Exact file names (config files, dotfiles).
		$lowerBase = strtolower($base);
		foreach ((array)$this->get_denied_filenames() as $denied) {
			if ($lowerBase === strtolower($denied)) {
				return false;
			}
		}

		// Files without extension: refuse. A static site never needs them and
		// they are the easiest way to slip something past an extension check.
		$ext = $this->get_ext_from_filename($base);
		if ($ext === false) {
			return false;
		}

		return $this->is_ext_allowed($ext);
	}

	/**
	 * Extension decision: deny list first, then allow list.
	 *
	 * @param string $ext normalised extension
	 * @return bool
	 */
	function is_ext_allowed($ext) {
		if (!$ext = $this->normalize_ext($ext)) {
			return false;
		}
		if (in_array($ext, $this->get_denied_exts(), true)) {
			return false;
		}
		return in_array($ext, $this->get_allowed_exts(), true);
	}

	/**
	 * True when the extension is text and therefore editable in the UI
	 * (a binary file can only be uploaded/renamed/deleted).
	 *
	 * @param string $filename
	 * @return bool
	 */
	function is_editable_ext($filename) {
		$ext = $this->get_ext_from_filename($filename);
		if ($ext === false) {
			return false;
		}
		$editable = array(
			'html', 'htm', 'css', 'js', 'mjs', 'json', 'map',
			'txt', 'md', 'csv', 'xml', 'svg',
		);
		return in_array($ext, $editable, true);
	}

	/**
	 * Content type for the given file name, used when serving it.
	 *
	 * @param string $filename
	 * @return string
	 */
	function get_content_type($filename) {
		$ext = $this->get_ext_from_filename($filename);
		$map = array(
			'html'  => 'text/html',
			'htm'   => 'text/html',
			'css'   => 'text/css',
			'js'    => 'application/javascript',
			'mjs'   => 'application/javascript',
			'json'  => 'application/json',
			'map'   => 'application/json',
			'txt'   => 'text/plain',
			'md'    => 'text/markdown',
			'csv'   => 'text/csv',
			'xml'   => 'application/xml',
			'svg'   => 'image/svg+xml',
			'png'   => 'image/png',
			'jpg'   => 'image/jpeg',
			'jpeg'  => 'image/jpeg',
			'gif'   => 'image/gif',
			'webp'  => 'image/webp',
			'avif'  => 'image/avif',
			'ico'   => 'image/x-icon',
			'woff'  => 'font/woff',
			'woff2' => 'font/woff2',
			'ttf'   => 'font/ttf',
			'otf'   => 'font/otf',
			'eot'   => 'application/vnd.ms-fontobject',
			'pdf'   => 'application/pdf',
			'mp4'   => 'video/mp4',
			'webm'  => 'video/webm',
			'mp3'   => 'audio/mpeg',
			'wav'   => 'audio/wav',
		);
		if ($ext !== false && isset($map[$ext])) {
			return $map[$ext];
		}
		return 'application/octet-stream';
	}

	/**
	 * Machine-readable summary, used by the MCP tool layer.
	 *
	 * @return array
	 */
	function get_debug_data() {
		return array(
			'allowed' => $this->get_allowed_exts(),
			'denied'  => $this->get_denied_exts(),
			'files'   => $this->get_denied_filenames(),
		);
	}
}
?>
