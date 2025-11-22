<?php
/**
 * Base class for all UI subinterfaces in Meralda.
 *
 * @property-read mwmod_mw_ui_main_def $maininterface Main interface object.
 * @property-read mwmod_mw_ui_template_abs $template UI rendering template.
 * @property-read mwmod_mw_mnu_man $mnu_man Menu manager.
 * @property-read mwmod_mw_data_xml_root $xmlResponse XML response for commands.
 * @property-read mwmod_mw_jsobj_obj $ui_js_init_params JavaScript initialization parameters. 
 * @property-read mwmod_mw_manager_man $items_man Manager for the items (can be subclassed with @method in children).
 * @property-read mwmod_mw_data_session_man_item $uiSessionDataMan Session manager for this UI.
 * @property-read mwmod_mw_manager_item $current_item Currently selected item in this UI.
 * @property-read string $code Interface code.
 * @property-read string $def_title Default title for this subinterface.
 * @property-read array $cmdparams Command parameters for getcmd operations.
 * @property-read mwmod_mw_ui_sub_uiabs|null $parent_subinterface Direct parent subinterface.
 * @property-read mwmod_mw_ui_sub_uiabs|null $current_sub_interface Currently active child subinterface.
 * @property-read mwmod_mw_ui_sub_uiabs|null $current_parent_subinterface Currently active parent subinterface.
 * @property-read string $code_for_parent Code used to identify this subinterface in the parent.
 * @property mwmod_mw_jsobj_obj $js_output JavaScript output object.
 * @property mwmod_mw_data_xml_root $xml_output XML output object.
 */
abstract class mwmod_mw_ui_sub_uiabs extends mw_apsubbaseobj{
	/**
	 * Main UI interface instance.
	 * @var mwmod_mw_ui_main_def
	 */
	private $maininterface;
	private $___subinterfaces=array();
	private $___all_subinterfaces;
	private $___all_subinterfaces_loaded;

	public $mainPanelEnabled=false;
	public $jsonPrettyPrint=true;
	public $mainPanelClasses="";
	public $mainPanel;
	public $mainPanelTitle;

	
	//private $_subinterfaces=array();
	private $parent_subinterface;
	private $current_sub_interface;

	private $current_parent_subinterface;
	private $code_for_parent;
	private $template;
	public $url_def_file;
	
	
	//private $subinterface_code;
	private $current_item;
	private $def_title;
	private $urlparams=array();
	private $cmdparams=array();
	public $inheritCMDParams=true;
	private $code;
	public $sucods;
	public $xml_output;
	public $js_output;
	//var $order_on_main_mnu=99999999;
	//var $mnu_icon;
	public $mnuIconClass;
	
	//inicializar
	var $mnu;
	private $items_man;
	private $_items_man_cod;
	
	
	var $is_current=false;
	var $subinterface_def_code="def";
	var $bottom_alert_msg;
	
	var $js_ui_class_name="mw_ui";
	var $ui_elems_pref="uie_";
	var $selected_as_current=false;
	var $in_exec_chain=false;
	private $ui_js_init_params;
	
	var $ui_dom_elem_container;
	var $js_header_declaration;
	var $js_var_declaration;
	
	var $js_ui_obj;
	var $debug_mode=false;
	var $done=false;
	var $tooltip;
	private $___sub_ui_classes_names=array();
	private $uiSessionDataMan;
	public $omitUIGeneralContainer=false;
	public $xmlResponse;

	/**
	 * Enables the main panel for this UI with optional title and CSS classes.
	 * 
	 * @param string|bool $title Panel title. If false, will use the subinterface title.
	 * @param string $classes Additional CSS classes for the panel container.
	 * @return void 
	 */
	function mainPanelEnable($title=false,$classes=""){
		//if(!$title){
			//$title=$this->get_title();	
		//}
		$this->mainPanelEnabled=true;
		$this->mainPanelTitle=$title;
		$this->mainPanelClasses=$classes;

		
	}
	
	/**
	 * Retrieves the main panel title, falling back to the subinterface title if not set.
	 * 
	 * @return string The panel title.
	 */
	function getMainPanelTitle(){
		if($this->mainPanelTitle){
			return $this->mainPanelTitle;	
		}
		return $this->get_title();
	}
	
	/**
	 * Creates and returns a Bootstrap panel with configured title and CSS classes.
	 * 
	 * @return mwmod_mw_bootstrap_html_template_panel The created panel instance.
	 */
	function createMainPanel(){
		$panel=new mwmod_mw_bootstrap_html_template_panel();
		if($this->mainPanelClasses){
			$panel->main_elem->add_additional_class($this->mainPanelClasses);	
		}
		$panel->panel_heading->add_cont($this->getMainPanelTitle());
		$this->mainPanel=$panel;
		return $panel;
	}
	
	/**
	 * Retrieves the language messages manager code for this subinterface.
	 * 
	 * Tries parent subinterface first, then main application. Defaults to "def".
	 * 
	 * @return string The language messages manager code.
	 */
	function get_lngmsgsmancod(){
		if($this->parent_subinterface){
			if(method_exists($this->parent_subinterface,"get_lngmsgsmancod")){
				if($code=$this->parent_subinterface->get_lngmsgsmancod()){
					return $code;
				}
			}
		}
		if(method_exists($this->mainap,"get_lngmsgsmancod")){
			if($code=$this->mainap->get_lngmsgsmancod()){
				return $code;
			}
		}
		return "def";

	}
	
	/**
	 * Creates a session data manager for this subinterface.
	 * 
	 * @return mwmod_mw_data_session_man_item|false Session manager item or false if unavailable.
	 */
	function createUISessionDataMan(){
		if($m=$this->maininterface->uiSessionDataMan){
			return $m->getItem("sui",$this->get_full_cod("-"));	
		}
		
		return false;
		//return new mwmod_mw_data_session_man("mainui");	
	}
	
	/**
	 * Checks if the general UI container should be omitted.
	 * 
	 * @return bool True if container should be omitted.
	 */
	function omitUIGeneralContainer(){
		return $this->omitUIGeneralContainer;	
	}

	/**
	 * Internal accessor for UI session data manager.
	 *
	 * @internal
	 * @return mwmod_mw_data_session_man_item|false The session data manager or false.
	 */
	final function  __get_priv_uiSessionDataMan(){
		if(!isset($this->uiSessionDataMan)){
			if(!$this->uiSessionDataMan= $this->createUISessionDataMan()){
				$this->uiSessionDataMan=false;	
			}
			
		}
		return $this->uiSessionDataMan;
	}

	/**
	 * Creates a subinterface instance from a registered class name.
	 * 
	 * @param string $cod The subinterface code.
	 * @return mwmod_mw_ui_sub_uiabs|false The created subinterface instance or false on failure.
	 */
	function do_create_subinterface_by_cod_from_class_name($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;
		}
		if(!$class_name=$this->getSubUIClassName($cod)){
			return false;	
		}
		$aman=mw_get_autoload_manager();
		if(!$aman->class_exists($class_name)){
			return false;	
		}
		$su=new $class_name($cod,$this);
		return $su;
		
		
		
		
	}
	
	/**
	 * Registers a class name for a subinterface code.
	 * 
	 * @param string $cod The subinterface code.
	 * @param string $className The fully qualified class name.
	 * @return bool Always returns true.
	 */
	final function addSubUIClass($cod,$className){
		$this->___sub_ui_classes_names[$cod]=$className;
		return true;
	}
	
	/**
	 * Retrieves the registered class name for a subinterface code.
	 * 
	 * @param string $cod The subinterface code.
	 * @return string|null The class name or null if not registered.
	 */
	final function getSubUIClassName($cod){
		return $this->___sub_ui_classes_names[$cod]??null;
	}
	
	/**
	 * Gets the logout URL from the main interface.
	 * 
	 * @return string The logout URL.
	 */
	function get_logout_url(){
		return $this->maininterface->get_logout_url();	
	}
	
	/**
	 * Determines if this interface is allowed for GET command execution without a logged-in user.
	 * 
	 * Override this method to allow anonymous access.
	 * 
	 * @return bool Always returns false unless overridden.
	 */
	function is_allowed_for_get_cmd_no_user(){
		return false;	
	}
	/**
	 * Retrieves a nested subinterface using a dot-separated path.
	 *
	 * For example, "admin.users.edit" will recursively return the "edit" subinterface of "users" under "admin".
	 *
	 * @param string $dotcod Dot-separated subinterface code path (e.g., "module.submodule").
	 * @param string $sep Separator character, default is ".".
	 * 
	 * @return mwmod_mw_ui_sub_uiabs The target subinterface if found, or false otherwise.
	 */
	function get_sub_interface_by_dot_cod($dotcod,$sep="."){
		if(!$dotcod){
			return false;	
		}
		$list=explode($sep,$dotcod);
		if(sizeof($list)<=0){
			return false;
		}
		$cod = array_shift($list);
		if(!$su=$this->get_subinterface($cod)){
			return false;	
		}
		if(sizeof($list)<=0){
			return $su;
		}else{
			return $su->get_sub_interface_by_dot_cod(implode($sep,$list),$sep);	
		}
		
	}
	/**
	 * Retrieves user-specific UI data stored in their JSON preferences.
	 *
	 * Constructs the path using the full subinterface code and the main interface's user preference path.
	 *
	 * @param string $cod The key of the data set to retrieve. Defaults to "uipref".
	 * 
	 * @return mwmod_mw_data_json_item The data item if found, or false if the user or path is not available.
	 */
	function get_user_ui_data($cod="uipref"){
		if(!$user=$this->get_current_user()){
			return false;
		}
		
		if(!$path=$this->get_full_cod("/")){
			return false;	
		}
		$maincode=$this->maininterface->get_user_pref_rel_path();
		
		return $user->get_jsondata_item($cod,"uipref/{$maincode}/".$path);
	}
	/**
	 * Shortcut to retrieve the user's UI preferences.
	 *
	 * Equivalent to calling get_user_ui_data("uipref").
	 *
	 * @return mwmod_mw_data_json_item User preference item or false if unavailable.
	 */
	function get_user_ui_pref(){
		return $this->get_user_ui_data("uipref"); 
	}
	/**
	 * Returns the current logged-in admin user.
	 *
	 * Delegates to the main interface to get the current admin user object.
	 *
	 * @return mwmod_mw_users_user|false The admin user object or false if none is set.
	 */
	function get_current_user(){
		return $this->get_admin_current_user();
	}
	
	/**
	 * Generates a URL for a nested subinterface by dot-separated path.
	 * 
	 * @param string $dotcod Dot-separated subinterface code path (e.g., "module.submodule").
	 * @param array|false $args URL query parameters.
	 * @param string|false $file Optional filename for the URL.
	 * @param string $sep Separator character, default is ".".
	 * @return string|false The URL or false on failure.
	 */
	function get_url_sub_interface_by_dot_cod($dotcod,$args=false,$file=false,$sep="."){
		if(!$full_cod=$this->get_full_cod($sep)){
			return false;
		}
		if($dotcod){
			$full_cod.=$sep.$dotcod;	
		}
		
		return $this->maininterface->get_url_sub_interface_by_dot_cod($full_cod,$file,$sep,$args);
	}
	/**
	 * Creates the main UI container DOM element with a unique ID.
	 * 
	 * @return mwmod_mw_html_elem The created DOM element.
	 */
	function create_ui_dom_elem_container(){
		$container= new mwmod_mw_html_elem("div");
		$container->set_att("id",$this->get_ui_elem_id_and_set_js_init_param("container"));
		return $container;
	
	}
	
	/**
	 * Creates a Bootstrap modal element with a predefined ID and structure.
	 * 
	 * @return mwmod_mw_bootstrap_html_template_modal The created modal element.
	 */
	function create_ui_modal(){
		$modal= new mwmod_mw_bootstrap_html_template_modal($this->get_ui_elem_id_and_set_js_init_param("modal"),"...");
		if($modal_footer=$modal->get_key_cont("footer")){
			$modal_footer->only_visible_when_has_cont=false;	
		}
		return $modal;
	}
	
	/**
	 * Checks if debug output is enabled for this subinterface.
	 * 
	 * Returns true if debug_mode is set or user has debug permission.
	 * 
	 * @return bool True if debug output is enabled.
	 */
	function debugOutputEnabled(){
		if($this->debug_mode){
			return true;	
		}
		if($this->allow("debug")){
			return true;	
		}
		return false;
	}
	
	/**
	 * Checks if debug mode is active with proper permissions.
	 * 
	 * Returns true only if debug_mode is enabled AND user has debug permission.
	 * 
	 * @return bool True if debug mode is active.
	 */
	function is_debug_mode(){
		if(!$this->debug_mode){
			return false;	
		}
		if($this->allow("debug")){
			return true;	
		}
		return false;
	}
	/**
	 * Creates a container with an iframe and form for file uploads or background operations.
	 * 
	 * @return mwmod_mw_html_elem The container element with iframe and form included.
	 */
	function create_ui_dom_elem_iframe_and_frm_container(){
		$container= new mwmod_mw_html_elem("div");
		$id=$this->get_ui_elem_id_and_set_js_init_param("iframeandfrm");
		$idiframe=$this->get_ui_elem_id_and_set_js_init_param("iframe");
		$idfrm=$this->get_ui_elem_id_and_set_js_init_param("frmoniframe");
		$container->set_att("id",$id);
		if(!$this->is_debug_mode()){
			$container->set_style("display","none");	
		}
		$iframe= new mwmod_mw_html_elem("iframe");
		$iframe->set_att("id",$idiframe);
		$iframe->set_att("name",$idiframe);
		$iframe->set_att("width","800");
		$iframe->set_att("height","800");
		$container->add_cont($iframe);
		$container->set_key_cont("iframe",$iframe);
		
		$frm= new mwmod_mw_html_elem("form");
		$form= new  mwmod_mw_html_elem("form");
		$form->set_att("id",$idfrm);
		$form->set_att("name",$idfrm);
		$form->set_att("method","post");
		$form->set_att("target",$idiframe);
		$form->set_att("action",$this->get_exec_cmd_sxml_url());
		
		$form->set_att("enctype","multipart/form-data");

		$container->set_key_cont("form",$form);
		$container->add_cont($form);
		
		
		
		return $container;
	
	}
	
	/**
	 * Sets the ID of a UI element and returns the modified element.
	 * 
	 * @param string $cod Identifier code used to generate the DOM ID.
	 * @param mwmod_mw_html_elem|string|null $elem Optional element or tag name to create.
	 * 
	 * @return mwmod_mw_html_elem The DOM element with ID set.
	 */
	function set_ui_dom_elem_id($cod,$elem=false){
		if(!$elem){
			
			$elem= new mwmod_mw_html_elem("div");
		}
		if(is_string($elem)){
			$elem= new mwmod_mw_html_elem($elem);	
		}
		
		$elem->set_att("id",$this->get_ui_elem_id_and_set_js_init_param($cod));
		return $elem;
	
	}
	/**
	 * Initializes and stores a UI container element if not already set.
	 * 
	 * @param mwmod_mw_html_elem|null $container Optional container to use.
	 * 
	 * @return mwmod_mw_html_elem The DOM container element.
	 */
	function get_ui_dom_elem_container_empty($container=false){
		if(!$container){
			$container=	$this->create_ui_dom_elem_container();	
		}
		$this->ui_dom_elem_container=$container;
		return $this->ui_dom_elem_container;
		
	}
	/**
	 * Retrieves or creates the main UI DOM container element.
	 * 
	 * @return mwmod_mw_html_elem The DOM container element.
	 */
	function get_ui_dom_elem_container(){
		if(!$this->ui_dom_elem_container){
			$this->ui_dom_elem_container=$this->create_ui_dom_elem_container();	
		}
		return $this->ui_dom_elem_container;
	}
	
	/**
	 * Returns an alert element indicating the operation is not allowed.
	 * 
	 * @return mwmod_mw_bootstrap_html_specialelem_alert The alert HTML element.
	 */
	function get_bt_operation_not_allowed_html_elem(){
		$msg=$this->lng_get_msg_txt("operation_not_allowed","Operación no permitida");
		$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"danger");
		return $alert;	
	}
	
	/**
	 * Outputs the "operation not allowed" alert HTML directly.
	 * 
	 * @return void
	 */
	function output_bt_operation_not_allowed_html_elem(){
		$e=$this->get_bt_operation_not_allowed_html_elem();
		echo $e->get_as_html();	
	}
	
	
	//js
	/**
	 * Creates a JavaScript function for populating the footer of a modal with Cancel and Accept buttons.
	 *
	 * The function will be assigned to `create_footer_input` on the given modal populator if provided.
	 *
	 * @param mwmod_mw_jsobj_obj|false $js_modalpopulator Optional JS modal populator to attach the footer creation function.
	 * @return mwmod_mw_jsobj_functionext The generated function object.
	 */
	function create_modal_js_inputs_footer_cancel_ok_fnc($js_modalpopulator=false){
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_fnc_arg("modalpopulator");
		$this->create_modal_js_inputs_footer_cancel_ok($fnc);
		if($js_modalpopulator){
			$js_modalpopulator->set_prop("create_footer_input",$fnc);	
		}
		return $fnc;
		//$this->create_new_doc_js_inputs_footer($fnc);
	
	}
	/**
	 * Adds Cancel and Accept buttons to a JS function for a modal footer.
	 *
	 * This method modifies the given JS function to include a button group with localized labels and actions.
	 *
	 * @param mwmod_mw_jsobj_functionext $js JavaScript function container to append button definitions.
	 */
	
	function create_modal_js_inputs_footer_cancel_ok($js){
		
		
		$jsinputgr=new mwmod_mw_jsobj_newobject("mw_datainput_item_btnsgroup");
		$js->add_cont("var grfooter=".$jsinputgr->get_as_js_val().";\n");
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_btn");
		$jsinput->set_prop("lbl",$this->lng_common_get_msg_txt("cancel","Cancelar"));
		$jsinput->set_prop("display_mode","danger");
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_cont($js->get_arg_by_index().".hide();\n");
		$jsinput->set_prop("onclick",$fnc);
		
		$js->add_cont("grfooter.addItem(".$jsinput->get_as_js_val().",'cancel');\n");
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_btn");
		$jsinput->set_prop("lbl",$this->lng_common_get_msg_txt("accept","Aceptar"));
		$jsinput->set_prop("display_mode","success");
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_cont($js->get_arg_by_index().".validate_and_submit_body_frm();\n");
		$jsinput->set_prop("onclick",$fnc);
		
		$js->add_cont("grfooter.addItem(".$jsinput->get_as_js_val().",'ok');\n");

		
		
		$js->add_cont($js->get_arg_by_index().".set_footer_input(grfooter);\n");
		
	}
	/**
	 * Creates a JavaScript header declaration item for the UI manager.
	 *
	 * @return mwmod_mw_html_manager_item_jscus JS custom item to be included in the HTML manager.
	 */
	
	function create_js_man_ui_header_declaration_item(){
		$cod=$this->get_js_ui_man_name();
		$js=$this->get_js_header_declaration();
		$item= new mwmod_mw_html_manager_item_jscus($cod,$js);
		return $item;	
	}
	/**
	 * Creates a container for JavaScript header declarations.
	 *
	 * This includes variable declarations for the current UI object.
	 *
	 * @return mwmod_mw_jsobj_codecontainer
	 */
	function create_js_header_declaration(){
		$js= new mwmod_mw_jsobj_codecontainer();
		$vardec=$this->get_js_var_declaration();
		$js->add_cont($vardec);
		
		return $js;
		
		
		
		
			
	}
	/**
	 * Returns the cached or newly created JavaScript header declaration object.
	 *
	 * @return mwmod_mw_jsobj_codecontainer
	 */
	function get_js_header_declaration(){
		if(!isset($this->js_header_declaration)){
			$this->js_header_declaration=$this->create_js_header_declaration();
		}
		return $this->js_header_declaration;
	}
	/**
	 * Returns the JavaScript variable name for this UI manager.
	 *
	 * @return string The variable name, e.g. "uiman_section_users".
	 */
	function get_js_ui_man_name(){
		return "uiman_".$this->get_full_cod("_");	
	}
	/**
	 * Creates a new JavaScript UI object using the class name and initialization info.
	 *
	 * @return mwmod_mw_jsobj_newobject
	 */
	function new_ui_js(){
		$js= new mwmod_mw_jsobj_newobject($this->js_ui_class_name,$this->get_ui_js_info());
		return $js;	
	}
	/**
	 * Returns the JavaScript variable declaration for the UI object.
	 *
	 * @return mwmod_mw_jsobj_vardeclaration
	 */
	function get_js_var_declaration(){
		if(!isset($this->js_var_declaration)){
			$varname=$this->get_js_ui_man_name();
			$js_obj=$this->get_js_ui_obj();
			$this->js_var_declaration=new mwmod_mw_jsobj_vardeclaration($varname,$js_obj);
		}
		return $this->js_var_declaration;
	}
	/**
	 * Returns the current JS UI object or creates a new one if not already set.
	 *
	 * @return mwmod_mw_jsobj_newobject
	 */
	function get_js_ui_obj(){
		if(!isset($this->js_ui_obj)){
			$this->js_ui_obj=$this->new_ui_js();
		}
		return $this->js_ui_obj;
	}
	
	/**
	 * Generates a DOM element ID based on the UI element code and stores it in the JS initialization parameters.
	 *
	 * The ID is added under `uielemsids.{cod}` in the JavaScript initialization object.
	 *
	 * @param string $cod The code identifying the UI element.
	 * @return string The generated DOM element ID.
	 */
	
	function get_ui_elem_id_and_set_js_init_param($cod){
		$r=$this->get_ui_elem_id($cod);
		$js=$this->__get_priv_ui_js_init_params();
		$js->set_prop("uielemsids.".$cod,$r);
		return $r;
	}
	/**
	 * Generates a DOM element ID for a given UI component code.
	 *
	 * The format is based on the UI element prefix and the full subinterface code.
	 *
	 * @param string $cod The code identifying the UI element.
	 * @return string The DOM element ID.
	 */
	function get_ui_elem_id($cod){
		
		return $this->ui_elems_pref.$this->get_full_cod("_")."-".$cod;	
		//return $this->ui_elems_pref.$this->code."_".$cod;	
	}
	
	/**
	 * Internal accessor for JavaScript initialization parameters.
	 *
	 * @internal
	 * @return mwmod_mw_jsobj_obj The JavaScript initialization parameters object.
	 */
	final function  __get_priv_ui_js_init_params(){
		if(!isset($this->ui_js_init_params)){
			$this->ui_js_init_params= new mwmod_mw_jsobj_obj();	
		}
		return $this->ui_js_init_params;
	}
	/**
	 * Returns an array of UI metadata used for JavaScript initialization.
	 *
	 * The data includes codes, titles, URLs for XML and downloads, 
	 * main interface JS info, debug mode status, and UI element prefix.
	 *
	 * @return array{
	 *     cod: string,
	 *     full_cod: string,
	 *     title: string,
	 *     url: string,
	 *     xmlurl: string,
	 *     dlurl: string,
	 *     mainui: array,
	 *     debug_mode: bool,
	 *     uielemspref: string
	 * }
	 */
	function get_ui_js_info(){
		$r=array();
		$r["cod"]=$this->get_code_for_parent();
		$r["full_cod"]=$this->get_full_cod(".");
		$r["title"]=$this->get_title();
		$r["url"]=$this->get_url();
		$r["xmlurl"]=$this->get_exec_cmd_sxml_url(false);
		$r["dlurl"]=$this->get_exec_cmd_dl_url(false);
		$r["mainui"]=$this->maininterface->get_ui_js_info_for_child($this);
		$r["debug_mode"]=$this->is_debug_mode();
		$r["uielemspref"]=$this->ui_elems_pref.$this->get_full_cod("_")."-";
		
		return $r;
			
	}
	
	
	/**
	 * Executes a debug command returning XML with diagnostic information.
	 *
	 * Useful for inspecting UI state, parameters, and JS initialization data.
	 *
	 * @param array $params Command parameters passed from the request.
	 * @param string|false $filename Optional filename for download.
	 * @return void Outputs XML directly.
	 */
	function execfrommain_getcmd_sxml_debug($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(true,"Debug");
		$xml->set_prop("class",get_class($this));
		$xml->set_prop("params",$params);
		$xml->set_prop("filename",$filename);
		$info=$this->get_ui_js_info();
		$xml->set_prop("info",$info);
		
		$xmljs=new mwmod_mw_data_xml_js("jsinfo",$info);
		
		$xml->add_sub_item($xmljs);
		
		//$xml->set_prop("post",$_POST);
		//$xml->set_prop("get",$_GET);
		
		$xml->root_do_all_output();
			
	}
	
	/**
	 * Creates a new XML response object for `getcmd` commands.
	 *
	 * @param bool $ok True if the command succeeded, false otherwise.
	 * @param string $msg Optional status message.
	 * @return mwmod_mw_data_xml_item The XML response item.
	 */
	function new_getcmd_sxml_answer($ok=true,$msg=""){
		$xmlroot=new mwmod_mw_data_xml_root();
		$xml=$xmlroot->get_sub_root();
		$xml->set_prop("ok",$ok);
		$xml->set_prop("msg",$msg);
		$this->xmlResponse=$xml;
		return $xml;
	
	}
	//rvh 20240214
	/**
	 * Hook method executed before processing a `getcmd` command.
	 *
	 * This method is intended to be overridden in subclasses to perform
	 * setup or dependency loading before handling a getcmd request.
	 *
	 * @param array $params Parameters passed with the getcmd request.
	 *
	 * @return void
	 */
	function before_exec_get_cmd($params=array()){
		//extender. cargar acá objetos dependientes
	}
	
	/**
	 * Executes an XML command requested via GET/POST parameters.
	 *
	 * Routes the request to the appropriate `execfrommain_getcmd_sxml_{cmdcod}` method.
	 *
	 * @param string $cmdcod The command code to execute.
	 * @param array $params Parameters for the command.
	 * @param string|false $filename Optional filename for output.
	 * @return mixed The result of the command method or false on failure.
	 */
	function execfrommain_getcmd_sxml($cmdcod,$params=array(),$filename=false){
		$this->before_exec_get_cmd($params);
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			$xml=$this->new_getcmd_sxml_answer(false,"Invalid command");
			$xml->root_do_all_output();
			return false;	
		}
		$method="execfrommain_getcmd_sxml_$cmdcod";
		if(!method_exists($this,$method)){
			$xml=$this->new_getcmd_sxml_answer(false,"Method $method does not exist on ".get_class($this));
			$xml->root_do_all_output();
			return false;
				
		}
		return $this->$method($params,$filename);
	}
	
	/**
	 * Outputs an error message for a failed download command.
	 *
	 * In debug mode, displays detailed error information.
	 *
	 * @param array $params Command parameters.
	 * @param string|false $filename Requested filename.
	 * @param string|false $errmsg Error message to display.
	 * @return false Always returns false.
	 */
	function execfrommain_getcmd_dl_error($params=array(),$filename=false,$errmsg=false){
		if($this->is_debug_mode()){
			echo "Error";
			if($errmsg){
				echo " $errmsg";	
			}
			$data=array(
				"params"=>$params,
				"filename"=>$filename,
				"uifullcod"=>$this->get_full_cod(),
				"uiclass"=>get_class($this),
				
			);
			mw_array2list_echo($data);
			
		}
		return false;
	}
	
	/**
	 * Test method for download commands, outputs diagnostic information.
	 *
	 * @param array $params Command parameters.
	 * @param string|false $filename Requested filename.
	 * @return false Always returns false.
	 */
	function execfrommain_getcmd_dl_test($params=array(),$filename=false){
		$data=array(
			"params"=>$params,
			"filename"=>$filename,
			"uifullcod"=>$this->get_full_cod(),
			"uiclass"=>get_class($this),
			
		);
		mw_array2list_echo($data);
		return false;
	}
	
	/**
	 * Executes a download command requested via GET/POST.
	 *
	 * Routes to the appropriate `execfrommain_getcmd_dl_{cmdcod}` method.
	 *
	 * @param string $cmdcod The download command code.
	 * @param array $params Command parameters.
	 * @param string|false $filename Filename for the download.
	 * @return mixed The result of the download method or false on error.
	 */
	function execfrommain_getcmd_dl($cmdcod,$params=array(),$filename=false){
		$this->before_exec_get_cmd($params);
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			$errmsg="invalid command";
			return $this->execfrommain_getcmd_dl_error($params,$filename,$errmsg);	
		}
		$method="execfrommain_getcmd_dl_$cmdcod";
		if(!method_exists($this,$method)){
			$errmsg=get_class($this)." has no method $method";
			return $this->execfrommain_getcmd_dl_error($params,$filename,$errmsg);	
				
		}
		return $this->$method($params,$filename);
	}
	//20250307
	/**
	 * Hook executed before setting the current subinterface during a getcmd request.
	 *
	 * Intended to be overridden for loading dependent objects or configurations
	 * before the subinterface is resolved.
	 *
	 * @param string|false $cods     Subinterface code or false if not provided.
	 * @param array        $params   Additional parameters passed to the request.
	 * @param string|false $filename Optional filename for the request, if applicable.
	 *
	 * @return void
	 */
	function before_set_current_subinterface_for_getcmd($cods=false,$params=array(),$filename=false){
		//extender puede usarse para cargar objetos dependientes especialmente cuando las subinterfases se crean en dependencia a parametros de entrada	
	}
	public $requestedCMDParams;
	
	/**
	 * Retrieves a parameter from the request, checking both local and global REQUEST arrays.
	 *
	 * @param string $key The parameter key to retrieve.
	 * @return mixed|null The parameter value if found, null otherwise.
	 */
	function getRequestedParam($key){
		if(is_array($this->requestedCMDParams)){
			if(isset($this->requestedCMDParams[$key])){
				return $this->requestedCMDParams[$key];
				
			}
		}
		if(is_array($_REQUEST)){
			if(isset($_REQUEST[$key])){
				return $_REQUEST[$key];	
			}
		}
		
		
	}
	
	/**
	 * Sets the current subinterface during a getcmd request by traversing hyphen-separated codes.
	 *
	 * Recursively navigates through nested subinterfaces and validates permissions.
	 *
	 * @param string|false $cods     Hyphen-separated subinterface codes (e.g., "users-edit").
	 * @param array        $params   Command parameters to pass along.
	 * @param string|false $filename Optional filename for the request.
	 *
	 * @return mwmod_mw_ui_sub_uiabs|false The target subinterface or false if not found/allowed.
	 */
	function set_current_subinterface_for_getcmd($cods=false,$params=array(),$filename=false){
		$this->setCMDParamsFromRequest($params);
		$this->before_set_current_subinterface_for_getcmd($cods,$params,$filename);
		
		if(!$cods){
			return false;	
		}
		if(!is_string($cods)){
			return false;	
		}
		$cods=explode("-",$cods,2);
		if(!$sub_ui=$this->get_subinterface($cods[0]??null)){
			return false;	
		}
		if(!$sub_ui->is_allowed_for_get_cmd($cods[1]??null,$params,$filename)){
			return false;	
		}
		if(!$this->set_current_subinterface($sub_ui,false)){
			return false;	
		}
		if(!isset($cods[1]) or !$cods[1]){
			$sub_ui->requestedCMDParams=$params;
			return $sub_ui;	
		}
		return $sub_ui->set_current_subinterface_for_getcmd($cods[1],$params,$filename);
		

	}
	
	/**
	 * Stores command parameters from the request into the local requestedCMDParams array.
	 *
	 * @param array $params Parameters to store.
	 * @return false|void Returns false if params is not an array.
	 */
	function setCMDParamsFromRequest($params){
		if(!is_array($params)){
			return false;	
		}
		if(!is_array($this->requestedCMDParams)){
			$this->requestedCMDParams=array();	
		}
		
		foreach($params as $key=>$val){
			$this->requestedCMDParams[$key]=$val;
			
		}
		
		return true;
		
	}
	
	/**
	 * Checks if this subinterface is allowed for getcmd execution.
	 *
	 * Override this method to implement custom permission logic.
	 *
	 * @param string|false $sub_ui_cods Optional sub-UI codes for nested checks.
	 * @param array        $params      Command parameters.
	 * @param string|false $filename    Optional filename.
	 *
	 * @return bool True if allowed, false otherwise.
	 */
	function is_allowed_for_get_cmd($sub_ui_cods=false,$params=array(),$filename=false){
		return $this->is_allowed();	
	}
	
	/**
	 * Checks if a specific XML command is enabled for this subinterface.
	 *
	 * Verifies that the corresponding method exists.
	 *
	 * @param string $cmdcod Command code to check.
	 * @param array  $params Command parameters (currently unused).
	 *
	 * @return bool True if the command method exists, false otherwise.
	 */
	function getCmdXmlEnabled($cmdcod,$params=array()){
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			return false;	
		}
		$method="execfrommain_getcmd_sxml_$cmdcod";
		if(method_exists($this,$method)){
			return true;
				
		}
		return false;
	}
	
	/**
	 * Returns the XML command URL only if the command is enabled.
	 *
	 * @param string $xmlcmd Command code to check and generate URL for.
	 * @param array  $params Additional parameters for the URL.
	 *
	 * @return string|false The URL if command is enabled, false otherwise.
	 */
	function get_exec_cmd_sxml_url_ifEnabled($xmlcmd="debug",$params=array()){
		if(!$this->getCmdXmlEnabled($xmlcmd)){
			return false;	
		}
		return $this->get_exec_cmd_sxml_url($xmlcmd,$params);
	}
	//rvh20240214
	/**
	 * Generates a URL for executing an XML command on this subinterface.
	 *
	 * Delegates to the main interface with enriched parameters.
	 *
	 * @param string $xmlcmd Command code to execute (default: "debug").
	 * @param array  $params Additional query parameters.
	 *
	 * @return string|false The generated URL or false if no main interface.
	 */
	function get_exec_cmd_sxml_url($xmlcmd="debug",$params=array()){
		if($this->maininterface){
			$params=$this->get_cmd_params($params);//new
			return $this->maininterface->get_exec_cmd_sxml_url($xmlcmd,$this,$params);	
		}
		
		
	}
	
	/**
	 * Generates a URL for executing a download command on this subinterface.
	 *
	 * @param string       $dlcmd        Download command code (default: "test").
	 * @param array        $params       Additional query parameters.
	 * @param string|false $realfilename Optional filename for the download.
	 *
	 * @return string|false The generated URL or false if no main interface.
	 */
	function get_exec_cmd_dl_url($dlcmd="test",$params=array(),$realfilename=false){
		if($this->maininterface){
			$params=$this->get_cmd_params($params);//new
			return $this->maininterface->get_exec_cmd_dl_url($dlcmd,$this,$params,$realfilename);	
		}
		
		
	}
	
	/**
	 * Merges default command parameters with provided arguments.
	 *
	 * @param array $args Optional parameters to merge with defaults.
	 *
	 * @return array The merged parameter array.
	 */
	function get_cmd_params($args=array()){
		$def=$this->get_cmd_param();
		if(!is_array($args)){
			return $def;	
		}
		return  mw_array_bydefault($args,$def);
	}

	/**
	 * Resets command parameters to an empty array.
	 *
	 * @return void
	 */
	final function reset_cmd_params(){
		$this->cmdparams=array();
		
	}
	
	/**
	 * Retrieves a command parameter by key or returns all parameters.
	 *
	 * @param string|false $key Parameter key or false to return all parameters.
	 *
	 * @return mixed The parameter value, array of all parameters, or null if not found.
	 */
	final function get_cmd_param($key=false){
		if(!$key){
			return 	$this->cmdparams;
		}
		return mw_array_get_sub_key($this->cmdparams,$key);	
	}
	
	/**
	 * Checks if a specific command parameter exists.
	 *
	 * @param string $key The parameter key to check.
	 *
	 * @return bool True if the parameter exists, false otherwise.
	 */
	final function get_cmd_param_exists($key){
		if($this->cmdparams){
			if(isset($this->cmdparams[$key])){
				return true;
			}
		}
		return false;
		
	}
	
	/**
	 * Sets a command parameter value, supporting nested keys via dot notation.
	 *
	 * @param string $key Parameter key (supports "parent.child" notation).
	 * @param mixed  $val Value to set.
	 *
	 * @return void
	 */
	final function set_cmd_param($key,$val){

		mw_array_set_sub_key($key,$val,$this->cmdparams);
		
	}

	/**
	 * Internal accessor for command parameters.
	 *
	 * @internal
	 * @return array The command parameters array.
	 */
	final function __get_priv_cmdparams(){
		return $this->cmdparams;	
	}
	////////

	/**
	 * Sets a message to display at the bottom of the UI.
	 *
	 * @param string|mwmod_mw_html_elem|false $msg Message string or HTML element object.
	 *
	 * @return void
	 */
	function set_bottom_alert_msg($msg=false){
		$this->bottom_alert_msg=$msg;	
	}
	
	/**
	 * Outputs the bottom alert message if set.
	 *
	 * Handles both string messages and HTML element objects.
	 *
	 * @return false|void Returns false if no message is set.
	 */
	function output_bottom_alert_msg(){
		if(!$this->bottom_alert_msg){
			return false;
		}
		if(is_object($this->bottom_alert_msg)){
			if(method_exists($this->bottom_alert_msg,"get_as_html")){
				echo $this->bottom_alert_msg->get_as_html();
			}
			return;
		}else{
			echo $this->bottom_alert_msg;	
		}
	}
	

	//subinterface mnu
	//un menu que crea un padre con is_responsable_for_sub_interface_mnu true para sí y sus hijos
	/**
	 * Adds this subinterface to a submenu structure.
	 *
	 * Override this to customize menu item appearance.
	 *
	 * @param mwmod_mw_mnu_mnu $mnu The menu object to add to.
	 *
	 * @return void
	 */
	function add_2_sub_interface_mnu($mnu){
		//ver mwmod_mw_ui_debug_htmltemplate
		$this->add_2_mnu($mnu);
	}
	
	/**
	 * Creates a submenu for this subinterface.
	 *
	 * Should be implemented by parent subinterfaces that are responsible for menu creation.
	 *
	 * @param mwmod_mw_ui_sub_uiabs|false $su The subinterface requesting the menu.
	 *
	 * @return mwmod_mw_mnu_mnu|false The created menu or false.
	 */
	function create_sub_interface_mnu_for_sub_interface($su=false){
		//ver mwmod_mw_ui_debug_uidebug
	}
	
	/**
	 * Retrieves the submenu from the parent responsible for menu creation.
	 *
	 * @return mwmod_mw_mnu_mnu|false The menu object or false if not found.
	 */
	function get_sub_interface_mnu_from_parent_responsable(){
		if($p=$this->get_parent_responsable_for_sub_interface_mnu()){
			return $p->create_sub_interface_mnu_for_sub_interface($this);
		}
	}
	
	/**
	 * Finds the parent subinterface responsible for creating submenus.
	 *
	 * Recursively walks up the parent chain until finding a responsible parent.
	 *
	 * @return mwmod_mw_ui_sub_uiabs|false The responsible parent or false if none found.
	 */
	function get_parent_responsable_for_sub_interface_mnu(){
		if($this->is_responsable_for_sub_interface_mnu()){
			return $this;	
		}
		if($this->parent_subinterface){
			return $this->parent_subinterface->get_parent_responsable_for_sub_interface_mnu();
		}
	}
	
	/**
	 * Indicates whether this subinterface is responsible for creating submenus.
	 *
	 * Override and return true in subinterfaces that manage menu creation.
	 *
	 * @return bool Always returns false unless overridden.
	 */
	function is_responsable_for_sub_interface_mnu(){
		return false;	
	}
	//bootstrap
	/**
	 * Executes the subinterface body using a Bootstrap-based template.
	 *
	 * @param mwmod_mw_bootstrap_ui_template_main $main_ui_template The Bootstrap template.
	 *
	 * @return mixed The result of template execution.
	 */
	function exec_page_body_sub_interface_on_main_template_bootstrap($main_ui_template){
		return $main_ui_template->exec_page_body_sub_interface_bootstrap($this);
	}
	
	/**
	 * Checks if this subinterface can be rendered on a Bootstrap template.
	 *
	 * @return bool Always returns true unless overridden.
	 */
	function can_page_body_sub_interface_on_main_template_bootstrap(){
		return true;
	}
	
	/**
	 * Initializes the subinterface with code and main interface reference.
	 *
	 * @param string                     $cod            The subinterface code.
	 * @param mwmod_mw_ui_main_uimainabs $maininterface Main interface parent.
	 *
	 * @return void
	 */
	function init($cod,$maininterface){
		$this->set_main_interface($maininterface);	
		$this->set_code($cod);
	}
	
	/**
	 * Checks if this subinterface is currently active.
	 *
	 * @return bool True if currently active.
	 */
	function is_current(){
		return $this->is_current;	
	}
	
	/**
	 * Checks if this subinterface is in the execution chain.
	 *
	 * @return bool True if in execution chain or is current.
	 */
	function is_in_exec_chain(){
		if($this->in_exec_chain){
			return true;	
		}
		return $this->is_current();	
	}
	
	/**
	 * Initializes as either main or sub, depending on parent type.
	 *
	 * @param string                                             $cod    Subinterface code.
	 * @param mwmod_mw_ui_main_uimainabs|mwmod_mw_ui_sub_uiabs $parent Parent interface.
	 *
	 * @return void
	 */
	function init_as_main_or_sub($cod,$parent){
		if(is_a($parent,"mwmod_mw_ui_main_uimainabs")){
			$this->init($cod,$parent);
		}else{
			$this->init_as_subinterface($cod,$parent);	
		}
	}
	
	/**
	 * Initializes this as a child subinterface of another subinterface.
	 *
	 * @param string                    $cod    Subinterface code.
	 * @param mwmod_mw_ui_sub_uiabs    $parent Parent subinterface.
	 *
	 * @return void
	 */
	function init_as_subinterface($cod,$parent){
		$maininterface=$parent->maininterface;
		$this->init($cod,$maininterface);
		$this->set_lngmsgsmancod_by_obj($parent);
		$this->set_parent_sub_interface($parent);
		$this->added_as_child($cod,$parent);
	}
	//items man
	/**
	 * Loads the items manager for this subinterface.
	 *
	 * Override `get_items_man_cod()` to specify which manager to load 
	 * or Override this method for custom loading logic.
	 * 
	 *
	 * @return mwmod_mw_manager_man|false The items manager or false if not available.
	 */
	function load_items_man(){
		if(!$cod=$this->get_items_man_cod()){
			return false;	
		}
		return $this->mainap->get_submanager($cod);
	}
	
	/**
	 * Retrieves the items manager for this subinterface, loading it if necessary.
	 *
	 * @return mwmod_mw_manager_man|false The items manager or false if not available.
	 */
	final function get_items_man(){
		if(isset($this->items_man)){
			return $this->items_man;
		}
		$this->items_man=false;
		if($man=$this->load_items_man()){
			$this->items_man=$man;	
		}
		return $this->items_man;
	}
	
	/**
	 * Sets the items manager for this subinterface.
	 *
	 * @param mwmod_mw_manager_man $man The items manager to set.
	 * @return void
	 */
	final function set_items_man($man){
		$this->items_man=$man;
		$this->after_set_items_man($man);
	}
	
	/**
	 * Hook called after setting the items manager.
	 *
	 * Override to perform additional setup based on the manager.
	 *
	 * @param mwmod_mw_manager_man $man The items manager that was set.
	 * @return void
	 */
	function after_set_items_man($man){
		$this->set_lngmsgsmancod($man->lngmsgsmancod);	
	}
	
	/**
	 * Sets the items manager code.
	 *
	 * @param string|false $cod The manager code.
	 * @return string|false The code that was set.
	 */
	final function set_items_man_cod($cod=false){
		return $this->_items_man_cod=$cod;
	}
	
	/**
	 * Gets the items manager code.
	 *
	 * @return string|false The manager code or false if not set.
	 */
	final function get_items_man_cod(){
		return $this->_items_man_cod;
	}

	/**
	 * Internal accessor for items manager.
	 *
	 * @internal
	 * @return mwmod_mw_manager_man The items manager instance.
	 */
	final function __get_priv_items_man(){
		return $this->get_items_man(); 	
	}
	
	//url
	
	/**
	 * Generates a URL for a child subinterface.
	 *
	 * @param string|false $code Subinterface code to navigate to.
	 * @param array|false  $args Additional URL parameters.
	 * @param string|false $file Optional filename.
	 *
	 * @return string The generated URL.
	 */
	function get_url_subinterface($code=false,$args=false,$file=false){
		if($code){
			if(!is_array($args)){
				$args=array();
			}
			$args[$this->get_subinterface_request_var()]=$code;
		}
		return $this->get_url($args,$file);
	}
	
	/**
	 * Generates a URL for this subinterface with optional parameters.
	 *
	 * @param array|false  $args Additional URL query parameters.
	 * @param string|false $file Optional filename.
	 *
	 * @return string The generated URL with query string.
	 */
	function get_url($args=false,$file=false){
		$url=$this->get_url_base($file);
		if($args1=$this->get_url_params($args)){
			if($q=mw_array2urlquery($args1)){
				$url.="?".$q;	
			}
		}
		return $url;
	}
	
	/**
	 * Merges provided URL parameters with defaults.
	 *
	 * @param array|false $args Optional parameters to merge.
	 *
	 * @return array Merged URL parameters.
	 */
	function get_url_params($args=false){
		$def=$this->get_url_param();
		if(!is_array($args)){
			return $def;	
		}
		return  mw_array_bydefault($args,$def);
	}
	
	/**
	 * Initializes URL parameters for a child subinterface.
	 *
	 * Passes current URL parameters down to the child along with its specific code.
	 *
	 * @param string                 $child_cod The child's code.
	 * @param mwmod_mw_ui_sub_uiabs $child     The child subinterface instance.
	 *
	 * @return false|void Returns false if parameters couldn't be retrieved.
	 */
	function init_child_url_params($child_cod,$child){
		$args=array();
		$args[$this->get_subinterface_request_var()]=$child_cod;
		
		if(!$params=$this->get_url_params($args)){
			return false;
		}
		foreach($params as $cod=>$v){
			$child->set_url_param($cod,$v);	
		}
	}

	/**
	 * Initializes URL parameters for this subinterface.
	 *
	 * If has a current parent, inherits its parameters. Otherwise, sets base parameter from main interface.
	 *
	 * @return void
	 */
	function init_url_params(){
		if($this->current_parent_subinterface){
			$this->current_parent_subinterface->init_child_url_params($this->code_for_parent,$this);
		}else{
			$this->set_url_param($this->maininterface->ui_var_name,$this->code);	
		}

		//$this->set_url_param("interface",$this->code);
	}
	
	/**
	 * Initializes command parameters, optionally inheriting from parent.
	 *
	 * If `inheritCMDParams` is true, parameters from parent subinterface are inherited.
	 *
	 * @return void
	 */
	function init_cmd_params(){
		if($this->current_parent_subinterface){
			if($this->inheritCMDParams){
				if($params=$this->current_parent_subinterface->get_cmd_param()){
					foreach($params as $cod=>$v){
						if(!$this->get_cmd_param_exists($cod)){
							$this->set_cmd_param($cod,$v);	
						}
						
					}
				}
			}
			
			
		}

		//$this->set_url_param("interface",$this->code);
	}
	
	/**
	 * Resets and reinitializes URL parameters.
	 *
	 * @return void
	 */
	final function reset_url_params(){
		$this->urlparams=array();
		$this->init_url_params();	
	}
	/**
	 * @param string $key 
	 * @param mixed $val 
	 * @return void 
	 */
	function setRequestParam($key,$val){
		$this->set_url_param($key,$val);
		$this->set_cmd_param($key,$val);

	}
	
	/**
	 * Sets a URL parameter value, supporting nested keys.
	 *
	 * @param string $key Parameter key (supports dot notation).
	 * @param mixed  $val Value to set.
	 *
	 * @return void
	 */
	final function set_url_param($key,$val){
		mw_array_set_sub_key($key,$val,$this->urlparams);	
	}
	
	/**
	 * Retrieves a URL parameter by key or returns all parameters.
	 *
	 * @param string|false $key Parameter key or false to return all.
	 *
	 * @return mixed Parameter value, array of all parameters, or null if not found.
	 */
	final function get_url_param($key=false){
		if(!$key){
			return 	$this->urlparams;
		}
		return mw_array_get_sub_key($this->urlparams,$key);	
	}
	
	/**
	 * Gets the default filename for URL generation.
	 *
	 * @return string|false The default file or false if not set.
	 */
	function get_url_def_file(){
		return $this->url_def_file;	
	}
	
	/**
	 * Generates the base URL for this subinterface.
	 *
	 * Delegates to the main interface with optional filename.
	 *
	 * @param string|false $file Optional filename to append.
	 *
	 * @return string The base URL.
	 */
	function get_url_base($file=false){
		if(!$file){
			$file=$this->get_url_def_file();	
		}
		
		return $this->maininterface->get_url_base($file);	
	}

	//subinerfaces
	
	/**
	 * Clears the current subinterface selection.
	 *
	 * @return void
	 */
	final function set_no_subinterface(){
		$this->current_sub_interface=false;
			
	}
	
	/**
	 * Navigates back to the parent subinterface by clearing parent's current child.
	 *
	 * @return void
	 */
	function go_to_parent(){
		if($this->parent_subinterface){
	
			$this->parent_subinterface->set_no_subinterface();	
		}
	}
	
	/**
	 * Handler called when a subinterface is not allowed.
	 *
	 * Override to customize behavior when access is denied.
	 *
	 * @return void
	 */
	function on_subinterface_not_allowed(){
		$this->set_no_subinterface();
			
	}
	/**
	 * Sets the given subinterface as the currently active one.
	 *
	 * Optionally checks if the subinterface is allowed before assigning it. 
	 * Also sets the `selected_as_current` flag on the subinterface.
	 *
	 * @param mwmod_mw_ui_sub_uiabs|false $item The subinterface to set as current, or false to unset.
	 * @param bool $check_allowed Whether to verify if the subinterface is allowed.
	 * 
	 * @return mwmod_mw_ui_sub_uiabs|false The subinterface if successfully set, or false on failure.
	 */
	final function set_current_subinterface($item=false,$check_allowed=true){
		if(!$item){
			return false;	
		}
		if($check_allowed){
			if(!$item->is_allowed()){
				
				return false;
			}
		}
		$this->current_sub_interface=$item;
		$item->selected_as_current=true;
		
		
		return $this->current_sub_interface;
	}

	/**
	 * Sets the current subinterface by its code identifier.
	 *
	 * Looks up the subinterface by code, optionally checks if it's allowed, 
	 * and sets it as current. Calls on_subinterface_not_allowed() if code is 
	 * invalid or access is denied.
	 *
	 * @param string|false $code The subinterface code to activate.
	 * @param bool $check_allowed Whether to verify if the subinterface is allowed.
	 * 
	 * @return mwmod_mw_ui_sub_uiabs|false The subinterface if successfully set, or false on failure.
	 */
	function set_current_subinterface_by_code($code=false,$check_allowed=true){
		if(!$code){
			return $this->on_subinterface_not_allowed();
		}
		if(!$si=$this->get_subinterface($code)){
			return $this->on_subinterface_not_allowed();
		}
		if($check_allowed){
			if(!$si->is_allowed()){
				return $this->on_subinterface_not_allowed();
			}
		}
		return $this->set_current_subinterface($si,false);
	}

	/**
	 * Gets the subinterface code from the request.
	 *
	 * Looks for the subinterface request variable in $_REQUEST and returns 
	 * the code if present, otherwise returns the default subinterface code.
	 *
	 * @return string|false The requested subinterface code or the default code.
	 */
	function get_sub_insterface_request_code(){
		
		$key=$this->get_subinterface_request_var();
		if($key and isset($_REQUEST[$key])){
			if($code=$_REQUEST[$key]){
				return $code;
			}
			
		}
		return $this->subinterface_def_code;	
	}
	/**
	 * Retrieves a subinterface instance by its code.
	 *
	 * Validates the code, initializes subinterfaces if not already done, and creates
	 * the subinterface if it doesn't exist yet.
	 *
	 * @param string $cod The subinterface code.
	 *
	 * @return mwmod_mw_ui_sub_uiabs|false The subinterface instance if found or created, false otherwise.
	 */
	final function get_subinterface($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;
		}
		$this->init_all_subinterfaces_once();
		if(isset($this->___subinterfaces[$cod])){
			return 	$this->___subinterfaces[$cod];
		}
		if($su=$this->create_subinterface($cod)){
			return $su;
		}
		return false;
		
	}

	/**
	 * Adds a subinterface to this interface's collection.
	 *
	 * Validates the code and registers the subinterface. Notifies the subinterface 
	 * that it has been added as a child.
	 *
	 * @param mwmod_mw_ui_sub_uiabs $item The subinterface instance to add.
	 * @param string|false $cod Optional code to use. If not provided, uses the item's code.
	 *
	 * @return mwmod_mw_ui_sub_uiabs|false The added subinterface or false on failure.
	 */
	final function add_subinterface($item,$cod=false){
		if(!$cod){
			$cod=$item->code;	
		}
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;
		}
		//$this->__subinterfaces[$cod]=$item;
		$this->___subinterfaces[$cod]=$item;
		$item->added_as_child($cod,$this);
		return $item;
		
	}

	/**
	 * Adds a new subinterface to this interface.
	 *
	 * Convenience method that uses the subinterface's own code property.
	 *
	 * @param mwmod_mw_ui_sub_uiabs $subinterface The subinterface to add.
	 *
	 * @return mwmod_mw_ui_sub_uiabs|false The added subinterface or false on failure.
	 */
	function add_new_subinterface($subinterface){
		
		return $this->add_subinterface($subinterface,$subinterface->code);
		
	}

	/**
	 * Hook for loading all subinterfaces.
	 *
	 * Override in child classes to programmatically create and add subinterfaces.
	 *
	 * @return void
	 */
	function load_all_subinterfases(){
		//for children
	}
	
	/**
	 * Ensures all subinterfaces are initialized, but only once.
	 *
	 * @return void
	 */
	final function init_all_subinterfaces_once(){
		if(!$this->___all_subinterfaces_loaded){
			$this->get_all_subinterfaces();
		}
		
	}

	/**
	 * Gets all subinterfaces, triggering initialization if needed.
	 *
	 * Calls load_all_subinterfases() to allow child classes to register their subinterfaces.
	 *
	 * @return array<string,mwmod_mw_ui_sub_uiabs> Array of subinterfaces keyed by code.
	 */
	final function get_all_subinterfaces(){
		if($this->___all_subinterfaces_loaded){
			return $this->___all_subinterfaces;
		}
		$this->___all_subinterfaces_loaded=true;
		$this->load_all_subinterfases();	
		$this->___all_subinterfaces=array();
		if(!$su=$this->get_subinterfaces()){
			return $this->___all_subinterfaces;
		}
		foreach($su as $cod=>$item){
			$this->___all_subinterfaces[$cod]=$item;	
		}
		return $this->___all_subinterfaces;
	}

	/**
	 * Gets the collection of registered subinterfaces.
	 *
	 * @return array<string,mwmod_mw_ui_sub_uiabs> Array of subinterfaces keyed by code.
	 */
	final function get_subinterfaces(){
		return $this->___subinterfaces;
	}

	//formulario e inputs

	/**
	 * Gets the form action URL for this subinterface.
	 *
	 * Returns a URL pointing to the current subinterface with optional parameters.
	 *
	 * @param array|false $args Optional URL parameters to include.
	 * @param string|false $file Optional filename to use in the URL.
	 *
	 * @return string The form action URL.
	 */
	function get_frm_action($args=false,$file=false){
		$code=$this->get_subinterface_code();
		return $this->get_url_subinterface($code,$args,$file);	
	}

	/**
	 * Creates a new form instance for this subinterface.
	 *
	 * Initializes a form with appropriate template and action URL.
	 *
	 * @deprecated Use modern form builder patterns instead of mwmod_mw_datafield_frm.
	 * @param string $name The form's name attribute.
	 *
	 * @return mwmod_mw_datafield_frm The new form instance.
	 */
	function new_frm($name="frm"){
		$frm= new mwmod_mw_datafield_frm($name);
		if(!$t=$this->get_template()){
			$frm->set_template($t->new_frm_template());	
		}
		//$frm->set_sub_interface($this);
		$frm->action=$this->get_frm_action();
		return $frm;
	}

	/**
	 * Generates HTML form from a data field creator instance.
	 *
	 * @deprecated Use modern form builder patterns instead of mwmod_mw_datafield classes.
	 * @param mwmod_mw_datafield_creator|false $cr The data field creator instance.
	 *
	 * @return string|false The HTML form content or false on failure.
	 */
	function get_html_frm_from_datafieldcreator($cr){
		if(!$cr){
			return false;
		}
		if(!$frm=$this->new_frm()){
			return false;
		}
		$frm->set_datafieldcreator($cr);
		return $frm->get_html();
	
	}

	/**
	 * Creates a new data field creator instance.
	 *
	 * Used to build forms with dynamic field configurations.
	 *
	 * @deprecated Use modern form builder patterns instead of mwmod_mw_datafield_creator.
	 * @param array $items Reference to array of field items.
	 *
	 * @return mwmod_mw_datafield_creator The new data field creator.
	 */
	function new_datafield_creator(&$items=array()){
		$cr=new mwmod_mw_datafield_creator($items);
		//$cr->set_sub_interface($this);
		return $cr;
	}

	//usuario
	/** @return mwmod_mw_users_user  */
	function get_admin_current_user(){
		return $this->maininterface->get_admin_current_user();	
	}

	//menu superior

	/**
	 * Checks if this subinterface is allowed to appear in the menu.
	 *
	 * By default, delegates to is_allowed().
	 *
	 * @return bool True if allowed in menu, false otherwise.
	 */
	function is_allowed_on_mnu(){
		return $this->is_allowed();	
	}
	
	/**
	 * Adds this subinterface as a menu item under a parent menu item.
	 *
	 * Creates a menu item with this interface's code, label, and URL. 
	 * Marks it active if this is the current subinterface.
	 *
	 * @param mwmod_mw_mnu_mnuitem $parent_mnu_item The parent menu item.
	 *
	 * @return mwmod_mw_mnu_mnuitem The newly added menu item.
	 */
	function add_as_sub_mnu_item($parent_mnu_item){
		$item=new mwmod_mw_mnu_mnuitem($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$parent_mnu_item,$this->get_url());
		if($this->is_current()){
			$item->set_active(true);
				
		}
		if($this->tooltip){
			$item->tooltip=	$this->tooltip;
		}
		return $parent_mnu_item->add_item_by_item($item);
			
	}

	/**
	 * Adds this subinterface to a side menu.
	 *
	 * Convenience method that delegates to add_2_mnu().
	 *
	 * @param mwmod_mw_mnu_mnu $mnu The menu instance.
	 * @param bool $checkallowed Whether to check if the interface is allowed.
	 *
	 * @return mwmod_mw_mnu_mnuitem|false The added menu item or false.
	 */
	function add_2_side_mnu($mnu,$checkallowed=true){
		return $this->add_2_mnu($mnu,$checkallowed);	
	}

	/**
	 * Adds this subinterface as a menu item to a menu.
	 *
	 * Creates a menu item with this interface's code, label, and URL. 
	 * Optionally checks permissions. Marks active if current. Recursively 
	 * adds child submenus if the menu supports sub-menus.
	 *
	 * @param mwmod_mw_mnu_mnu $mnu The menu instance.
	 * @param bool $checkallowed Whether to check if the interface is allowed.
	 *
	 * @return mwmod_mw_mnu_mnuitem|false The added menu item or false on failure.
	 */
	function add_2_mnu($mnu,$checkallowed=true){
		if(!$mnu){
			return false;	
		}
		if($checkallowed){
			if(!$this->is_allowed_on_mnu()){
				return false;
			}
		}
		if(!$item=$mnu->add_new_item($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$this->get_url())){
			
			return false;	
		}
		if($this->is_current()){
			$item->set_active(true);	
		}
		$this->prepare_mnu_item($item);
		
		
		if($mnu->allow_sub_menus()){
			$this->add_sub_mnus($item,$checkallowed);	
		}
		
		return $item;
	}

	/**
	 * Adds child submenus under a parent menu item.
	 *
	 * Override in child classes to add subinterface menu items.
	 *
	 * @param mwmod_mw_mnu_mnuitem $mnuitem The parent menu item.
	 * @param bool $checkallowed Whether to check if subinterfaces are allowed.
	 *
	 * @return void
	 */
	function add_sub_mnus($mnuitem,$checkallowed=true){
		//agregar aca
	}

	/**
	 * Prepares and customizes a menu item.
	 *
	 * Applies icon classes and other customizations to the menu item.
	 *
	 * @param mwmod_mw_mnu_mnuitem $item The menu item to prepare.
	 *
	 * @return void
	 */
	function prepare_mnu_item($item){
		if($this->mnuIconClass){
			$item->addInnerHTML_icon($this->mnuIconClass);
		}
		//aca puede colocarse icono	
	}

	/**
	 * Gets the code to use for menu identification.
	 *
	 * @return string The code for this interface in menus.
	 */
	function get_cod_for_mnu(){
		return $this->get_code_for_parent();	
	}

	/**
	 * Gets the label to display in menus.
	 *
	 * @return string The menu label for this interface.
	 */
	function get_mnu_lbl(){
		return $this->get_name();	
	}

	//menú interno
	private $mnu_man;
	
	/**
	 * Gets the internal menu manager, creating it if necessary.
	 *
	 * The menu manager handles the subinterface's internal navigation menu.
	 *
	 * @return mwmod_mw_mnu_man The menu manager instance.
	 */
	final function get_mnu_man(){
		if(!isset($this->mnu_man)){
			$this->mnu_man=$this->create_mnu_man();
			$this->mnu_man_on_create($this->mnu_man);
		}
		return $this->mnu_man;
	}

	/**
	 * Creates the menu manager instance.
	 *
	 * @return mwmod_mw_mnu_man A new menu manager.
	 */
	function create_mnu_man(){
		$m=new mwmod_mw_mnu_man();
		$m->set_sub_interface($this);
		return $m;
	}

	/**
	 * Hook called after menu manager creation.
	 *
	 * Override to populate the menu with items. Default implementation 
	 * adds items to the "sumnu" menu.
	 *
	 * @param mwmod_mw_mnu_man $mnu_man The menu manager instance.
	 *
	 * @return void
	 */
	function mnu_man_on_create($mnu_man){
		$mnu=$mnu_man->get_item("sumnu");
		$this->add_mnu_items($mnu);	
	}

	/**
	 * Internal accessor for menu manager.
	 *
	 * @internal
	 * @return mwmod_mw_mnu_man The menu manager instance.
	 */
	final function __get_priv_mnu_man(){
		return $this->get_mnu_man(); 	
	}
	
	/**
	 * Creates the main menu instance for this subinterface.
	 *
	 * @return mwmod_mw_mnu_mnu The menu instance.
	 */
	function create_mnu(){
		$m=$this->get_mnu_man();
		return $m->get_item("sumnu");
		
		
		//return new mwmod_mw_mnu_ui($this);	
	}

	/**
	 * Gets the menu instance, creating it if necessary.
	 *
	 * @return mwmod_mw_mnu_mnu|false The menu instance or false on failure.
	 */
	function get_mnu(){
		if(!isset($this->mnu)){
			if($this->mnu=$this->create_mnu()){
				//$this->add_mnu_items($this->mnu);	
			}
		}
		return $this->mnu;
	}
	function add_mnu_items($mnu){
		//
	}
	/**
	 * Retrieves multiple subinterfaces by a comma-separated list or array of codes.
	 *
	 * Optionally checks if each subinterface is allowed before including it.
	 *
	 * @param string|array $code Comma-separated string or array of subinterface codes.
	 * @param bool $checkallowed If true, only includes allowed subinterfaces.
	 *
	 * @return array<string, mwmod_mw_ui_sub_uiabs>|false Associative array of subinterfaces by code, or false if input is invalid or none found.
	 */
	function get_subinterfaces_by_code($code,$checkallowed=true){
		if(!$code){
			return false;
		}
		if(!is_array($code)){
			$code=explode(",",$code);
		}
		$r=array();
		foreach($code as $c){
			$cc=trim($c);
			if($su=$this->get_subinterface($cc)){
				if($checkallowed){
					if($su->is_allowed()){
						$r[$cc]=$su;	
					}
				}else{
					$r[$cc]=$su;		
				}
			}
		}
		if(sizeof($r)){
			return $r;	
		}
			
	}

	/**
	 * Adds subinterfaces to a menu by code list.
	 *
	 * Accepts a comma-separated string or array of subinterface codes 
	 * and adds each to the given menu.
	 *
	 * @param mwmod_mw_mnu_mnu $mnu The menu instance.
	 * @param string|array $code Comma-separated string or array of subinterface codes.
	 *
	 * @return string|false The last processed code or false.
	 */
	function add_sub_interface_to_mnu_by_code($mnu,$code){
		if(!$code){
			return false;
		}
		if(!is_array($code)){
			$code=explode(",",$code);
		}
		$r=array();
		foreach($code as $c){
			$cc=trim($c);
			$r[$cc]=$this->add_sub_interface_to_mnu_by_code_item($mnu,$cc);	
		}
		
		return $cc;
	}

	/**
	 * Adds a single subinterface to a menu by code.
	 *
	 * @param mwmod_mw_mnu_mnu $mnu The menu instance.
	 * @param string $code The subinterface code.
	 *
	 * @return mwmod_mw_mnu_mnuitem|false The added menu item or false on failure.
	 */
	function add_sub_interface_to_mnu_by_code_item($mnu,$code){
		if(!$si=$this->get_subinterface($code)){
			return false;	
		}
		
		return $si->add_2_mnu($mnu);
	}

	//exec

	/**
	 * Executes when no subinterface is active.
	 *
	 * Override to provide custom execution logic when no child subinterface handles the request.
	 *
	 * @return void
	 */
	function do_exec_no_sub_interface(){
		//extender!!!;
	}

	/**
	 * Hook called before execution begins.
	 *
	 * Adds required JavaScript and CSS resources.
	 *
	 * @return void
	 */
	function before_exec(){
		$this->add_req_js_scripts();	
		$this->add_req_css();
	}

	/**
	 * Adds required JavaScript scripts for this subinterface.
	 *
	 * Override to add custom scripts. Alternative: use prepare_before_exec_no_sub_interface().
	 *
	 * @return void
	 */
	function add_req_js_scripts(){
		//ver mwmod_mw_ui_debug_frm	
		//altarnativa a esto es prepare_before_exec_no_sub_interface
	}

	/**
	 * Adds required CSS stylesheets for this subinterface.
	 *
	 * Override to add custom styles. Alternative: use prepare_before_exec_no_sub_interface().
	 *
	 * @return void
	 */
	function add_req_css(){
		//ver mwmod_mw_ui_debug_frm	
		//altarnativa a esto es prepare_before_exec_no_sub_interface
	}

	/**
	 * Prepares the UI before executing when no subinterface is active.
	 *
	 * Override to customize UI preparation logic.
	 *
	 * @return void
	 */
	function prepare_before_exec_no_sub_interface(){
		//$p=new mwmod_mw_html_manager_uipreparers_default($this);
		//$p->preapare_ui();
	}

	/**
	 * Prepares and executes when no subinterface is active.
	 *
	 * Combines preparation and execution steps.
	 *
	 * @return void
	 */
	function prepare_and_do_exec_no_sub_interface(){
		$this->prepare_before_exec_no_sub_interface();
		$this->do_exec_no_sub_interface();
	}

	/**
	 * Main execution method for the subinterface.
	 *
	 * Checks permissions, sets up the execution chain, and either delegates 
	 * to a child subinterface or executes directly if none is active.
	 *
	 * @return false|void False if not allowed, void otherwise.
	 */
	function do_exec(){
		
		if(!$this->is_allowed()){
			return false;	
		}
		$this->in_exec_chain=true;
		$this->before_exec();
		if($si=$this->set_current_subinterface_by_code($this->get_sub_insterface_request_code())){
			
			if($si->is_allowed()){
				$si->do_exec();	
			}

			
		}else{
			
			$this->prepare_and_do_exec_no_sub_interface();	
		}
		//$this->do_exec_after_subui();
	}
	
	//exec output

	/**
	 * Executes page output as a subinterface.
	 *
	 * Delegates to the template to render the subinterface within a parent layout.
	 *
	 * @return void
	 */
	function do_exec_page_in_as_sub(){
		if(!$template=$this->get_template()){
			$this->do_exec_page_in();
			return;	
		}
		$template->exec_page_full_body_sub_interface();
		
	}

	/**
	 * Gets the deepest active subinterface in the hierarchy.
	 *
	 * Traverses the current subinterface chain to find the final active one.
	 * Marks this interface as current if it's the final one.
	 *
	 * @return mwmod_mw_ui_sub_uiabs The final current subinterface.
	 */
	function get_this_or_final_current_subinterface(){
		if($this->current_sub_interface){
			if($this->current_sub_interface->is_allowed()){
				if($r=$this->current_sub_interface->get_this_or_final_current_subinterface()){
					return $r;	
				}
			}
		}
		$this->is_current=true;
		return $this;
	}

	/**
	 * Executes page in single mode.
	 *
	 * Delegates to do_exec_page().
	 *
	 * @return void
	 */
	function do_exec_page_single_mode(){
		$this->do_exec_page();	
	}

	/**
	 * Executes on the template, delegating to child if present.
	 *
	 * If a current subinterface exists and is allowed, delegates rendering to it. 
	 * Otherwise, executes this interface's page content.
	 *
	 * @param object $template The template instance.
	 *
	 * @return void
	 */
	function do_exec_on_template($template){
		if($this->current_sub_interface){
			if($this->current_sub_interface->is_allowed()){
				//echo "ss";
				return $this->current_sub_interface->do_exec_page_in_as_sub();	
			}
		}
		$this->do_exec_page_in();
			
	}

	/**
	 * Checks if the header should be omitted.
	 *
	 * Override to return true for pages that don't need headers.
	 *
	 * @return bool False by default.
	 */
	function omit_header(){
		return false;	
	}

	/**
	 * Executes page content within the main template.
	 *
	 * @param object $maintemplate The main template instance.
	 *
	 * @return void
	 */
	function do_exec_on_page_in_on_maintemplate($maintemplate){
		$this->do_exec_page_in();
			
	}

	/**
	 * Executes page rendering.
	 *
	 * Delegates to do_exec_page_in().
	 *
	 * @return void
	 */
	function do_exec_page(){
		$this->do_exec_page_in();
	}

	/**
	 * Renders the page content.
	 *
	 * Override in child classes to provide actual page output.
	 *
	 * @return void
	 */
	function do_exec_page_in(){
		//extender
	}
	
	//permisos

	/**
	 * Checks if the current user is allowed to access this subinterface.
	 *
	 * Override in child classes to implement permission logic.
	 *
	 * @return bool False by default. Override to implement actual permission checks.
	 */
	function is_allowed(){
		return false;
		//return $this->allow("admin");	
	}

	/**
	 * Checks if an action is allowed for the current user.
	 *
	 * Delegates to the main interface's permission system.
	 *
	 * @param string $action The action to check.
	 * @param mixed $params Optional parameters for the permission check.
	 *
	 * @return bool True if allowed, false otherwise.
	 */
	function allow($action,$params=false){
		return $this->maininterface->allow($action,$params);	
	}

	//template

	/**
	 * Creates the template instance for this subinterface.
	 *
	 * @param object|false $main_interface_template Optional parent template. If not provided, uses main interface's template.
	 *
	 * @return object The new template instance.
	 */
	function create_template($main_interface_template=false){
		if(!$main_interface_template){
			$main_interface_template=$this->maininterface->get_template();	
		}
		$t=$main_interface_template->new_sub_interface_template($this);
		return $t;
	}

	/**
	 * Gets the template instance, creating it if necessary.
	 *
	 * @param object|false $main_interface_template Optional parent template.
	 *
	 * @return mwmod_mw_ui_sub_uitemplate The template instance.
	 */
	final function get_template($main_interface_template=false){
		if(!isset($this->template)){
			$this->template=$this->create_template($main_interface_template);
		}
		return $this->template;
	}
	
	/**
	 * Internal accessor for template.
	 *
	 * @internal
	 * @return mwmod_mw_ui_sub_uitemplate The template instance.
	 */
	final function __get_priv_template(){
		return $this->get_template(); 	
	}

	//info

	/**
	 * Gets the display name of the subinterface.
	 *
	 * Returns the title if available, otherwise returns the code.
	 *
	 * @return string The subinterface name.
	 */
	function get_name(){
		if($r=$this->get_title()){
			return $r;	
		}
		return $this->code;
	}

	/**
	 * String representation of the subinterface.
	 *
	 * @return string The subinterface name.
	 */
	function __toString(){
		return $this->get_name()."";	
	}
	
	/**
	 * Gets the title of the subinterface.
	 *
	 * @return string The title.
	 */
	function get_title(){
		return $this->__get_priv_def_title();	
	}

	/**
	 * Internal method to get the default title.
	 *
	 * Returns the class name if no title is set.
	 *
	 * @internal
	 * @return string The title or class name.
	 */
	final function __get_priv_def_title(){
		if(!$this->def_title){
			return get_class($this);	
		}
		
		return $this->def_title; 	
	}

	/**
	 * Sets the default title for this subinterface.
	 *
	 * @param string $tit The title to set.
	 *
	 * @return void
	 */
	final function set_def_title($tit){
		$this->def_title=$tit;
	}
	
	
	//por verificar
	
	
	/*
	function do_exec_page_direct(){
		$this->url_def_file="interface.php";
		$this->do_exec_page_in();	
	}
	*/

	/**
	 * Gets HTML for this subinterface in a parent chain breadcrumb.
	 *
	 * Returns a linked title for use in breadcrumb navigation.
	 *
	 * @return string HTML anchor tag with URL and title.
	 */
	function get_html_for_parent_chain_on_child_title(){
		$url=$this->get_url();
		return "<a href='$url'>".$this->get_title_for_box()."</a>";
	}

	/**
	 * Gets HTML for the full parent chain breadcrumb.
	 *
	 * Builds a breadcrumb trail showing all parent subinterfaces.
	 *
	 * @return string HTML breadcrumb string with " - " separators.
	 */
	function get_html_parents_chain(){
		$l=array();
		if($list=$this->get_parents_chain()){
			foreach($list as $p){
				$l[]=$p->get_html_for_parent_chain_on_child_title();	
			}
		}
		$l[]=$this->get_html_for_parent_chain_on_child_title();
		return implode(" - ",$l);
		
	}

	/**
	 * Gets HTML for the parent route with custom separator.
	 *
	 * @param string $sep The separator to use between route elements.
	 *
	 * @return string HTML route string.
	 */
	function get_html_parents_route($sep=" - "){
		$l=array();
		if($list=$this->get_parents_chain()){
			foreach($list as $p){
				if($h=$p->get_html_for_parent_chain_on_child_title()){
					$l[]=$h;
				}
				//$p->get_html_for_parent_chain_on_child_title();	
			}
		}
		if(!sizeof($l)){
			return "";
		}
		//$l[]=$this->get_html_for_parent_chain_on_child_title();
		return implode($sep,$l);
		
	}
	
	/**
	 * Gets the title for display in a box or container.
	 *
	 * Returns the full parent chain as HTML.
	 *
	 * @return string HTML breadcrumb chain.
	 */
	function get_title_for_box_html(){
		return $this->get_html_parents_chain();
			
	}

	/**
	 * Gets a formatted title with subtitle for the selected UI header.
	 *
	 * @param string $title The main title.
	 * @param string|false $subtitle Optional subtitle. If not provided, uses this interface's title.
	 *
	 * @return string HTML formatted title with subtitle in h5 tag.
	 */
	function get_selected_ui_header_title_and_sub_title($title,$subtitle=false){
		if(!$subtitle){
			$subtitle=$this->get_title();	
		}
		return $title."<h5>$subtitle</h5>";
	}

	/**
	 * Checks if this interface is responsible for sub-interface header titles.
	 *
	 * Override to return true if this interface should control titles for child interfaces.
	 *
	 * @return bool False by default.
	 */
	function isResponsableForSubInterfacesHeaderTitle(){
		return false;
	}

	/**
	 * Gets the header title for this subinterface when it's responsible for sub-UIs.
	 *
	 * @return string The title for the header.
	 */
	function getHeaderTitleForSubUIWhenResponsable(){
		return $this->get_title();
	}

	/**
	 * Gets the UI responsible for sub-interface header titles.
	 *
	 * Traverses up the parent chain to find the responsible UI.
	 *
	 * @return mwmod_mw_ui_sub_uiabs|false The responsible UI or false if none found.
	 */
	function getUIResponsableForSubInterfacesHeaderTitle(){
		if($this->isResponsableForSubInterfacesHeaderTitle()){
			return $this;
		}
		if($this->parent_subinterface){
			return $this->parent_subinterface->getUIResponsableForSubInterfacesHeaderTitle();
		}
		return false;

	}

	/**
	 * Gets the parent UI responsible for sub-interface header titles.
	 *
	 * @return mwmod_mw_ui_sub_uiabs|false The parent responsible UI or false if none found.
	 */
	function getParentUIResponsableForSubInterfacesHeaderTitle(){
		if($this->parent_subinterface){
			return $this->parent_subinterface->getUIResponsableForSubInterfacesHeaderTitle();
		}
		return false;
	}

	/**
	 * Gets the selected UI header title.
	 *
	 * If a parent is responsible for titles, uses that. Otherwise uses this interface's title.
	 *
	 * @return string The header title.
	 */
	function get_selected_ui_header_title(){
		if($parent=$this->getParentUIResponsableForSubInterfacesHeaderTitle()){
			if($t=$parent->getHeaderTitleForSubUIWhenResponsable()){
				return $t;
			}
		}
		return $this->get_title();	
	}

	/**
	 * Gets the selected UI header subtitle.
	 *
	 * Returns this interface's title if a parent is responsible, otherwise returns false.
	 *
	 * @return string|false The subtitle or false.
	 */
	function get_selected_ui_header_subtitle(){
		if($parent=$this->getParentUIResponsableForSubInterfacesHeaderTitle()){
			return $this->get_title();	
		}
		return false;

		
	}

	/**
	 * Gets the title for display in a box.
	 *
	 * @return string The title.
	 */
	function get_title_for_box(){
		//return $this->get_html_parents_chain();
		
		return $this->get_title();	
	}

	/**
	 * Determines if children inherit this interface's permissions.
	 *
	 * Override to return true for permission inheritance.
	 *
	 * @return bool False by default.
	 */
	function childrenInheritPermissions(){
		return false;
	}

	/**
	 * Gets the full code path with separators.
	 *
	 * Builds a hierarchical code string from all parents and this interface.
	 *
	 * @param string $sep The separator to use between codes.
	 *
	 * @return string|false The full code path or false if no parents.
	 */
	function get_full_cod($sep="-"){
		if(!$sub_uis=$this->get_parents_chain(true,true)){
			return false;
		}
		$suicods=array();
		foreach($sub_uis as $ui){
			$suicods[]=$ui->get_code_for_parent();	
		}
		if(!sizeof($suicods)){
			return false;	
		}
		return implode($sep,$suicods);

	}

	/**
	 * Gets the parent chain of subinterfaces.
	 *
	 * Builds an array of parent subinterfaces in the hierarchy.
	 *
	 * @param bool $top2bot If true, orders from top to bottom. If false, bottom to top.
	 * @param bool $addthis If true, includes this interface in the chain.
	 *
	 * @return array<mwmod_mw_ui_sub_uiabs> Array of parent subinterfaces.
	 */
	function get_parents_chain($top2bot=true,$addthis=false){
		$r=array();
		if($addthis){
			$r[]=$this;	
		}
		if($p=$this->__get_priv_current_parent_subinterface()){
			$p->add2parents_chain($r);
		}
		if($top2bot){
			return array_reverse($r);	
		}
		return $r;
	}

	
	/**
	 * Adds this interface and its parents to a chain list.
	 *
	 * Recursively builds the parent chain by reference.
	 *
	 * @param array $list Reference to the list being built.
	 *
	 * @return void
	 */
	function add2parents_chain(&$list){
		if(!$list){
			$list=array();	
		}
		$list[]=$this;
		if($p=$this->__get_priv_current_parent_subinterface()){
			$p->add2parents_chain($list);
		}
		return $list;
	}


	/**
	 * Sets the parent subinterface for this interface.
	 *
	 * @param mwmod_mw_ui_sub_uiabs $parent The parent subinterface.
	 *
	 * @return void
	 */
	final function set_parent_sub_interface($parent){
		$this->parent_subinterface=$parent;
		$this->current_parent_subinterface=$parent;	
	}

	/**
	 * Sets the current parent subinterface with a specific code.
	 *
	 * @param string $cod The code to use for this interface within the parent.
	 * @param mwmod_mw_ui_sub_uiabs $parent The parent subinterface.
	 *
	 * @return void
	 */
	final function set_current_parent_sub_interface($cod,$parent){
		$this->code_for_parent=$cod;
		$this->current_parent_subinterface=$parent;	
	}

	/**
	 * Gets the code used for parent identification.
	 *
	 * @internal
	 * @return string The code for parent or default code.
	 */
	final function __get_priv_code_for_parent(){
		if(!$this->code_for_parent){
			return $this->code; 	
		}
		return $this->code_for_parent; 	
	}

	/**
	 * Gets the current parent subinterface.
	 *
	 * Falls back to parent_subinterface if current is not set.
	 *
	 * @internal
	 * @return mwmod_mw_ui_sub_uiabs|false The parent subinterface or false.
	 */
	final function __get_priv_current_parent_subinterface(){
		if(!$this->current_parent_subinterface){
			return $this->parent_subinterface; 	
		}
		return $this->current_parent_subinterface; 	
	}

	/**
	 * Gets the parent subinterface.
	 *
	 * @internal
	 * @return mwmod_mw_ui_sub_uiabs|false The parent subinterface or false.
	 */
	final function __get_priv_parent_subinterface(){
		return $this->parent_subinterface; 	
	}

	/**
	 * Called when this interface is added as a child to a parent.
	 *
	 * Sets up parent relationship and resets URL parameters.
	 *
	 * @param string $cod The code to use in the parent.
	 * @param mwmod_mw_ui_sub_uiabs $parent The parent subinterface.
	 *
	 * @return bool True on success.
	 */
	function added_as_child($cod,$parent){
		$this->set_current_parent_sub_interface($cod,$parent);
		$this->reset_url_params();
		$this->init_cmd_params();//new 20250525
		return true;	
	}
	
	/**
	 * Gets the code to use when referenced by parent.
	 *
	 * @return string The code for parent or default code.
	 */
	function get_code_for_parent(){
		if($this->code_for_parent){
			return $this->code_for_parent;	
		}
		return $this->code;
	}

	/**
	 * Gets the current child subinterface.
	 *
	 * @internal
	 * @return mwmod_mw_ui_sub_uiabs|false The current subinterface or false.
	 */
	final function __get_priv_current_sub_interface(){
		return $this->current_sub_interface; 	
	}
	

	
	/**
	 * Creates a subinterface by code.
	 *
	 * Delegates to do_create_subinterface().
	 *
	 * @param string $cod The subinterface code.
	 *
	 * @return mwmod_mw_ui_sub_uiabs|false The created subinterface or false.
	 */
	function create_subinterface($cod){
		return $this->do_create_subinterface($cod);
		
	}

	/**
	 * Checks if dynamic subinterface creation by code is allowed.
	 *
	 * Override to return true to enable dynamic subinterface creation.
	 *
	 * @return bool False by default.
	 */
	function allowcreatesubinterfacechildbycode(){
		return false;	
	}
	
	/**
	 * Called after a subinterface child is created.
	 *
	 * Registers the newly created subinterface.
	 *
	 * @param string $cod The subinterface code.
	 * @param mwmod_mw_ui_sub_uiabs $item The created subinterface.
	 *
	 * @return mwmod_mw_ui_sub_uiabs The registered subinterface.
	 */
	function subinterface_child_created($cod,$item){
		$this->add_subinterface($item,$cod);
		
		return $item;	
	}

	/**
	 * Internal method to create a subinterface by code.
	 *
	 * Checks permissions and validates the code before attempting creation.
	 *
	 * @param string $cod The subinterface code.
	 *
	 * @return mwmod_mw_ui_sub_uiabs|false The created subinterface or false.
	 */
	function do_create_subinterface($cod){
		if(!$this->allowcreatesubinterfacechildbycode()){
			return false;	
		}
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;
		}
		$method="_do_create_subinterface_child_$cod";
		if(method_exists($this,$method)){
			if($item=$this->$method($cod)){
				return $this->subinterface_child_created($cod,$item);
			}
		}else{
			if($item=$this->do_create_subinterface_by_cod_from_class_name($cod)){
				return $this->subinterface_child_created($cod,$item);
			}
				
		}
		
	}

	
	
	
	/**
	 * Gets the request variable name for this subinterface level.
	 *
	 * Delegates to main interface to get the appropriate variable name based on depth.
	 *
	 * @return string The request variable name (e.g., "subinterface", "subinterface1", etc.).
	 */
	function get_subinterface_request_var(){
		return $this->maininterface->get_subinterface_request_var_by_deep($this->get_deep()+1);
		/*
		if(!$deep=$this->get_deep()){
			return "subinterface";	
		}else{
			return "subinterface$deep";		
		}
		*/
	}

	/**
	 * Gets the depth level of this subinterface in the hierarchy.
	 *
	 * Recursively calculates depth from root.
	 *
	 * @return int The depth level (0 for root).
	 */
	function get_deep(){
		if(!$this->parent_subinterface){
			return 0;	
		}
		return $this->parent_subinterface->get_deep()+1;
	}
	
	
	/**
	 * Generates an HTML link to a subinterface.
	 *
	 * @param string $lbl The link label text.
	 * @param string|false $subinterface The target subinterface code.
	 * @param array|false $args Optional URL parameters.
	 * @param string|false $file Optional filename.
	 *
	 * @return string HTML anchor tag.
	 */
	function get_html_link($lbl,$subinterface=false,$args=false,$file=false){
		$url=$this->get_url_subinterface($subinterface,$args,$file);
		return "<a href='$url'>$lbl</a>";	
	}
	
	
	function XXXXget_input_template(){
		//DEPRECATED: use maininterface directly
		return $this->maininterface->get_input_template(); 	
	}

	/**
	 * Creates a new table template.
	 *
	 * @return object The table template instance.
	 */
	function new_tbl_template(){
		$tm=$this->get_template();
		return $tm->new_tbl_template();	
	}

	
	/**
	 * Called after code validation succeeds.
	 *
	 * Resets URL parameters and initializes the interface.
	 *
	 * @return void
	 */
	final function after_code_ok(){
		
		$this->reset_url_params();
		
	}

	/**
	 * Hook for extending after code validation.
	 *
	 * Override to add custom logic after code is validated. Called after after_code_ok().
	 *
	 * @return void
	 */
	function after_code_ok_sub(){
		//para extender, se ejecuta después de 	after_code_ok
	}

	/**
	 * Sets the code for this subinterface.
	 *
	 * Can only be set once. Validates the code format and calls after_code_ok().
	 *
	 * @param string $cod The code to set.
	 *
	 * @return false|void False on failure, void on success.
	 */
	final function set_code($cod){
		if(isset($this->code)){
			return false;	
		}
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;		
		}
		if(!$this->check_str_key_alnum_underscore($cod)){
			return false;		
		}
		if(!$cod=basename($cod)){
			return false;		
		}
		$this->code=$cod;
		$this->after_code_ok();
		
	}

	/**
	 * Sets the main interface reference for this subinterface.
	 *
	 * Also sets the main application reference.
	 *
	 * @param mwmod_mw_ui_main_def $maininterface The main interface instance.
	 *
	 * @return void
	 */
	final function set_main_interface($maininterface){
		$ap=$maininterface->mainap;
		$this->set_mainap($ap);	
		$this->maininterface=$maininterface;
	}
	
	
	/**
	 * Gets the menu icon for this subinterface.
	 *
	 * @return string|false The menu icon identifier or false.
	 */
	function get_mnu_icon(){
		return $this->mnu_icon;	
	}

	/**
	 * Internal accessor for the code.
	 *
	 * @internal
	 * @return string The subinterface code.
	 */
	final function __get_priv_code(){
		return $this->code; 	
	}

	/**
	* Gets the main interface reference.
	*
	* @internal
	* @return mwmod_mw_ui_main_def MainUI instance.
	*/
	final function __get_priv_maininterface(){
		return $this->maininterface; 	
	}

	//current item

	/**
	 * Sets the current item for this subinterface.
	 *
	 * @param mixed $item The item to set as current.
	 *
	 * @return void
	 */
	final function set_current_item($item){
		$this->current_item=$item;	
	
	}
	/**
	 * Returns the currently selected item for this UI subinterface.
	 *
	 * This is typically used in item-editing interfaces where an item has been selected or loaded.
	 *
	 * @return mwmod_mw_manager_item|null The current item object if set, or null otherwise.
	 */
	final function get_current_item(){
		return $this->current_item; 	
	}

	/**
	 * Internal accessor for the current item.
	 *
	 * @internal
	 * @return mixed The current item or null.
	 */
	final function __get_priv_current_item(){
		return $this->current_item; 	
	}

	/**
	 * Magic method for handling undefined method calls.
	 *
	 * Returns false for any undefined method.
	 *
	 * @param string $a Method name.
	 * @param array $b Method arguments.
	 *
	 * @return false Always returns false.
	 */
	function __call($a,$b){
		return false;	
	}


	function execfrommain_getcmd_json($cmdcod,$params=array(),$filename=false){
		// Iniciar buffer para evitar basura
		ob_end_clean();
		ob_start();

		$this->before_exec_get_cmd($params);

		// validar comando
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			return $this->json_output_error("Invalid command");
		}

		$method="execfrommain_getcmd_json_$cmdcod";

		if(!method_exists($this,$method)){
			return $this->json_output_error("Method $method does not exist on ".get_class($this));
		}

		return $this->$method($params,$filename);
	}
	function json_output_error($msg){
		
		ob_end_clean();
		

		header("Content-Type: application/json; charset=UTF-8");
		header("HTTP/1.1 403 Forbidden");
		echo json_encode(array(
			"ok" => false,
			"msg" => $msg
		));
		return false;
	}
	function json_output_data($data=array()){
		ob_end_clean();
		

		header("Content-Type: application/json; charset=UTF-8");
		if($this->jsonPrettyPrint){
			echo json_encode($data,JSON_PRETTY_PRINT);
			return true;
		}
		echo json_encode($data);
		return true;
	}
	function get_exec_cmd_json_url($jsoncmd="debug", $params=array()){
		if($this->maininterface){
			// hereda parámetros igual que el SXML
			$params = $this->get_cmd_params($params);
			$ui_full_cod = $this->get_full_cod("-");
			return $this->maininterface->get_exec_cmd_json_url_from_ui_full_cod($jsoncmd, $ui_full_cod, $params);
		}
	}
	
}
?>