<?php
//rvh 2015-01-12 v 1
/** @property-read string $mode  */
class mwmod_mw_ap_paths extends mw_apsubbaseobj {
	private $mode;
	private $_file_man;
	private $_siteRootRelPath;

	/**
	 * Constructor. Initializes the path manager with the application and mode.
	 * @param object $ap Main application object
	 * @param string $mode Path mode identifier
	 */
	function __construct($ap, $mode) {
		$this->init($ap, $mode);
	}

	/**
	 * Returns the site root-relative path for a given subpath (or the root if none).
	 * @param string|false $subpath
	 * @return string
	 */
	function getSiteRootRelSubPath($subpath = false) {
		$p = $this->getSiteRootRelPath();
		if ($subpath) {
			$p .= "/" . $subpath;
		}
		return $p;
	}

	/**
	 * Returns the site root-relative path for this path manager, or false if it cannot be determined.
	 * @return string|false
	 */
	function loadSiteRootRelPath() {
		if (!$root = $this->mainap->get_path("root")) {
			return false;
		}
		$rootlen = strlen($root);
		$p = $this->get_path();
		if (!$sub = substr($p, 0, $rootlen)) {
			return false;
		}
		$sp = trim(substr($p, $rootlen) . "", "/");
		return $sp . "";
	}

	/**
	 * Returns the cached site root-relative path for this path manager.
	 * @return string
	 */
	final function getSiteRootRelPath() {
		if (!isset($this->_siteRootRelPath)) {
			$this->_siteRootRelPath = $this->loadSiteRootRelPath() . "";
		}
		return $this->_siteRootRelPath;
	}

	/**
	 * Returns the absolute file path if it exists, or false otherwise.
	 * @param string $filename
	 * @param string $subpath
	 * @return string|false
	 */
	function get_file_path_if_exists($filename, $subpath) {
		return $this->mainap->get_file_path_if_exists($filename, $subpath, $this->mode);
	}

	/**
	 * Returns the absolute file path for a given filename and subpath, or false if not found.
	 * @param string $filename
	 * @param string $subpath
	 * @return string|false
	 */
	function get_file_path($filename, $subpath) {
		return $this->mainap->get_file_path($filename, $subpath, $this->mode);
	}

	/**
	 * Returns the absolute path for a given subpath, or false if invalid.
	 * @param string $subpath
	 * @return string|false
	 */
	function get_sub_path($subpath) {
		return $this->mainap->get_sub_path($subpath, $this->mode);
	}

	/**
	 * Returns the base absolute path for this path manager's mode.
	 * @return string
	 */
	function get_path() {
		return $this->mainap->get_path($this->mode);
	}

	/**
	 * Returns a subpath manager object for a given subpath, or false if invalid.
	 * @param string $subpath
	 * @return mwmod_mw_ap_paths_subpath|false
	 */
	function get_sub_path_man($subpath) {
		if (!$dir = $this->check_sub_path($subpath)) {
			return false;
		}
		$m = new mwmod_mw_ap_paths_subpath($dir, $this);
		return $m;
	}

	/**
	 * Validates and normalizes a subpath string. Returns the safe subpath or false if invalid.
	 * @param string $subpath
	 * @return string|false
	 */
	function check_sub_path($subpath) {
		if (!$subpath) {
			return false;
		}
		if (!is_string($subpath)) {
			return false;
		}
		$subpath = trim($subpath);
		$subpath = trim($subpath, "/");
		$subpath = trim($subpath);
		if ($subpath === "") {
			return false;
		}
		// 1) Null bytes / control chars
		if (preg_match('/[\x00-\x1F\x7F]/', $subpath)) {
			return false;
		}
		// 2) Normalizar slashes unicode → "/"
		$subpath = str_replace([
			"\u{2215}", "\u{2044}", "\u{FF0F}"
		], "/", $subpath);
		// 3) Rechazar backslash
		if (strpos($subpath, "\\") !== false) {
			return false;
		}
		// 4) Colapsar dobles //
		while (strpos($subpath, "//") !== false) {
			$subpath = str_replace("//", "/", $subpath);
		}
		$parts = explode("/", $subpath);
		$safe = [];
		foreach ($parts as $part) {
			$part = trim($part);
			if ($part === "" || $part === ".") {
				continue;
			}
			// 5) Prohibir traversal — pero PERMITIR dot-directories
			if ($part === "..") {
				return false;
			}
			if (strpos($part, "..") !== false) {
				// "a..b" permitido
				// ".../test" permitido
				// pero "a/..\b" ya está bloqueado por equals ".."
				if ($part !== "..") {
					// realmente aquí no bloqueamos; solo bloqueamos si ES exactamente ".."
				}
			}
			// 6) Caracteres ilegales (esto sí se bloquea)
			if (strpbrk($part, ":*?\"<>|") !== false) {
				return false;
			}
			// 7) Largo razonable
			if (strlen($part) > 255) {
				return false;
			}
			$safe[] = $part;
		}
		if (!$safe) {
			return false;
		}
		return implode("/", $safe);
	}

	/**
	 * Returns the file manager object for this path manager, or false if unavailable.
	 * @return mwmod_mw_helper_fileman|false
	 */
	final function get_file_man() {
		if (isset($this->_file_man)) {
			return $this->_file_man;
		}
		$this->_file_man = false;
		if ($fm = $this->mainap->get_submanager("fileman")) {
			$this->_file_man = $fm;
		}
		return $this->_file_man;
	}

	/**
	 * Returns an array of debug information for this path manager.
	 * @return array
	 */
	function get_debug_info() {
		$r = array();
		$r["mode"] = $this->__get_priv_mode();
		$r["path"] = $this->get_path();
		$r["siteRootRel"] = $this->getSiteRootRelPath();
		$sub = $this->get_sub_path_man("test");
		$r["test_sub_man"] = $sub ? $sub->get_debug_data() : null;
		return $r;
	}

	/**
	 * Initializes the path manager with the main application and mode.
	 * @param object $ap Main application object
	 * @param string $mode Path mode identifier
	 * @return void
	 */
	final function init($ap, $mode) {
		$this->set_mainap($ap);
		$this->mode = $mode;
	}

	/**
	 * Returns the private mode property.
	 * @return string
	 */
	final function __get_priv_mode() {
		return $this->mode;
	}
}
?>