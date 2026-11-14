<?php
/**
 * PHP helper for the `mw_datainput_item_tinymce` JavaScript input.
 *
 * Builds the params object consumed by `res/js/inputs/tinymce.js`, so a PHP UI
 * never has to remember the exact property names:
 *
 *     $ed = $gr->addNewTinyMCE("html", "HTML content");
 *     $ed->setHeight(420)
 *        ->setFileManUrl($this->get_url_subinterface("fileman"))
 *        ->setImageManUrl($this->get_url_subinterface("imgfm"))
 *        ->addPlaceholder("customer.fullName", "Customer full name");
 *
 * Every setter returns $this, except the `add*()` builders that return the new
 * object (placeholder item, params group).
 *
 * Note about `editorcfg`: the JS input deep-copies it and hands it to
 * `tinymce.init()`, so anything TinyMCE understands can be passed with
 * `setEditorCfg()`, including nested keys in dot notation.
 *
 * @see mw_datainput_item_tinymce (src/public_html/res/js/inputs/tinymce.js)
 * @see docs/ai/html-editor-file-manager.md
 */
class mwmod_mw_jsobj_inputs_tinymce extends mwmod_mw_jsobj_inputs_input{

	/**
	 * Placeholder manager attached to this input, if any.
	 * @var mwmod_mw_jsobj_objcoladv_base|false
	 */
	private $_placeholderman = false;

	// ============================================================
	// CONSTRUCTION
	// ============================================================

	function __construct($cod,$objclass=false){
		$this->def_js_class="mw_datainput_item_tinymce";
		$this->init_js_input($cod,$objclass);
	}

	// ============================================================
	// SIZE / LOOK
	// ============================================================

	/**
	 * Editor height in pixels.
	 * @param int $px
	 * @return $this
	 */
	function setHeight($px){
		$this->set_prop("height",$px);
		return $this;
	}

	/**
	 * TinyMCE language code (a matching file must exist in
	 * /res/thirdparty/tinymce/langs/<code>.js). Default in JS: "es".
	 * @param string $lang
	 * @return $this
	 */
	function setLang($lang){
		$this->set_prop("lang",$lang);
		return $this;
	}

	/**
	 * Fires the change event on every key press instead of only on blur.
	 * @param bool $val
	 * @return $this
	 */
	function setValidateOnKeyUp($val=true){
		$this->set_prop("validateonkeyup",$val);
		return $this;
	}

	// ============================================================
	// FILE MANAGER
	// ============================================================

	/**
	 * URL of the picker page used for links and files (the "Browse" button of
	 * the link dialog).
	 * @param string $url
	 * @return $this
	 */
	function setFileManUrl($url){
		$this->set_prop("filemanurl",$url);
		return $this;
	}

	/**
	 * URL of the picker page used for images. When not set, the JS input falls
	 * back to the file manager URL.
	 * @param string $url
	 * @return $this
	 */
	function setImageManUrl($url){
		$this->set_prop("imagemanurl",$url);
		return $this;
	}

	/**
	 * Sets both picker URLs at once. The common case: the same subinterface
	 * answers both, with `mode=images` added by the file manager subinterface.
	 * @param string $fileUrl
	 * @param string|false $imageUrl Defaults to $fileUrl.
	 * @return $this
	 */
	function setFilePickerUrls($fileUrl,$imageUrl=false){
		$this->setFileManUrl($fileUrl);
		if(!$imageUrl){
			$imageUrl=$fileUrl;
		}
		$this->setImageManUrl($imageUrl);
		return $this;
	}

	// ============================================================
	// PLUGINS / TOOLBAR
	// ============================================================

	/**
	 * Replaces the plugin list. Accepts an array or a space separated string.
	 * The JS input defaults to: lists, link, image, table, code.
	 * @param array|string $plugins
	 * @return $this
	 */
	function setPlugins($plugins){
		$this->set_prop("editorcfg.plugins",$this->list_to_js_value($plugins));
		return $this;
	}

	/**
	 * Appends plugins to the caller configured list (creating it if needed).
	 * @param array|string $plugins
	 * @return $this
	 */
	function addPlugins($plugins){
		$list=$this->get_plugins_list();
		foreach($this->list_to_array($plugins) as $p){
			if(!in_array($p,$list,true)){
				$list[]=$p;
			}
		}
		return $this->setPlugins($list);
	}

	/**
	 * Replaces the toolbar. A string uses TinyMCE syntax (`"bold | link"`);
	 * an array of strings is joined with spaces.
	 * A `| mwplaceholders` entry is appended automatically by the JS input when
	 * a placeholder manager is set.
	 * @param array|string $toolbar
	 * @return $this
	 */
	function setToolbar($toolbar){
		if(is_array($toolbar)){
			$toolbar=implode(" ",$toolbar);
		}
		$this->set_prop("editorcfg.toolbar",$toolbar);
		return $this;
	}

	/**
	 * Appends items to the current toolbar string.
	 * @param array|string $items
	 * @return $this
	 */
	function addToolbarItems($items){
		$tb=$this->get_prop("editorcfg.toolbar");
		$tb=$tb ? $tb."" : "";
		$add=is_array($items) ? implode(" ",$items) : $items;
		$add=trim($add."");
		if($add===""){
			return $this;
		}
		if($tb!==""){
			$tb.=" | ";
		}
		return $this->setToolbar($tb.$add);
	}

	/**
	 * Hides the "File / Edit / View ..." menu bar. The JS input already defaults
	 * to false; kept so a caller can turn it back on.
	 * @param bool $val
	 * @return $this
	 */
	function setMenuBar($val=false){
		$this->set_prop("editorcfg.menubar",$val);
		return $this;
	}

	// ============================================================
	// RAW EDITORCFG
	// ============================================================

	/**
	 * Sets any `tinymce.init()` option. Dot notation builds nested objects:
	 * `setEditorCfg("image_uploadtab", true)` or
	 * `setEditorCfg("importcss_file_filter", "custom.css")`.
	 * @param string $key
	 * @param mixed $val
	 * @return $this
	 */
	function setEditorCfg($key,$val){
		$this->set_prop("editorcfg.".$key,$val);
		return $this;
	}

	/**
	 * Shortcut for `content_css`, the stylesheet used inside the editable area.
	 * @param string $url
	 * @return $this
	 */
	function setContentCss($url){
		return $this->setEditorCfg("content_css",$url);
	}

	/**
	 * Restricts the HTML that survives editing.
	 * @param string $mode "html" (default in TinyMCE), "xhtml" or "html5".
	 * @return $this
	 */
	function setSchema($mode){
		return $this->setEditorCfg("schema",$mode);
	}

	// ============================================================
	// PLACEHOLDERS (mw_placeholders)
	// ============================================================

	/**
	 * Enables the Meralda placeholder system. Calling it without arguments
	 * creates the catalog; an existing `mwmod_mw_jsobj_objcoladv_base` can be
	 * passed to share one catalog between several editors.
	 *
	 * Once enabled, the JS input adds a `[[ ]]` button to the toolbar that opens
	 * the same modal used by the CKEditor integration and inserts the
	 * `[[code]]` token as plain text.
	 *
	 * @param mwmod_mw_jsobj_objcoladv_base|false $man
	 * @return mwmod_mw_jsobj_objcoladv_base
	 */
	function setPlaceholders($man=false){
		if(!$man){
			$man=new mwmod_mw_jsobj_objcoladv_base("placeholderman","mw_placeholders");
		}
		$this->_placeholderman=$man;
		$this->set_prop("placeholderman",$man);
		return $man;
	}

	/**
	 * Returns the placeholder catalog, creating it when needed.
	 * @return mwmod_mw_jsobj_objcoladv_base
	 */
	function getPlaceholdersMan(){
		if(!$this->_placeholderman){
			$this->setPlaceholders();
		}
		return $this->_placeholderman;
	}

	/**
	 * Adds a placeholder to the catalog.
	 *
	 * @param string $cod Token code, e.g. "customer.fullName".
	 * @param string|false $lbl Human label shown in the picker.
	 * @return mwmod_mw_jsobj_objcoladv_base The placeholder item.
	 */
	function addPlaceholder($cod,$lbl=false){
		$man=$this->getPlaceholdersMan();
		$ph=$man->add_new_child($cod);
		if($lbl){
			$ph->set_prop("lbl",$lbl);
		}
		return $ph;
	}

	/**
	 * Adds a placeholder that accepts parameters, and returns the params group
	 * to fill with `add_new_child()` / `add_data_gr()`.
	 *
	 *     $inputs = $ed->addPlaceholderWithParams("order.summary", "Order summary");
	 *     $inputs->add_new_child("headline")->set_prop("lbl", "Headline");
	 *
	 * @param string $cod
	 * @param string|false $lbl
	 * @return mwmod_mw_jsobj_inputs_input
	 */
	function addPlaceholderWithParams($cod,$lbl=false){
		$ph=$this->addPlaceholder($cod,$lbl);
		$inputs=new mwmod_mw_jsobj_inputs_input("inputs","mw_datainput_item_group");
		$ph->set_prop("inputs",$inputs);
		return $inputs;
	}

	/**
	 * Label of the toolbar button, in case the caller wants to translate it.
	 * @param string $lbl
	 * @return $this
	 */
	function setPlaceholdersButtonLabel($lbl){
		$this->set_prop("placeholders.lbl",$lbl);
		return $this;
	}

	// ============================================================
	// HELPERS
	// ============================================================

	/**
	 * Normalises an array|string into the value the JS side expects:
	 * arrays are kept as JS arrays, strings are kept as strings.
	 * @param array|string $val
	 * @return array|string
	 */
	private function list_to_js_value($val){
		if(is_array($val)){
			return $val;
		}
		return trim($val."");
	}

	/**
	 * Normalises an array|string into a PHP array of items.
	 * @param array|string $val
	 * @return array
	 */
	private function list_to_array($val){
		if(is_array($val)){
			return $val;
		}
		$val=trim($val."");
		if($val===""){
			return array();
		}
		return preg_split('/\s+/',$val);
	}

	/**
	 * Current plugin list as a PHP array.
	 * @return array
	 */
	private function get_plugins_list(){
		$p=$this->get_prop("editorcfg.plugins");
		return $this->list_to_array($p);
	}
}
?>
