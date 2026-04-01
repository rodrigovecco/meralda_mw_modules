<?php
/**
 * UI2 Login Interface
 * 
 * Modern login UI using JS inputs system with floating labels.
 * 
 * @todo WIP - EN PAUSA - Requiere revisión de la integración con JS inputs modernos
 */
class mwmod_mw_ui2_sub_uilogin extends mwmod_mw_uitemplates_sbadmin_sub_uilogin {
	
	function __construct($cod, $maininterface) {
		parent::__construct($cod, $maininterface);
		$this->js_ui_class_name = "mw_ui_login2";
	}
	
	/**
	 * Prepare UI - load modern inputs JS
	 */
	function prepare_before_exec_no_sub_interface() {
		// Preparers básicos
		$util = new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		
		$p = new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
		$uiutil = new mwmod_mw_html_manager_uipreparers_ui($this);
		$uiutil->preapare_ui();
		
		// JS base de UI
		$jsman = $this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_login.js");
		
		// JS moderno de inputs
		$jsman->add_item_by_cod("/res/js/inputs/inputs.js");
		$jsman->add_item_by_cod("/res/js/inputs/container.js");
		$jsman->add_item_by_cod("/res/js/inputs/frm.js");
		$jsman->add_item_by_cod("/res/js/validator.js");
		$jsman->add_item_by_cod("/res/js/ui/mwui_login2.js");
		
		$item = $this->create_js_man_ui_header_declaration_item();
		$uiutil->add_js_item($item);
	}
	
	/**
	 * Get login form HTML with modern JS inputs
	 */
	function get_login_frm_html() {
		if (!$msg_man = $this->mainap->get_msgs_man_common()) {
			return false;
		}
		
		$userman = $this->maininterface->get_admin_user_manager();
		
		// Create form container
		$container = new mwmod_mw_bootstrap_html_elem("div");
		$container->addClass("login-form-container");
		
		// Form element
		$frm = new mwmod_mw_bootstrap_html_elem("form");
		$frm->set_att("method", "post");
		$frm->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("loginform"));
		
		if ($this->login_direct_mode) {
			$frm->set_att("action", $this->get_exec_cmd_dl_url("logindirect", array(), "login.html"));
		} else {
			$frm->set_att("action", $this->get_exec_cmd_dl_url("login", array(), "login.html"));
			$frm->set_att("target", $this->get_ui_elem_id_and_set_js_init_param("iframe"));
		}
		
		// Inputs container
		$inputsDiv = new mwmod_mw_bootstrap_html_elem("div");
		$inputsDiv->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("inputs"));
		$frm->add_cont($inputsDiv);
		
		// Hidden token field if needed
		if ($userman->login_session_token_enabled()) {
			$tokenInput = new mwmod_mw_bootstrap_html_elem("input");
			$tokenInput->set_att("type", "hidden");
			$tokenInput->set_att("name", "login_token");
			$tokenInput->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("token"));
			$frm->add_cont($tokenInput);
		}
		
		// Submit button
		$submitDiv = new mwmod_mw_bootstrap_html_elem("div");
		$submitDiv->addClass("d-grid gap-2 mt-3");
		$submitBtn = new mwmod_mw_bootstrap_html_elem("button");
		$submitBtn->set_att("type", "submit");
		$submitBtn->addClass("btn btn-primary btn-lg");
		$submitBtn->add_cont($msg_man->get_msg_txt("login", "Iniciar sesión"));
		$submitDiv->add_cont($submitBtn);
		$frm->add_cont($submitDiv);
		
		$container->add_cont($frm);
		
		return $container->get_as_html();
	}
	
	/**
	 * Execute page - adds modern inputs JS initialization
	 */
	function do_exec_page_in() {
		parent::do_exec_page_in();
		$this->output_modern_inputs_init_js();
	}
	
	/**
	 * Output JS initialization for modern inputs
	 */
	function output_modern_inputs_init_js() {
		$userman = $this->maininterface->get_admin_user_manager();
		$var = $this->get_js_ui_man_name();
		
		$js = new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont("(function() {\n");
		$js->add_cont("  var inputsman = new mw_datainput_item_frmonpanel();\n");
		$js->add_cont("  inputsman.setTitleMode('floating');\n");
		$js->add_cont("  inputsman.setPanel(document.getElementById('" . $this->get_ui_elem_id("inputs") . "'));\n");
		
		// Username input
		$js->add_cont("  var userInput = inputsman.addNewChild('login_userid', 'input');\n");
		$js->add_cont("  userInput.setTitle('" . addslashes($this->lng_common_get_msg_txt("user", "Usuario")) . "');\n");
		$js->add_cont("  userInput.setRequired(true);\n");
		$js->add_cont("  inputsman.addValidation2List(userInput, 'required', '" . addslashes($this->lng_common_get_msg_txt("required_field", "Campo requerido")) . "');\n");
		
		// Password input
		$js->add_cont("  var passInput = inputsman.addNewChild('login_pass', 'password');\n");
		$js->add_cont("  passInput.setTitle('" . addslashes($this->lng_common_get_msg_txt("password", "Contraseña")) . "');\n");
		$js->add_cont("  passInput.setRequired(true);\n");
		$js->add_cont("  inputsman.addValidation2List(passInput, 'required', '" . addslashes($this->lng_common_get_msg_txt("required_field", "Campo requerido")) . "');\n");
		
		$js->add_cont("  inputsman.render();\n");
		
		// Set inputsman on login UI object using mw_ui_login2 methods
		$js->add_cont("  {$var}.set_inputsman(inputsman);\n");
		
		// Set form reference
		$js->add_cont("  var form = document.getElementById('" . $this->get_ui_elem_id("loginform") . "');\n");
		$js->add_cont("  if (form) {\n");
		$js->add_cont("    {$var}.set_frm(form);\n");
		$js->add_cont("  }\n");
		
		$js->add_cont("})();\n");
		
		echo $js->get_js_script_html();
	}
	
	/**
	 * Allow login during forced security action (obviously)
	 */
	function isAllowedDuringForcedSecurityAction() {
		return true;
	}
	
	function is_single_mode() {
		return true;
	}
	
	function is_allowed() {
		return true;
	}
}
?>
