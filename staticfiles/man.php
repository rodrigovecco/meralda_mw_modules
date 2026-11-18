<?php
/**
 * Abstract manager for a "static files area".
 *
 * A static files area is a directory INSIDE the public document root that holds
 * user-managed assets (a hand-made website, images, css, js…) together with the
 * rules for what may be written into it.
 *
 * It is deliberately split from its consumers:
 *
 *   - the admin UI (mwmod_mw_staticfiles_ui_adminui) → DevExtreme file manager
 *   - the MCP tool layer (mwmod_mw_staticfiles_tools) → JSON tools for an agent
 *
 * Both call the same methods, so behaviour can never diverge between the two.
 *
 * ## Two kinds of writable content
 *
 *   1. Files inside sub-folders. Allowed while `set_allow_subfolders(true)` and
 *      the extension passes the policy. Upload / rename / delete / write.
 *
 *   2. Files at the ROOT of the area. These are declared one by one through
 *      `set_editable_root_files(array)`. The area root is otherwise read-only:
 *      nothing can be created, renamed or deleted there, but the declared files
 *      CAN be written (that is how `index.html` is edited without being
 *      renamed, moved or re-created).
 *
 * ## Why the root is special
 *
 * The scaffold of a Meralda site owns `public_html/index.php`; a static-site
 * area lives at `public_html/ws/`. If the UI could freely create files at the
 * area root, a single upload could drop an executable next to `index.html` or
 * replace it silently. Declaring the editable list keeps the root closed.
 *
 * ## Extension of this class
 *
 * A project subclasses it and decides four things:
 *
 *   function get_init_root_subpath()   { return "ws"; }            // public_html/ws
 *   function get_init_url_base()       { return "/ws"; }           // public URL
 *   function get_init_editable_root_files() { return array("index.html"); }
 *   function get_init_policy()         { return new mwmod_mw_staticfiles_policy(); }
 */
abstract class mwmod_mw_staticfiles_man extends mw_apsubbaseobj {

	/** Path mode used to resolve the root ("public" = document root). */
	private $path_mode      = 'public';
	/** Sub-path inside the path mode. */
	private $root_subpath   = '';
	/** Public URL base (no trailing slash). */
	private $url_base       = '';
	/** Role label: "live" | "draft" | "canonical" (informative only). */
	private $role           = 'live';
	/** Root-level files that may be written. Lower-case, relative to root. */
	private $editable_root_files = null;
	/** Allow creating/editing anything inside sub-folders. */
	private $allow_subfolders = true;
	/** Allow deleting / renaming (sub-folders only). */
	private $allow_delete   = true;
	private $allow_rename   = true;
	/** Allow creating sub-folders. */
	private $allow_mkdir    = true;
	/**
	 * Close the area for writing, whatever the other flags say.
	 *
	 * SEPARATE FROM THE allow_* FLAGS ON PURPOSE. Those answer "may this KIND of
	 * change happen here" (a root file, a sub-folder, a delete) and are a
	 * property of the area. This one answers "may anything change today" and is
	 * a property of the DEPLOYMENT — an operator freezing the live copy while a
	 * release is being cut, say.
	 *
	 * It is checked BEFORE every other rule, so turning it on cannot be undone by
	 * a flag set to true somewhere else, and reading stays untouched: the area
	 * remains browsable and downloadable, which is what makes it useful as a
	 * reference while it is frozen.
	 */
	private $read_only      = false;
	/** Hide dot-files / dot-folders from listings. */
	private $hide_dotfiles   = true;
	/** Max bytes accepted for a single write/upload. 0 = use fileman limit. */
	private $max_file_bytes = 0;
	/** @var mwmod_mw_staticfiles_policy|null */
	private $_policy;
	/** @var mwmod_mw_helper_fileman|null */
	private $_fileman;

	function __construct($ap = false) {
		if ($ap) {
			$this->init($ap);
		} else {
			$this->set_mainap();
		}
	}

	// ====================================================================
	// Override points
	// ====================================================================

	/** Sub-path inside the path mode, e.g. "ws" → <docroot>/ws */
	function get_init_root_subpath() {
		return '';
	}

	/** Public URL base, e.g. "/ws". Defaults to the root sub-path. */
	function get_init_url_base() {
		$sub = trim($this->get_init_root_subpath(), '/');
		return $sub === '' ? '' : '/' . $sub;
	}

	/**
	 * Root-level files that may be written.
	 *
	 * Only these are writable at the root of the area. Everything else at the
	 * root is read-only; sub-folders are writable through the policy.
	 *
	 * @return string[]
	 */
	function get_init_editable_root_files() {
		return array();
	}

	/** @return mwmod_mw_staticfiles_policy */
	function get_init_policy() {
		return new mwmod_mw_staticfiles_policy();
	}

	/**
	 * Absolute root of the area, when it does not come from a path mode.
	 *
	 * Return a string to point the area at any directory on disk (a private
	 * draft outside the document root, a git-tracked canonical copy…). Return
	 * null to build the path from `path_mode` + `root_subpath`.
	 *
	 * @return string|null
	 */
	function get_init_abs_root() {
		return null;
	}

	/** Human label shown in the admin UI and in MCP responses. */
	function get_area_label() {
		return $this->get_root_subpath();
	}

	// ====================================================================
	// Configuration
	// ====================================================================

	final function init($ap) {
		$this->set_mainap($ap);
		$this->root_subpath = trim((string)$this->get_init_root_subpath(), '/');
		$this->url_base     = rtrim((string)$this->get_init_url_base(), '/');
	}

	final function set_path_mode($mode) {
		$this->path_mode = $mode;
	}

	final function get_path_mode() {
		return $this->path_mode;
	}

	final function set_root_subpath($sub) {
		$this->root_subpath = trim((string)$sub, '/');
		$this->_abs_root    = null;
	}

	final function get_root_subpath() {
		return $this->root_subpath;
	}

	final function set_url_base($url) {
		$this->url_base = rtrim((string)$url, '/');
	}

	final function get_url_base() {
		return $this->url_base;
	}

	/**
	 * Point the area at an absolute directory, bypassing path modes.
	 *
	 * Needed for areas that live outside both the document root and the
	 * standard modes — e.g. the private `draft` served through an authenticated
	 * subdomain, or the git-tracked `canonical` copy under src/websites/.
	 *
	 * The caller is responsible for passing a trusted path (it comes from code,
	 * never from user input).
	 *
	 * @param string|null $abs_path  null to fall back to the path mode
	 */
	final function set_abs_root($abs_path) {
		if ($abs_path === null) {
			$this->_abs_root = null;
			$this->_abs_root_ready = false;
			return;
		}
		$this->_abs_root = rtrim((string)$abs_path, '/');
		$this->_abs_root_ready = true;
	}

	/**
	 * Informative role of the area: "live", "draft" or "canonical".
	 * It changes nothing in the rules; it is used for labels and MCP responses.
	 */
	final function set_role($role) {
		$this->role = trim((string)$role);
	}

	final function get_role() {
		return $this->role;
	}

	/**
	 * Declare the root-level files that may be written.
	 *
	 * @param string[] $files
	 */
	final function set_editable_root_files($files) {
		$r = array();
		foreach ((array)$files as $f) {
			$f = trim((string)$f, '/');
			if ($f !== '') {
				$r[] = $f;
			}
		}
		$this->editable_root_files = $r;
	}

	/** @return string[] */
	final function get_editable_root_files() {
		if (!isset($this->editable_root_files)) {
			$this->editable_root_files = (array)$this->get_init_editable_root_files();
		}
		return $this->editable_root_files;
	}

	final function set_allow_subfolders($v)    { $this->allow_subfolders = (bool)$v; }
	final function set_allow_delete($v)        { $this->allow_delete     = (bool)$v; }
	final function set_allow_rename($v)        { $this->allow_rename     = (bool)$v; }
	final function set_allow_mkdir($v)         { $this->allow_mkdir      = (bool)$v; }
	final function set_max_file_bytes($bytes)  { $this->max_file_bytes   = (int)$bytes; }

	/**
	 * Freeze or unfreeze the area.
	 *
	 * A frozen area still lists, reads and downloads; it refuses every write,
	 * rename, delete and folder creation. See the property docblock for why this
	 * is not one more allow_* flag.
	 *
	 * @param bool $v
	 */
	final function set_read_only($v) { $this->read_only = (bool)$v; }

	final function is_read_only() { return (bool)$this->read_only; }

	final function is_subfolders_allowed() { return $this->allow_subfolders; }
	final function is_delete_allowed()     { return (bool)$this->allow_delete     && !$this->read_only; }
	final function is_rename_allowed()     { return (bool)$this->allow_rename     && !$this->read_only; }
	final function is_mkdir_allowed()      { return (bool)$this->allow_mkdir      && !$this->read_only; }

	/** Show dot-files in listings (default: hidden). */
	final function set_show_dotfiles($v) {
		$this->hide_dotfiles = !$v;
	}

	final function get_show_dotfiles() {
		return !$this->hide_dotfiles;
	}

	/**
	 * Names that exist in the folder but are never offered in the browser.
	 *
	 * THE SITE'S CONTENT IS NOT EVERYTHING IN THE DIRECTORY. A document root
	 * accumulates files that belong to the HOST, not to the site: `php.ini` and
	 * `.user.ini` written by the control panel, `cgi-bin` created with the
	 * domain, `web.config` on IIS. They have to stay where they are — deleting
	 * `php.ini` changes how PHP runs, deleting `cgi-bin` breaks the subdomain —
	 * and they are not content, so listing them only offers rows whose every
	 * action is refused.
	 *
	 * They are still READABLE BY PATH, exactly like dot-files: the deploy scripts
	 * and the server itself address them directly. "Not listed" is not "not
	 * there".
	 *
	 * BUILT FROM THE POLICY where the policy already knows: every denied filename
	 * (`php.ini`, `.htpasswd`, `.env`, `web.config`, `composer.json`...) is server
	 * configuration by definition, so it is hidden for the same reason it is not
	 * writable. The list adds the ones that are not files-with-an-extension and
	 * therefore have no place in an extension policy at all.
	 *
	 * @return string[]
	 */
	function get_hidden_names() {
		$names = (array)$this->get_init_hidden_names();

		// Everything the policy refuses by name: config files.
		foreach ((array)$this->get_policy()->get_denied_filenames() as $n) {
			$names[] = $n;
		}

		return array_values(array_unique(array_filter(array_map('strval', $names), 'strlen')));
	}

	/**
	 * Area-specific additions to `get_hidden_names()`.
	 *
	 * `cgi-bin` is here rather than in every area's own list: modern cPanel
	 * creates it next to a document root whether or not anything uses it, and it
	 * is a server folder in a site's folder. A project that genuinely serves CGI
	 * from its static area can override this method.
	 *
	 * @return string[]
	 */
	function get_init_hidden_names() {
		return array('cgi-bin');
	}

	/**
	 * Paths that are CODE, not content, and that this manager must never touch.
	 *
	 * HIDING IS NOT PROTECTING. `get_hidden_names()` removes a name from the
	 * listing, which is a courtesy to the person using the panel; a request that
	 * names the path directly — the file manager's own endpoints, the MCP tools,
	 * a future API — would still reach it. So a protected path is refused by the
	 * guards themselves: no write, no upload, no folder, no rename, no delete.
	 *
	 * WHY AN AREA WOULD NEED THIS: a static website is static, and the moment a
	 * folder in it runs code (the draft's `/get` router, which exists only so the
	 * form works on the private host exactly as it does in public), editing that
	 * folder through a file browser is a way to execute anything. The router is
	 * not content: it is the application, sitting where the document root put it.
	 *
	 * Relative paths, matched case-insensitively, and a folder covers everything
	 * below it.
	 *
	 * @return string[]
	 */
	function get_protected_paths() {
		return array();
	}

	/**
	 * True when the relative path is a protected path or lives inside one.
	 *
	 * @param string $relpath
	 * @return bool
	 */
	final function is_protected_path($relpath) {
		if (!is_string($relpath) && !is_numeric($relpath)) {
			return false;
		}
		$rel = strtolower(trim(str_replace('\\', '/', (string)$relpath), '/'));
		if ($rel === '') {
			return false;
		}
		foreach ((array)$this->get_protected_paths() as $p) {
			$p = strtolower(trim(str_replace('\\', '/', (string)$p), '/'));
			if ($p === '') {
				continue;
			}
			if ($rel === $p || strpos($rel, $p . '/') === 0) {
				return true;
			}
		}
		return false;
	}

	/**
	 * True when a file/folder name must be left out of listings.
	 *
	 * Three reasons, in this order: a leading dot (server configuration and
	 * editor/VCS leftovers), an explicit name from `get_hidden_names()`, or — for
	 * the areas that ask for it — an entry the caller could not act on anyway.
	 * They stay reachable by absolute path for the deploy scripts, but are not
	 * offered in the browser.
	 *
	 * @param string $name
	 * @return bool
	 */
	function is_hidden_name($name) {
		if (!is_string($name) || $name === '') {
			return false;
		}

		// A leading dot: server configuration and editor/VCS leftovers.
		if ($this->hide_dotfiles && substr($name, 0, 1) === '.') {
			return true;
		}

		// Names the host owns rather than the site: php.ini, cgi-bin, web.config.
		// CASE-INSENSITIVE, because the same file arrives spelled differently
		// depending on who created it, and a name that hides only in one spelling
		// is worse than no rule at all.
		$lower = strtolower($name);
		foreach ($this->get_hidden_names() as $hidden) {
			if ($lower === strtolower($hidden)) {
				return true;
			}
		}

		return false;
	}

	// ====================================================================
	// Framework helpers
	// ====================================================================

	/** @return mwmod_mw_staticfiles_policy */
	final function get_policy() {
		if (!isset($this->_policy)) {
			$this->_policy = $this->get_init_policy();
		}
		return $this->_policy;
	}

	/** @return mwmod_mw_helper_fileman|false */
	final function get_file_man() {
		if (isset($this->_fileman)) {
			return $this->_fileman;
		}
		$this->_fileman = false;
		if ($fm = $this->mainap->get_submanager("fileman")) {
			$this->_fileman = $fm;
		}
		return $this->_fileman;
	}

	/** @return mwmod_mw_ap_paths|false */
	final function get_path_man() {
		return $this->mainap->get_path_man($this->path_mode);
	}

	private $_abs_root;

	/**
	 * Absolute path of the area root (no trailing slash).
	 *
	 * Resolution order:
	 *   1. set_abs_root()             — explicit, wins over everything
	 *   2. get_init_abs_root()        — subclass hook (private/git areas)
	 *   3. path_mode + root_subpath   — normal public areas
	 *
	 * @return string|false
	 */
	final function get_root_path() {
		if (isset($this->_abs_root) && $this->_abs_root !== null) {
			return $this->_abs_root;
		}
		if (isset($this->_abs_root_ready)) {
			return $this->_abs_root;
		}

		// Hook: subclasses that live outside the standard path modes.
		if (($init = $this->get_init_abs_root()) !== null) {
			$this->_abs_root = rtrim((string)$init, '/');
			$this->_abs_root_ready = true;
			return $this->_abs_root;
		}

		$this->_abs_root_ready = true;
		$this->_abs_root = false;
		if (!$base = $this->mainap->get_path($this->path_mode)) {
			return false;
		}
		$base = rtrim($base, '/');
		$p    = $this->root_subpath === '' ? $base : $base . '/' . $this->root_subpath;
		$this->_abs_root = $p;
		return $this->_abs_root;
	}

	private $_abs_root_ready;

	// ====================================================================
	// Path validation — the security boundary
	// ====================================================================

	/**
	 * Validate a path relative to the area root.
	 *
	 * An empty string means "the root itself" and is valid.
	 * Returns false when the path tries to escape, uses forbidden characters,
	 * or reaches outside the root through a symlink.
	 *
	 * @param string $relpath
	 * @return string|false  normalised relative path ("" for root)
	 */
	final function check_relpath($relpath) {
		if ($relpath === null || $relpath === false) {
			return '';
		}
		if (!is_string($relpath)) {
			return false;
		}
		$relpath = str_replace('\\', '/', $relpath);
		$relpath = trim($relpath);
		$relpath = trim($relpath, '/');
		if ($relpath === '') {
			return '';
		}

		// Reuse the framework's traversal protection (blocks "..", control
		// chars, ":*?\"<>|", segments over 255 bytes).
		$pathman = $this->get_path_man();
		if (!$pathman) {
			return false;
		}
		$safe = $pathman->check_sub_path($relpath);
		if ($safe === false) {
			return false;
		}
		return $safe;
	}

	/**
	 * Absolute path for a relative path inside the area.
	 * Returns false if the relative path is invalid or escapes the root.
	 *
	 * @param string $relpath
	 * @return string|false
	 */
	final function get_abs_path($relpath) {
		$rel = $this->check_relpath($relpath);
		if ($rel === false) {
			return false;
		}
		if (!$root = $this->get_root_path()) {
			return false;
		}
		if ($rel === '') {
			return $root;
		}
		$abs = $root . '/' . $rel;

		// Symlink containment: when the parent exists, its realpath must still be
		// inside the root. This stops a symlink planted in the area from turning
		// into a write outside the document root.
		//
		// Both sides are normalised to forward slashes before comparing: realpath()
		// returns native separators (backslashes on Windows) while $root may have
		// been built with "/", and a mixed comparison fails.
		$parent = dirname($abs);
		if (is_dir($parent) && ($realParent = realpath($parent))) {
			$realRoot = realpath($root);
			if ($realRoot) {
				$rp = str_replace('\\', '/', $realParent);
				$rr = str_replace('\\', '/', rtrim($realRoot, '/'));
				if (strpos($rp . '/', $rr . '/') !== 0) {
					return false;
				}
			}
		}

		return $abs;
	}

	/**
	 * Normalised public URL for a relative path.
	 *
	 * @param string $relpath
	 * @return string
	 */
	final function get_url($relpath) {
		$rel = $this->check_relpath($relpath);
		if ($rel === false) {
			return '';
		}
		$base = $this->url_base;
		if ($rel === '') {
			return $base . '/';
		}
		return $base . '/' . $rel;
	}

	// ====================================================================
	// Write rules
	// ====================================================================

	/**
	 * Is this relative path a root-level file declared as editable?
	 *
	 * @param string $relpath
	 * @return bool
	 */
	final function is_editable_root_file($relpath) {
		$rel = $this->check_relpath($relpath);
		if ($rel === false || $rel === '') {
			return false;
		}
		// Only direct children of the root qualify.
		if (strpos($rel, '/') !== false) {
			return false;
		}
		foreach ($this->get_editable_root_files() as $f) {
			if (strcasecmp(trim($f, '/'), $rel) === 0) {
				return true;
			}
		}
		return false;
	}

	/** True when the path points into a sub-folder (not the root). */
	final function is_in_subfolder($relpath) {
		$rel = $this->check_relpath($relpath);
		if ($rel === false || $rel === '') {
			return false;
		}
		return strpos($rel, '/') !== false;
	}

	/**
	 * May the CONTENT of this file be written (created or overwritten)?
	 *
	 * - root-level file  → only when declared in editable_root_files
	 * - sub-folder file  → when allow_subfolders + policy allows the extension
	 *
	 * @param string $relpath
	 * @return bool
	 */
	function is_writable_file($relpath) {
		// The freeze is checked first: it must win over any flag set to true.
		if ($this->read_only) {
			return false;
		}
		$rel = $this->check_relpath($relpath);
		if ($rel === false || $rel === '') {
			return false;
		}
		// Application code is not content, whatever the flags say.
		if ($this->is_protected_path($rel)) {
			return false;
		}
		if (!$this->get_policy()->is_filename_allowed($rel)) {
			return false;
		}
		if (!$this->is_in_subfolder($rel)) {
			return $this->is_editable_root_file($rel);
		}
		return (bool)$this->allow_subfolders;
	}

	/**
	 * May this path be deleted?
	 *
	 * - root-level FILE  → never (that is the whole point of the editable list)
	 * - root-level FOLDER→ yes, when allow_delete (structure is not content)
	 * - sub-folder       → yes, when allow_delete
	 *
	 * @param string $relpath
	 * @return bool
	 */
	function is_deletable_path($relpath) {
		if ($this->read_only) {
			return false;
		}
		$rel = $this->check_relpath($relpath);
		if ($rel === false || $rel === '') {
			return false;
		}
		if ($this->is_protected_path($rel)) {
			return false;
		}
		if (!$this->is_in_subfolder($rel)) {
			return (bool)$this->allow_delete && $this->is_existing_dir($rel);
		}
		return (bool)$this->allow_delete;
	}

	/**
	 * May this path be renamed/moved?
	 *
	 * Same rule as delete: root-level files are pinned (their name is part of
	 * the contract), root-level folders and anything in a sub-folder can move.
	 *
	 * @param string $relpath
	 * @return bool
	 */
	function is_renamable_path($relpath) {
		if ($this->read_only) {
			return false;
		}
		$rel = $this->check_relpath($relpath);
		if ($rel === false || $rel === '') {
			return false;
		}
		if ($this->is_protected_path($rel)) {
			return false;
		}
		if (!$this->is_in_subfolder($rel)) {
			return (bool)$this->allow_rename && $this->is_existing_dir($rel);
		}
		return (bool)$this->allow_rename;
	}

	/** True when the relative path points to a directory that exists. */
	final function is_existing_dir($relpath) {
		$abs = $this->get_abs_path($relpath);
		return $abs ? is_dir($abs) : false;
	}

	/** Effective maximum size in bytes for one file. */
	final function get_max_file_bytes() {
		if ($this->max_file_bytes > 0) {
			return $this->max_file_bytes;
		}
		if ($fm = $this->get_file_man()) {
			if ($b = $fm->get_max_upload_size_bytes()) {
				return (int)$b;
			}
		}
		return 0;
	}

	// ====================================================================
	// Operations
	// ====================================================================

	/** @return mwmod_mw_staticfiles_item|false */
	function get_item($relpath) {
		$rel = $this->check_relpath($relpath);
		if ($rel === false) {
			return false;
		}
		return new mwmod_mw_staticfiles_item($rel, $this);
	}

	/**
	 * List a directory.
	 *
	 * Dot-files and dot-folders are skipped by default: they are server
	 * configuration (`.htaccess`, `.user.ini`, `.env`…) that the editor has no
	 * business showing, and an accidental rename or delete there takes the site
	 * down. `set_show_dotfiles(true)` reveals them for troubleshooting.
	 *
	 * @param string $relpath  "" = root
	 * @return array|false     array of items data; false on error
	 */
	function list_dir($relpath = '') {
		$rel = $this->check_relpath($relpath);
		if ($rel === false) {
			return false;
		}
		$abs = $this->get_abs_path($rel);
		if (!$abs || !is_dir($abs)) {
			return false;
		}
		$names = @scandir($abs);
		if ($names === false) {
			return false;
		}
		$dirs  = array();
		$files = array();
		foreach ($names as $name) {
			if ($name === '.' || $name === '..') {
				continue;
			}
			$childRel = $rel === '' ? $name : $rel . '/' . $name;
			// Hidden by name (dot-files, server files) or protected (code that
			// happens to live under this document root).
			if ($this->is_hidden_name($name) || $this->is_protected_path($childRel)) {
				continue;
			}
			$item     = $this->get_item($childRel);
			if (!$item) {
				continue;
			}
			$data = $item->get_data();
			if ($item->is_dir()) {
				$dirs[] = $data;
			} else {
				$files[] = $data;
			}
		}
		// Folders first, then files, each alphabetically.
		$byName = function ($a, $b) {
			return strcasecmp($a['name'], $b['name']);
		};
		usort($dirs, $byName);
		usort($files, $byName);
		return array_merge($dirs, $files);
	}

	/**
	 * Read a text file.
	 *
	 * READING IS NOT WRITING, and the guard says so: the check is readability
	 * (text extension, allowed name), not editability. Tying the two together
	 * meant a frozen area — and the canonical copy, which is read-only on
	 * purpose — answered every read with false, so the reference could not be
	 * consulted at all.
	 *
	 * What is still refused: binaries and anything the policy blocks, because
	 * the point of the strict check is to keep a huge file out of a textarea.
	 *
	 * @param string $relpath
	 * @param bool   $allow_any  read even a non-text file (still policy-checked)
	 * @return string|false
	 */
	function read_file($relpath, $allow_any = false) {
		$item = $this->get_item($relpath);
		if (!$item || !$item->exists() || $item->is_dir()) {
			return false;
		}
		if (!$this->get_policy()->is_filename_allowed($relpath)) {
			return false;
		}
		if (!$allow_any && !$item->is_readable()) {
			return false;
		}
		$max = $this->get_max_file_bytes();
		if ($max > 0 && filesize($item->get_abspath()) > $max) {
			return false;
		}
		return @file_get_contents($item->get_abspath());
	}

	/**
	 * Write (create or overwrite) a text file.
	 *
	 * @param string $relpath
	 * @param string $content
	 * @return array  ['ok'=>bool, 'msg'=>string]
	 */
	function write_file($relpath, $content) {
		$rel = $this->check_relpath($relpath);
		if ($rel === false || $rel === '') {
			return $this->err("Invalid path.");
		}
		if (!$this->is_writable_file($rel)) {
			return $this->err($this->explain_not_writable($rel));
		}
		if (!is_string($content)) {
			return $this->err("Content must be a string.");
		}
		$max = $this->get_max_file_bytes();
		if ($max > 0 && strlen($content) > $max) {
			return $this->err("Content exceeds the maximum size.");
		}

		$abs = $this->get_abs_path($rel);
		if (!$abs) {
			return $this->err("Invalid path.");
		}

		// Create the parent folder when the file lives in a sub-folder.
		$dir = dirname($abs);
		if (!is_dir($dir)) {
			if (!$this->is_in_subfolder($rel)) {
				return $this->err("Destination folder does not exist.");
			}
			if (!($fm = $this->get_file_man()) || !$fm->create_dir($dir)) {
				return $this->err("Could not create the destination folder.");
			}
		}

		// Write atomically so a failed write never truncates a live file.
		$tmp = $abs . '.tmp' . bin2hex(random_bytes(4));
		if (@file_put_contents($tmp, $content) === false) {
			return $this->err("Could not write the file.");
		}
		if (!@rename($tmp, $abs)) {
			@unlink($tmp);
			return $this->err("Could not replace the file.");
		}
		@chmod($abs, 0644);

		return array(
			'ok'  => true,
			'msg' => "File saved.",
			'item' => $this->get_item($rel)->get_data(true),
		);
	}

	/**
	 * Write raw bytes (used by the MCP layer with base64 content).
	 *
	 * @param string $relpath
	 * @param string $bytes
	 * @return array
	 */
	function write_bytes($relpath, $bytes) {
		return $this->write_file($relpath, $bytes);
	}

	/**
	 * Handle a browser upload ($_FILES[$inputname]).
	 *
	 * Delegates the file-system work to the framework fileman, but runs the
	 * area policy first so a `.php` can never reach the disk.
	 *
	 * @param string $inputname  $_FILES key
	 * @param string $dir_rel    destination folder, relative to the area root
	 * @return array
	 */
	function upload_from_request($inputname, $dir_rel = '') {
		$dir_rel = $this->check_relpath($dir_rel);
		if ($dir_rel === false) {
			return $this->err("Invalid destination folder.");
		}
		if ($dir_rel !== '' && !$this->allow_subfolders) {
			return $this->err("Uploads into sub-folders are disabled.");
		}
		if ($dir_rel === '') {
			return $this->err("Files cannot be uploaded to the area root.");
		}

		if (!isset($_FILES[$inputname]) || !is_array($_FILES[$inputname])) {
			return $this->err("No file received.");
		}
		$f = $_FILES[$inputname];
		if (isset($f['name']) && is_array($f['name'])) {
			return $this->err("Only one file per upload is supported here.");
		}
		if (!isset($f['error']) || $f['error'] !== UPLOAD_ERR_OK) {
			return $this->err("Upload failed (error " . (int)($f['error'] ?? -1) . ").");
		}

		// Policy first: never let an executable reach fileman.
		$name = basename((string)$f['name']);
		if (!$this->get_policy()->is_filename_allowed($name)) {
			return $this->err("File type not allowed: " . $name);
		}

		$max = $this->get_max_file_bytes();
		if ($max > 0 && (int)$f['size'] > $max) {
			return $this->err("File is larger than the maximum allowed size.");
		}

		$destRel = $dir_rel . '/' . $name;
		if (!$this->is_writable_file($destRel)) {
			return $this->err($this->explain_not_writable($destRel));
		}

		$absDir = $this->get_abs_path($dir_rel);
		if (!$absDir) {
			return $this->err("Invalid destination folder.");
		}

		$fm = $this->get_file_man();
		if (!$fm) {
			return $this->err("File manager unavailable.");
		}
		if (!is_dir($absDir) && !$fm->create_dir($absDir)) {
			return $this->err("Could not create the destination folder.");
		}

		// Allowed ext list is enforced by us; pass the policy list to fileman so
		// its own check agrees instead of using the global cfg value.
		//
		// $urlsercurefilename = true is intentional: the framework lowercases
		// the name and replaces every non-alphanumeric character with "_"
		// ("My Photo 1.JPG" -> "my_photo_1.jpg"). That is the house rule for a
		// static website (no spaces, no capitals, no surprises on Linux), but
		// the user MUST be told, because the file will not keep the name they
		// typed. See the `renamed` key below and the UI notice.
		$allowed = $this->get_policy()->get_allowed_exts();
		$saved   = @$fm->upload_file($inputname, $absDir, false, false, false, true, true, $allowed);
		if (!$saved || !is_string($saved)) {
			return $this->err("The file was rejected. Check the extension.");
		}

		// upload_file() returns the FINAL file name. Never recompute it from
		// $_FILES: it may differ (sanitising), and the returned item would then
		// point at a path that does not exist.
		$finalRel = $dir_rel . '/' . basename($saved);
		$savedName = basename($saved);
		return array(
			'ok'        => true,
			'msg'       => "File uploaded.",
			'orig_name' => $name,
			'saved_name' => $savedName,
			'renamed'   => ($savedName !== $name),
			'item'      => $this->get_item($finalRel)->get_data(),
		);
	}

	/**
	 * Create a sub-folder.
	 *
	 * @param string $relpath
	 * @return array
	 */
	function mkdir($relpath) {
		if ($this->read_only) {
			return $this->err("This area is read-only.");
		}
		$rel = $this->check_relpath($relpath);
		if ($rel === false || $rel === '') {
			return $this->err("Invalid folder name.");
		}
		if (!$this->allow_mkdir || !$this->allow_subfolders) {
			return $this->err("Creating folders is disabled.");
		}
		if ($this->is_protected_path($rel)) {
			return $this->err($this->explain_protected_path());
		}
		// Folders may be created at the root (that is structure, not content):
		// the editable-root rule only pins root FILES.
		$abs = $this->get_abs_path($rel);
		if (!$abs) {
			return $this->err("Invalid folder name.");
		}
		if (file_exists($abs)) {
			return $this->err("That name already exists.");
		}
		$fm = $this->get_file_man();
		if (!$fm || !$fm->create_dir($abs)) {
			return $this->err("Could not create the folder.");
		}
		return array('ok' => true, 'msg' => "Folder created.", 'item' => $this->get_item($rel)->get_data());
	}

	/**
	 * Rename or move a file/folder inside the area.
	 * Root-level entries can never be renamed.
	 *
	 * @param string $from_rel
	 * @param string $to_rel
	 * @return array
	 */
	function rename($from_rel, $to_rel) {
		$from = $this->check_relpath($from_rel);
		$to   = $this->check_relpath($to_rel);
		if ($from === false || $to === false || $from === '' || $to === '') {
			return $this->err("Invalid path.");
		}
		if (!$this->is_renamable_path($from)) {
			return $this->err($this->explain_not_renamable($from));
		}
		// The destination keeps the same rules as any new file.
		if (!$this->is_writable_file($to)) {
			return $this->err($this->explain_not_writable($to));
		}
		$absFrom = $this->get_abs_path($from);
		$absTo   = $this->get_abs_path($to);
		if (!$absFrom || !$absTo) {
			return $this->err("Invalid path.");
		}
		if (!file_exists($absFrom)) {
			return $this->err("The source does not exist.");
		}
		if (file_exists($absTo)) {
			return $this->err("The destination already exists.");
		}
		$fm = $this->get_file_man();
		if (!$fm) {
			return $this->err("File manager unavailable.");
		}
		$dir = dirname($absTo);
		if (!is_dir($dir) && !$fm->create_dir($dir)) {
			return $this->err("Could not create the destination folder.");
		}
		if (!@rename($absFrom, $absTo)) {
			return $this->err("Could not rename.");
		}
		return array('ok' => true, 'msg' => "Renamed.", 'item' => $this->get_item($to)->get_data());
	}

	/**
	 * Delete a file or an empty folder inside a sub-folder.
	 *
	 * @param string $relpath
	 * @param bool   $recursive  delete folders recursively
	 * @return array
	 */
	function delete($relpath, $recursive = false) {
		$rel = $this->check_relpath($relpath);
		if ($rel === false || $rel === '') {
			return $this->err("Invalid path.");
		}
		if (!$this->is_deletable_path($rel)) {
			return $this->err($this->explain_not_deletable($rel));
		}
		$abs = $this->get_abs_path($rel);
		if (!$abs || !file_exists($abs)) {
			return $this->err("The file does not exist.");
		}
		$fm = $this->get_file_man();
		if (!$fm) {
			return $this->err("File manager unavailable.");
		}
		if (is_dir($abs) && $recursive) {
			if (!$fm->delete_path($abs)) {
				return $this->err("Could not delete the folder.");
			}
		} else {
			if (!@unlink($abs)) {
				return $this->err("Could not delete (a folder must be empty).");
			}
			@$fm->delete_empty_dirs(dirname($abs));
		}
		return array('ok' => true, 'msg' => "Deleted.", 'path' => $rel);
	}

	// ====================================================================
	// Diagnostics / data
	// ====================================================================

	/** @return array */
	function get_data() {
		$root = $this->get_root_path();
		return array(
			'label'          => $this->get_area_label(),
			'role'           => $this->role,
			'path_mode'      => $this->path_mode,
			'root_subpath'   => $this->root_subpath,
			'root_path'      => $root ?: '',
			'root_exists'    => $root ? is_dir($root) : false,
			'url_base'       => $this->url_base,
			'editable_root'  => $this->get_editable_root_files(),
			'allow_subfolders' => (bool)$this->allow_subfolders,
			'allow_delete'   => (bool)$this->allow_delete,
			'allow_rename'   => (bool)$this->allow_rename,
			'allow_mkdir'    => (bool)$this->allow_mkdir,
			'read_only'      => (bool)$this->read_only,
			// What the caller can actually do, once every flag has had its say.
			// A client asking "is this writable" should not have to re-implement
			// the precedence between read_only and the allow_* flags.
			'writable'       => !$this->read_only && ((bool)$this->allow_subfolders
				|| (bool)$this->get_editable_root_files()),
			'show_dotfiles'  => (bool)$this->get_show_dotfiles(),
			'max_file_bytes' => $this->get_max_file_bytes(),
			'policy'         => $this->get_policy()->get_debug_data(),
		);
	}

	// ====================================================================
	// Messages
	// ====================================================================

	function err($msg) {
		return array('ok' => false, 'msg' => $msg);
	}

	function explain_not_writable($rel) {
		if ($this->read_only) {
			return "This area is read-only: it can be browsed and downloaded, but not changed.";
		}
		if ($this->is_protected_path($rel)) {
			return $this->explain_protected_path();
		}
		$policy = $this->get_policy();
		if ($policy->is_filename_allowed($rel) === false) {
			// Distinguish the three reasons: blocked name, blocked ext, no ext.
			$base = strtolower(basename($rel));
			foreach ((array)$policy->get_denied_filenames() as $denied) {
				if ($base === strtolower($denied)) {
					return "Server configuration files cannot be written: " . basename($rel);
				}
			}
			$ext = $policy->get_ext_from_filename($rel);
			if ($ext === false) {
				return "Files without an extension are not allowed.";
			}
			return "File type not allowed: ." . $ext;
		}
		if (!$this->is_in_subfolder($rel)) {
			return "This root file is not in the editable list.";
		}
		return "Writing into sub-folders is disabled.";
	}

	function explain_not_deletable($rel) {
		if ($this->read_only) {
			return "This area is read-only: nothing can be deleted.";
		}
		if ($this->is_protected_path($rel)) {
			return $this->explain_protected_path();
		}
		if (!$this->is_in_subfolder($rel)) {
			return "Root files cannot be deleted.";
		}
		return "Deleting is disabled.";
	}

	function explain_not_renamable($rel) {
		if ($this->read_only) {
			return "This area is read-only: nothing can be renamed.";
		}
		if ($this->is_protected_path($rel)) {
			return $this->explain_protected_path();
		}
		if (!$this->is_in_subfolder($rel)) {
			return "Root files cannot be renamed.";
		}
		return "Renaming is disabled.";
	}

	/**
	 * The one sentence every protected-path refusal gives.
	 *
	 * Written for the person reading it, not for the developer: it says WHAT the
	 * folder is (part of the application) rather than that a permission was
	 * missing, because "not allowed" in a file manager reads like a bug.
	 *
	 * @return string
	 */
	function explain_protected_path() {
		return "This folder is part of the application, not of the website: "
			. "it can be browsed but not created, changed, renamed or deleted here.";
	}
}
?>
