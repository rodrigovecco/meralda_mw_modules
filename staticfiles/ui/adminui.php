<?php
/**
 * Admin UI for a static-files area, built on the DevExtreme FileManager widget.
 *
 * One concrete subclass per area, in the PROJECT that declares the areas:
 *
 *   class <project>_ws_live_adminui extends mwmod_mw_staticfiles_ui_adminui {
 *       function getFilesMan() { return $this->mainap->ws->live; }
 *   }
 *
 * This class knows nothing about any particular site: no area name, no path, no
 * domain. Everything it displays comes from the manager it is handed, which is
 * what lets the same screen serve a private draft and a public copy.
 *
 * The widget talks to this same class over the framework `sxml` command
 * channel. Every action is forwarded to mwmod_mw_staticfiles_man, so the rules
 * are identical to the ones enforced for the MCP tools.
 *
 * Commands (registered as execfrommain_getcmd_sxml_*):
 *   list     {path}                    → entries of a folder
 *   read     {path}                    → text content
 *   write    {path, content}           → save text file
 *   mkdir    {path}                    → create folder
 *   rename   {from, to}                → rename / move
 *   delete   {path, recursive?}        → delete
 *   upload   (multipart: path, upload) → store an uploaded file
 */
abstract class mwmod_mw_staticfiles_ui_adminui extends mwmod_mw_ui_base_basesubui {

	/**
	 * Script that renders the file browser (absolute URL).
	 *
	 * Lives in the `meralda_public_res` submodule (res/meralda/staticfiles), so
	 * it travels with the submodule updates instead of being copied into every
	 * project. Parallel to src/mwap/modules/mw/staticfiles/.
	 */
	protected $js_widget_src = "/res/meralda/staticfiles/fm.js";

	/** @return mwmod_mw_staticfiles_man */
	abstract function getFilesMan();

	function __construct($cod, $parent) {
		$this->init_as_main_or_sub($cod, $parent);
		$this->set_def_title($this->lng_get_msg_txt("files", "Files"));
	}

	/**
	 * Writing requires BOTH the subinterface permission and a manager that is not
	 * frozen.
	 *
	 * TWO QUESTIONS, and they are asked in this order on purpose: `allow_edit()`
	 * is about the ACCOUNT (may this person write here at all) and the manager's
	 * read_only is about the DEPLOYMENT (may anything change today). A frozen area
	 * is frozen for the administrator too — that is what makes the switch useful
	 * while a release is being cut.
	 */
	function allow_files_write() {
		if (!$this->allow_edit()) {
			return false;
		}
		$man = $this->getFilesMan();
		if ($man && $man->is_read_only()) {
			return false;
		}
		return true;
	}

	/**
	 * Extra rows for the settings block, contributed by the project.
	 *
	 * Return an associative array of label => HTML value. Empty by default: an
	 * area knows about files, not about the endpoints that may reach it.
	 *
	 * @param array $data  the area's get_data()
	 * @return array<string,string>
	 */
	function get_extra_info_rows($data) {
		return array();
	}

	/**
	 * Load DevExtreme and the widget script before the page renders.
	 * Mirrors mwmod_mw_ui_base_dxtbladmin::before_exec().
	 */
	function before_exec() {
		parent::before_exec();
		$util = new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		if ($this->maininterface && $this->maininterface->jsmanager) {
			// External item: absolute URL, independent of the manager def_path.
			$item = new mwmod_mw_html_manager_item_jsexternal("mwstaticfiles_fm", $this->js_widget_src);
			$this->maininterface->jsmanager->add_item_by_item($item);
		}
	}

	// ====================================================================
	// Page
	// ====================================================================

	function do_exec_page_in() {
		$man = $this->getFilesMan();
		if (!$man) {
			echo "<p>" . $this->lng_get_msg_txt("not_available", "Not available.") . "</p>";
			return;
		}

		$data = $man->get_data();
		$main = $this->get_ui_dom_elem_container();
		$container = $main;

		// ---- frozen banner ---------------------------------------------
		// A read-only area that says nothing looks like a broken panel: the
		// buttons are missing, uploads do nothing and the reason is invisible.
		// The banner says why, and says what still works.
		if (!empty($data["read_only"])) {
			$bar = $container->add_cont_elem(false, "p");
			$bar->set_style("margin", "0 0 10px");
			$bar->set_style("padding", "8px 12px");
			$bar->set_style("border", "1px solid #e0c9a6");
			$bar->set_style("border-radius", "6px");
			$bar->set_style("background", "#fdf6e8");
			$bar->set_style("color", "#7a5b20");
			$bar->set_style("font-size", "0.87em");
			$bar->add_cont(
				"<b>"
				. $this->lng_get_msg_txt("fm_frozen_title", "Read-only")
				. "</b> &middot; "
				. $this->lng_get_msg_txt(
					"fm_frozen_body",
					"This area is frozen: files can be browsed, read and downloaded, but nothing "
					. "can be written, renamed, deleted or created."
				)
			);
		}

		// ---- widget mount point ----------------------------------------
		// set_ui_dom_elem_id() only registers the element; it still has to be
		// appended to the container that gets echoed.
		//
		// Height leaves room for the Edit bar above and the editor panel below
		// so the three pieces fit in one screen on a laptop.
		$mount = $this->set_ui_dom_elem_id("fm_container");
		$mount->set_style("height", "52vh");
		$mount->set_style("min-height", "320px");
		$container->add_cont($mount);

		// ---- help block (collapsed) ------------------------------------
		// Reference material, so it goes UNDER the browser and collapsed: the
		// file list keeps the screen, and the one-line summary already answers
		// "which area am I in?". Everything else is one click away.
		$help = $container->add_cont_elem(false, "details");
		$help->set_style("margin", "16px 0 0");
		$help->set_style("font-size", "0.85em");
		$help->set_style("color", "#666");
		$help->set_style("border-top", "1px solid #e6e6e6");
		$help->set_style("padding-top", "10px");

		$sum = $help->add_cont_elem(false, "summary");
		$sum->set_style("cursor", "pointer");
		$sum->set_style("color", "#444");
		$sum->add_cont(
			htmlspecialchars($data["label"])
			. " <b>[" . htmlspecialchars($data["role"]) . "]</b>"
			. " &middot; "
			. $this->lng_get_msg_txt("fm_help_show", "settings and limits")
		);

		// Two columns, no borders: label / value pairs.
		$tbl = $help->add_cont_elem(false, "table");
		$tbl->set_style("margin", "10px 0 0");
		$tbl->set_style("border-collapse", "collapse");

		$row = function ($label, $value) use ($tbl) {
			$tr = $tbl->add_cont_elem(false, "tr");
			$th = $tr->add_cont_elem(false, "th");
			$th->set_style("text-align", "left");
			$th->set_style("font-weight", "600");
			$th->set_style("color", "#555");
			$th->set_style("padding", "3px 18px 3px 0");
			$th->set_style("vertical-align", "top");
			$th->set_style("white-space", "nowrap");
			$th->add_cont($label);
			$td = $tr->add_cont_elem(false, "td");
			$td->set_style("padding", "3px 0");
			$td->set_style("vertical-align", "top");
			$td->add_cont($value);
		};

		$filesUrl = isset($data["files_url_base"]) ? (string) $data["files_url_base"] : "";
		$row(
			$this->lng_get_msg_txt("fm_help_published", "Published at"),
			// NO ADDRESS IS A REAL ANSWER, NOT AN EMPTY LINK. The canonical copy is
			// a git checkout that nothing serves, so its address is "": rendering
			// `<a href="">` would point at whatever page the reader is already on,
			// which reads as a working link to the wrong place.
			$filesUrl !== ""
				? '<a href="' . htmlspecialchars($filesUrl) . '" target="_blank" '
					. 'rel="noopener" style="color:#337ab7;">'
					. htmlspecialchars($filesUrl) . '</a>'
				: '<span style="color:#888;">'
					. htmlspecialchars($this->lng_get_msg_txt(
						"fm_help_not_served",
						"Not served anywhere — this copy lives in git, not at an address"
					))
					. '</span>'
		);

		$row(
			$this->lng_get_msg_txt("fm_help_root", "Root folder"),
			count($data["editable_root"])
				? $this->lng_get_msg_txt("fm_help_root_only", "only these files are writable")
					. ': <b>' . htmlspecialchars(implode(", ", $data["editable_root"])) . '</b>'
				: $this->lng_get_msg_txt("fm_help_root_none", "read-only")
		);

		$row(
			$this->lng_get_msg_txt("fm_help_folders", "Folders"),
			($data["allow_mkdir"] ? $this->lng_get_msg_txt("fm_help_yes", "yes") : $this->lng_get_msg_txt("fm_help_no", "no"))
			. ' &middot; '
			. ($data["allow_rename"] ? $this->lng_get_msg_txt("fm_help_yn_rename", "rename ok") : $this->lng_get_msg_txt("fm_help_yn_rename_no", "no rename"))
			. ' &middot; '
			. ($data["allow_delete"] ? $this->lng_get_msg_txt("fm_help_yn_delete", "delete ok") : $this->lng_get_msg_txt("fm_help_yn_delete_no", "no delete"))
		);

		// Dot-files are filtered out of the listing (server configuration), so
		// say it: a folder that only holds them would look empty.
		if (empty($data["show_dotfiles"])) {
			$row(
				$this->lng_get_msg_txt("fm_help_dotfiles", "Hidden files"),
				$this->lng_get_msg_txt(
					"fm_dotfiles_hint",
					"Server configuration files (.htaccess, .user.ini...) are hidden and read-only."
				)
			);
		}

		// Uploads are sanitised by the framework file manager: lowercase and
		// every non-alphanumeric character becomes "_". It is the right rule
		// for a static site, but it must be stated or the file looks lost.
		$row(
			$this->lng_get_msg_txt("fm_help_names", "File names"),
			$this->lng_get_msg_txt(
				"fm_naming_hint",
				"Uploaded names are lowercased and every character other than a letter, "
				. "digit, dot, dash or underscore becomes an underscore "
				. "(\"My Photo 1.JPG\" is saved as \"my_photo_1.jpg\")."
			)
		);

		$max = (int)$data["max_file_bytes"];
		$row(
			$this->lng_get_msg_txt("fm_help_limits", "Uploads"),
			($max > 0
				? $this->lng_get_msg_txt("fm_help_max", "max") . ' ' . round($max / 1048576, 1) . ' MB'
				: $this->lng_get_msg_txt("fm_help_max_none", "no size limit"))
			. ' &middot; '
			. $this->lng_get_msg_txt(
				"fm_help_inside_only",
				"files go inside a folder, never on the area root"
			)
		);

		// ---- rows contributed by the project --------------------------
		// THIS CLASS KNOWS NOTHING ABOUT WHAT ELSE MAY CONNECT to an area, and it
		// should not: a static-files area is reusable, and the endpoints that can
		// reach it belong to the deployment. The project answers with a
		// label => value map and it is rendered as ordinary rows, so a project can
		// document its own MCP endpoint here without this file naming it.
		foreach ((array)$this->get_extra_info_rows($data) as $label => $value) {
			$row(htmlspecialchars((string)$label), $value);
		}

		echo $main->get_as_html();

		// ---- js ---------------------------------------------------------
		$cfg = new mwmod_mw_jsobj_obj();
		// The real DOM id is prefixed by the UI (ui_elems_pref + full code),
		// so it must be resolved instead of hardcoded.
		$cfg->set_prop("containerId", $this->get_ui_elem_id("fm_container"));
		// The framework encodes the command in the URL path, so the widget gets
		// a template with a placeholder it replaces per call.
		$cfg->set_prop("sxmlUrl", $this->get_exec_cmd_sxml_url("__CMD__"));
		$cfg->set_prop("rootUrl", $data["files_url_base"]);
		$cfg->set_prop("role", $data["role"]);
		$cfg->set_prop("label", $data["label"]);
		$cfg->set_prop("editableRoot", array_values($data["editable_root"]));
		$cfg->set_prop("allowWrite", (bool)$this->allow_files_write());
		$cfg->set_prop("allowDelete", (bool)$data["allow_delete"]);
		$cfg->set_prop("allowRename", (bool)$data["allow_rename"]);
		$cfg->set_prop("allowMkdir", (bool)$data["allow_mkdir"]);			$cfg->set_prop("readOnly", (bool)!empty($data["read_only"]));		$cfg->set_prop("maxBytes", (int)$data["max_file_bytes"]);
		$cfg->set_prop("msgs", array(
			"create"   => $this->lng_get_msg_txt("fm_create", "New folder"),
			"upload"   => $this->lng_get_msg_txt("fm_upload", "Upload files"),
			"rename"   => $this->lng_get_msg_txt("fm_rename", "Rename"),
			"delete"   => $this->lng_get_msg_txt("fm_delete", "Delete"),
			"edit"     => $this->lng_get_msg_txt("fm_edit", "Edit"),
			"save"     => $this->lng_get_msg_txt("save", "Save"),
			"cancel"   => $this->lng_get_msg_txt("cancel", "Cancel"),
			"readonly" => $this->lng_get_msg_txt("fm_readonly", "Read-only"),
			"folder_name"    => $this->lng_get_msg_txt("fm_folder_name", "Folder name"),
			"folder_invalid" => $this->lng_get_msg_txt(
				"fm_folder_invalid",
				"A folder name cannot contain slashes"
			),
			"created"  => $this->lng_get_msg_txt("fm_created", "Folder created"),
			"into"     => $this->lng_get_msg_txt("fm_into", "into"),
			"into_hint" => $this->lng_get_msg_txt(
				"fm_into_hint",
				"Files are saved into this folder"
			),
			"into_root" => $this->lng_get_msg_txt(
				"fm_into_root",
				"The area root only accepts the files pinned as editable. Open a folder to upload."
			),
			"renamed"  => $this->lng_get_msg_txt(
				"fm_renamed",
				"saved with the normalised name (no spaces, no capitals)"
			),
		));

		$js = new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont(
			"if(window.mw_staticfiles_fm){ window.mw_fm_instance = new mw_staticfiles_fm("
			. $cfg->get_as_js_val() . "); }\n"
		);
		echo $js->get_js_script_html();
	}

	// ====================================================================
	// sxml commands
	// ====================================================================

	protected function json_out($arr) {
		header("Content-Type: application/json; charset=utf-8");
		echo json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	/**
	 * Read a command parameter.
	 *
	 * The framework passes URL parameters in $params, while the widget sends a
	 * POST body. Both are checked so the same handler works either way.
	 *
	 * @param array  $params
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	protected function param($params, $key, $default = '') {
		if (is_array($params) && array_key_exists($key, $params)) {
			return $params[$key];
		}
		if (isset($_POST[$key])) {
			return $_POST[$key];
		}
		if (isset($_REQUEST[$key])) {
			return $_REQUEST[$key];
		}
		return $default;
	}

	protected function guard_write() {
		$man = $this->getFilesMan();
		if ($man && $man->is_read_only()) {
			$this->json_out(array(
				"ok"  => false,
				"msg" => $this->lng_get_msg_txt(
					"fm_readonly_area",
					"This area is read-only: it can be browsed and downloaded, but not changed."
				),
			));
			return false;
		}
		if ($this->allow_files_write()) {
			return true;
		}
		$this->json_out(array("ok" => false, "msg" => "Not allowed."));
		return false;
	}

	/**
	 * Is this entry something the person looking at the screen can act on?
	 *
	 * A FOLDER IS ALWAYS ONE, because opening it IS the action. `is_writable_file()`
	 * answers "may its CONTENT be replaced", which is always false for a directory,
	 * and using that alone hid folders nobody could rename or delete — the draft's
	 * own `/get` folder among them — so a listing that was supposed to be complete
	 * quietly was not. A folder the person cannot change is still a folder she can
	 * look into, and she should be able to see that it exists.
	 *
	 * A FILE that cannot be edited, renamed or deleted has nothing to offer, and
	 * `should_hide_immutable_items()` is what removes it.
	 *
	 * @param array $it  one entry of list_dir()
	 * @return bool
	 */
	protected function is_actionable_item($it) {
		if (!empty($it["is_dir"])) {
			return true;
		}
		return !empty($it["editable"])
			|| !empty($it["deletable"])
			|| !empty($it["renamable"]);
	}

	/**
	 * Should entries that cannot be changed be left out of the listing?
	 *
	 * YES FOR A LIVE AREA, NO FOR A FROZEN ONE, and the asymmetry is the point:
	 *
	 *   - In an area somebody edits, an entry that cannot be touched is noise. It
	 *     cannot be opened in the editor, renamed or deleted, so showing it is
	 *     showing a row whose every action is refused. The area root is the case
	 *     that matters: it is closed except for the files declared in
	 *     `editable_root_files`, so a README sitting there is a row that does
	 *     nothing — and it makes the root look like it holds files it cannot.
	 *
	 *   - In a FROZEN area everything is immutable, so the same rule would empty
	 *     the screen. There the listing is a reference: the point is to see what
	 *     the approved copy contains, and a file nobody can edit is exactly what
	 *     one expects to find.
	 *
	 * @return bool
	 */
	function should_hide_immutable_items() {
		$man = $this->getFilesMan();
		if (!$man) {
			return false;
		}
		return !$man->is_read_only();
	}

	function execfrommain_getcmd_sxml_list($params = array(), $filename = false) {
		$man   = $this->getFilesMan();
		$path  = $this->param($params, "path", "");
		$items = $man->list_dir($path);
		if ($items === false) {
			$this->json_out(array("ok" => false, "msg" => "Folder not found."));
			return true;
		}
		$hide  = $this->should_hide_immutable_items();
		$out   = array();
		$hidden = 0;
		foreach ($items as $it) {
			if ($hide && !$this->is_actionable_item($it)) {
				// Counted, not silently dropped: a folder that looks emptier than
				// it is reads as a bug, and the count is what the info strip uses
				// to say so.
				$hidden++;
				continue;
			}
			// `readable` is sent separately from `editable` so the widget can open
			// a file for reading in a frozen area. Without it the panel fetched
			// nothing and printed "this file cannot be shown as text" — the
			// listing worked, the content did not.
			$out[] = array(
				"name"        => $it["name"],
				"path"        => $it["path"],
				"isDirectory" => (bool)$it["is_dir"],
				"size"        => (int)$it["size"],
				"modified"    => $it["mtime_iso"],
				"ext"         => $it["ext"],
				"url"         => $it["url"],
				"editable"    => (bool)$it["editable"],
				"readable"    => (bool)$it["readable"],
				"deletable"   => (bool)$it["deletable"],
				"renamable"   => (bool)$it["renamable"],
				"protected"   => (bool)$it["protected"],
			);
		}
		$this->json_out(array(
			"ok"     => true,
			"path"   => (string)$man->check_relpath($path),
			"items"  => $out,
			"hidden" => $hidden,
		));
		return true;
	}

	function execfrommain_getcmd_sxml_read($params = array(), $filename = false) {
		$man = $this->getFilesMan();
		$txt = $man->read_file($this->param($params, "path", ""));
		if ($txt === false) {
			$this->json_out(array("ok" => false, "msg" => "Cannot read that file."));
			return true;
		}
		$this->json_out(array("ok" => true, "content" => $txt));
		return true;
	}

	function execfrommain_getcmd_sxml_write($params = array(), $filename = false) {
		if (!$this->guard_write()) {
			return true;
		}
		$man     = $this->getFilesMan();
		$relpath = $this->param($params, "path", "");
		$result  = $man->write_file($relpath, $this->param($params, "content", ""));

		// A SAVE CAN HAVE CONSEQUENCES OUTSIDE THIS AREA, and only the project
		// knows which. The typical case: a file that is also served from a second
		// location — the same page published at two addresses — where leaving the
		// other copy behind makes an edit look like it was never saved.
		//
		// The hook is called only on success, and what it returns is appended to
		// the message the panel shows, so a secondary failure is visible instead of
		// being reported as "File saved." with something else quietly broken.
		if (!empty($result["ok"])) {
			$notice = $this->after_save_file($relpath, $result);
			if (is_string($notice) && $notice !== "") {
				$result["msg"] = rtrim((string)$result["msg"]) . " " . $notice;
			}
		}

		$this->json_out($result);
		return true;
	}

	/**
	 * Act on a file that was just written successfully.
	 *
	 * Override in the project's subclass. Called AFTER the write, with the path
	 * that was written (relative to the area root) and the manager's result.
	 *
	 * @param string $relpath
	 * @param array  $result  ['ok'=>true,'msg'=>...,'item'=>...]
	 * @return string  appended to the message shown to the user; '' for nothing
	 */
	function after_save_file($relpath, $result) {
		return "";
	}

	function execfrommain_getcmd_sxml_mkdir($params = array(), $filename = false) {
		if (!$this->guard_write()) {
			return true;
		}
		$man = $this->getFilesMan();
		$this->json_out($man->mkdir($this->param($params, "path", "")));
		return true;
	}

	function execfrommain_getcmd_sxml_rename($params = array(), $filename = false) {
		if (!$this->guard_write()) {
			return true;
		}
		$man = $this->getFilesMan();
		$this->json_out($man->rename(
			$this->param($params, "from", ""),
			$this->param($params, "to", "")
		));
		return true;
	}

	function execfrommain_getcmd_sxml_delete($params = array(), $filename = false) {
		if (!$this->guard_write()) {
			return true;
		}
		$man = $this->getFilesMan();
		$this->json_out($man->delete(
			$this->param($params, "path", ""),
			(bool)$this->param($params, "recursive", false)
		));
		return true;
	}

	/**
	 * Multipart upload. The browser posts:
	 *   path   → destination folder (must be a sub-folder)
	 *   upload → the file field
	 */
	function execfrommain_getcmd_sxml_upload($params = array(), $filename = false) {
		if (!$this->guard_write()) {
			return true;
		}
		$man = $this->getFilesMan();
		$this->json_out($man->upload_from_request("upload", $this->param($params, "path", "")));
		return true;
	}
}
?>
