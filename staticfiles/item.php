<?php
/**
 * One entry (file or folder) inside a static-files area.
 *
 * Created by mwmod_mw_staticfiles_man::get_item(). Holds no open resources: it
 * is a value object that stats the file system lazily.
 *
 * Deliberately NOT a mw_baseobj subclass: it is a small value object, so the
 * magic-property machinery would only add indirection. The accessors are
 * explicit methods, which also keeps them usable from inside the class.
 */
class mwmod_mw_staticfiles_item {

	private $man;
	private $relpath;
	private $_abs  = null;
	private $_stat = null;

	function __construct($relpath, $man) {
		$this->man     = $man;
		$this->relpath = (string)$relpath;
	}

	// --------------------------------------------------------------------
	// Basic accessors
	// --------------------------------------------------------------------

	/** @return mwmod_mw_staticfiles_man */
	function get_man() {
		return $this->man;
	}

	/** Path relative to the area root. "" = the root itself. */
	function get_relpath() {
		return $this->relpath;
	}

	/** File name only. Empty string for the area root. */
	function get_basename() {
		if ($this->relpath === '') {
			return '';
		}
		return basename($this->relpath);
	}

	/** Absolute path on disk (or false when the path is invalid). */
	function get_abspath() {
		if ($this->_abs === null) {
			$this->_abs = $this->man->get_abs_path($this->relpath);
		}
		return $this->_abs;
	}

	function exists() {
		$p = $this->get_abspath();
		return $p && file_exists($p);
	}

	function is_dir() {
		$p = $this->get_abspath();
		return $p && is_dir($p);
	}

	function is_root() {
		return $this->relpath === '';
	}

	function get_ext() {
		return $this->man->get_policy()->get_ext_from_filename($this->relpath);
	}

	// --------------------------------------------------------------------
	// Permission helpers — all delegated to the manager
	// --------------------------------------------------------------------

	/**
	 * True when the file can be opened in the text editor.
	 *
	 * Requires: exists, plain file, text extension, and the manager allows
	 * writing it (sub-folder, or a root file in the editable list).
	 *
	 * NOT the same as readable — see `is_readable()`. This one answers "may the
	 * editor SAVE it", which is why a frozen area refuses here.
	 */
	function is_editable() {
		if (!$this->exists() || $this->is_dir()) {
			return false;
		}
		if (!$this->man->is_writable_file($this->relpath)) {
			return false;
		}
		return $this->man->get_policy()->is_editable_ext($this->relpath);
	}

	/**
	 * True when the CONTENT may be shown, whether or not it may be changed.
	 *
	 * Reading is not writing, and the two were the same question until a frozen
	 * area had to stay useful: the freeze turned `is_editable()` false, and
	 * `read_file()` refused every file — the area became a folder listing with
	 * nothing behind it. The distinction matters for the canonical copy too,
	 * which is meant to be consulted as the approved reference.
	 *
	 * Still a text-extension check: opening a 200 MB video in a textarea helps
	 * nobody.
	 *
	 * @return bool
	 */
	function is_readable() {
		if (!$this->exists() || $this->is_dir()) {
			return false;
		}
		if (!$this->man->get_policy()->is_filename_allowed($this->relpath)) {
			return false;
		}
		return $this->man->get_policy()->is_editable_ext($this->relpath);
	}

	function is_writable() {
		if ($this->is_root()) {
			return false;
		}
		return $this->man->is_writable_file($this->relpath);
	}

	function is_deletable() {
		if ($this->is_root()) {
			return false;
		}
		return $this->man->is_deletable_path($this->relpath);
	}

	function is_renamable() {
		if ($this->is_root()) {
			return false;
		}
		return $this->man->is_renamable_path($this->relpath);
	}

	// --------------------------------------------------------------------
	// Data
	// --------------------------------------------------------------------

	/**
	 * Metadata for the UI and the MCP layer.
	 *
	 * @param bool $with_hash  compute the sha1 of the content
	 * @return array
	 */
	function get_data($with_hash = false) {
		$exists = $this->exists();
		$isDir  = $this->is_dir();
		$ext    = $this->get_ext();

		$r = array(
			'path'      => $this->relpath,
			'name'      => $this->get_basename(),
			'exists'    => $exists,
			'is_dir'    => $isDir,
			'is_root'   => $this->is_root(),
			'ext'       => $ext ? $ext : '',
			'editable'  => $this->is_editable(),
			'readable'  => $this->is_readable(),
			'writable'  => $this->is_writable(),
			'deletable' => $this->is_deletable(),
			'renamable' => $this->is_renamable(),
			'size'      => 0,
			'mtime'     => null,
			'mtime_iso' => '',
			'url'       => '',
			'mime'      => '',
		);

		if ($exists && !$isDir) {
			if ($this->_stat === null) {
				$this->_stat = @stat($this->get_abspath());
			}
			if ($this->_stat) {
				$r['size']      = (int)$this->_stat['size'];
				$r['mtime']     = (int)$this->_stat['mtime'];
				$r['mtime_iso'] = date('c', (int)$this->_stat['mtime']);
			}
			$r['mime'] = $this->man->get_policy()->get_content_type($this->relpath);
			$r['url']  = $this->man->get_url($this->relpath);

			if ($with_hash) {
				$r['sha1'] = @sha1_file($this->get_abspath());
			}
		}

		return $r;
	}
}
?>
