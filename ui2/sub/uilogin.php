<?php
/**
 * UI2 Login Interface
 * 
 * Modern login UI with floating labels using native HTML.
 * Standalone implementation - does not extend legacy sbadmin login.
 */
class mwmod_mw_ui2_sub_uilogin extends mwmod_mw_uitemplates_sbadmin_sub_abs {
	
	/** @var bool Enable direct login mode (no iframe) */
	var $login_direct_mode = false;
	
	/** @var bool Allow direct mode option */
	var $allow_direct_mode = false;
	
	function __construct($cod, $maininterface) {
		$this->init($cod, $maininterface);
		if ($msg_man = $this->mainap->get_msgs_man_common()) {
			$this->set_def_title($msg_man->get_msg_txt("login", "Iniciar sesión"));
		}
		$this->js_ui_class_name = "mw_ui_login2";
	}
	
	// =========================================================================
	// Permissions
	// =========================================================================
	
	function is_allowed_for_get_cmd_no_user() {
		return true;
	}
	
	function is_allowed_for_get_cmd($sub_ui_cods = false, $params = array(), $filename = false) {
		return true;
	}
	
	function is_allowed() {
		return true;
	}
	
	function is_single_mode() {
		return true;
	}
	
	function isAllowedDuringForcedSecurityAction() {
		return true;
	}
	
	// =========================================================================
	// Download commands (AJAX endpoints)
	// =========================================================================
	
	/**
	 * Direct login - redirects instead of using iframe
	 */
	function execfrommain_getcmd_dl_logindirect($params = array(), $filename = false) {
		if (!$this->allow_direct_mode) {
			echo "Not allowed";
			return;
		}
		$this->maininterface->exec_login_and_user_validation();
		if (!$user = $this->get_current_user()) {
			$url = $this->maininterface->get_url(array("dm" => "true"));
			ob_end_clean();
			header("Location: $url");
		} else {
			$url = $this->get_on_ok_url();
			ob_end_clean();
			header("Location: $url");
		}
	}
	
	/**
	 * Get login session token for CSRF protection
	 */
	function execfrommain_getcmd_dl_logintoken($params = array(), $filename = false) {
		$xml = $this->new_getcmd_sxml_answer(false);
		$userman = $this->maininterface->get_admin_user_manager();
		if (!$userman->login_session_token_enabled()) {
			$xml->root_do_all_output();
			return;
		}
		sleep(1);
		$xml->set_prop("ok", true);
		$xml->set_prop("chiwawa", $userman->get_login_session_token());
		$xml->root_do_all_output();
	}
	
	/**
	 * Process login POST (renders response for iframe)
	 */
	function execfrommain_getcmd_dl_login($params = array(), $filename = false) {
		$html = new mwmod_mw_html_cont_fulldoc();
		$html->body->add_cont("");
		$paramsjs = new mwmod_mw_jsobj_obj();
		$this->maininterface->exec_login_and_user_validation();
		$userman = $this->maininterface->get_admin_user_manager();
		
		if (!$user = $this->get_current_user()) {
			if ($msg = $this->get_login_fail_msg()) {
				$alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg, "danger");
				$paramsjs->set_prop("msg", $alert->get_ui_devexpress_toast_options());
			}
			$paramsjs->set_prop("ok", false);
		} else {
			$paramsjs->set_prop("ok", true);
		}
		$paramsjs->set_prop("result", $userman->login_js_response);
		//$paramsjs->set_prop("debug", $_REQUEST);
		
		$js = new mwmod_mw_jsobj_codecontainer();
		$var = $this->get_js_ui_man_name();
		$js->add_cont("window.parent." . $var . ".on_post_response(" . $paramsjs->get_as_js_val() . ");\n");
		$html->body->add_cont($js->get_js_script_html());
		$html->do_output();
	}
	
	// =========================================================================
	// UI Preparation
	// =========================================================================
	
	/**
	 * Prepare UI - load JS
	 */
	function prepare_before_exec_no_sub_interface() {
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
		$jsman->add_item_by_cod("/res/js/ui/mwui_login2.js");
		
		$item = $this->create_js_man_ui_header_declaration_item();
		$uiutil->add_js_item($item);
	}
	
	// =========================================================================
	// Page rendering
	// =========================================================================
	
	function get_on_ok_url() {
		return $this->maininterface->get_url();
	}
	
	function get_login_fail_msg() {
		if (!$man = $this->maininterface->get_admin_user_manager()) {
			return false;
		}
		return $man->login_fail_msg;
	}
	
	/**
	 * Render login page
	 */
	function do_exec_page_in() {
		$userman = $this->maininterface->get_admin_user_manager();
		$this->login_direct_mode = false;
		if ($this->allow_direct_mode) {
			if (($_REQUEST["dm"] ?? "") === "true") {
				$this->login_direct_mode = true;
			}
		}
		
		if (!$msg_man = $this->mainap->get_msgs_man_common()) {
			return false;
		}
		
		$maincontainer = $this->get_ui_dom_elem_container_empty();
		
		// Wrapper sin grid
		$wrapper = new mwmod_mw_html_elem("div");
		$wrapper->addClass("auth-form-wrapper");
		
		// Card panel
		$panel = new mwmod_mw_bootstrap_html_def("card card-default shadow-lg rounded-lg mt-5");
		$wrapper->add_cont($panel);
		
		// Card header
		$panel_head = new mwmod_mw_bootstrap_html_def("card-header");
		$panel->add_cont($panel_head);
		
		$fixcontent = new mwmod_mw_data_fixcontent_item("login/panelhead.html");
		if ($fixcontentHtml = $fixcontent->getContentHTML()) {
			$panel_head->add_cont($fixcontentHtml);
		} else {
			$panel_title = new mwmod_mw_bootstrap_html_def("card-title", "h4");
			$panel_title->add_cont($msg_man->get_msg_txt("please_login", "Por favor, iniciar sesión"));
			$panel_head->add_cont($panel_title);
		}
		
		// Card body
		$panel_body = new mwmod_mw_bootstrap_html_def("card-body");
		$panel->add_cont($panel_body);
		
		// Form container (initially hidden)
		$frmcontainer = $this->set_ui_dom_elem_id("loginfrm");
		$frmcontainer->add_cont($this->get_login_frm_html($userman));
		
		if ($this->allow_direct_mode) {
			if ($this->login_direct_mode) {
				$frmcontainer->add_cont("<p>" . $msg_man->get_msg_txt("direct_mode", "Modo directo") . ".</p>");
			} else {
				$urldirect = "index.php?dm=true";
				$frmcontainer->add_cont("<p style='display:none'><a href='$urldirect'>" . $msg_man->get_msg_txt("direct_mode", "Modo directo") . ".</a></p>");
			}
		}
		
		$frmcontainer->set_style("display", "none");
		$panel_body->add_cont($frmcontainer);
		
		// Wait container (for timeout display)
		$waitcontainer = $this->set_ui_dom_elem_id("wait");
		$waitcontainer->set_style("display", "none");
		$waitcontainer->set_style("padding-top", "3px");
		$panel_body->add_cont($waitcontainer);
		
		// Other info (remember password link)
		$subpanelbody = $panel_body->add_cont_elem();
		$subpanelbody->addClass("login-other-info");
		
		if ($uiremember = $this->maininterface->get_subinterface("rememberlogindata")) {
			if ($uiremember->is_rememberlogindata()) {
				if ($uiremember->is_enabled()) {
					if ($html = $uiremember->get_html_link_on_login()) {
						$alert = new mwmod_mw_bootstrap_html_elem("div", false, $html);
						$subpanelbody->add_cont($alert);
					}
				}
			}
		}
		
		$maincontainer->add_cont($wrapper);
		
		// Iframe for form submission
		$iframaandfrm = $this->create_ui_dom_elem_iframe_and_frm_container();
		$maincontainer->add_cont($iframaandfrm);
		
		echo $maincontainer->get_as_html();
		
		// JS initialization
		$this->output_ui_init_js($userman);
	}
	
	/**
	 * Get login form HTML using native Bootstrap elements
	 * @param object $userman User manager
	 * @return string HTML
	 */
	function get_login_frm_html($userman) {
		// Form element
		$frm = new mwmod_mw_bootstrap_html_elem("form");
		$frm->set_att("method", "post");
		$frm->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("loginform"));
		$frm->set_att("autocomplete", "off");
		
		if ($this->login_direct_mode) {
			$frm->set_att("action", $this->get_exec_cmd_dl_url("logindirect", array(), "login.html"));
		} else {
			$frm->set_att("action", $this->get_exec_cmd_dl_url("login", array(), "login.html"));
			$frm->set_att("target", $this->get_ui_elem_id_and_set_js_init_param("iframe"));
		}
		
		// Username floating label input
		$userGroup = new mwmod_mw_bootstrap_html_elem("div");
		$userGroup->addClass("form-floating mb-3");
		
		$userInput = new mwmod_mw_bootstrap_html_elem("input");
		$userInput->set_att("type", "text");
		$userInput->set_att("name", "login_userid");
		$userInput->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("input_user"));
		$userInput->set_att("placeholder", $this->lng_common_get_msg_txt("user", "Usuario"));
		$userInput->set_att("required", "required");
		$userInput->set_att("autocomplete", "username");
		$userInput->addClass("form-control");
		$userGroup->add_cont($userInput);
		
		$userLabel = new mwmod_mw_bootstrap_html_elem("label");
		$userLabel->set_att("for", $this->get_ui_elem_id("input_user"));
		$userLabel->add_cont($this->lng_common_get_msg_txt("user", "Usuario"));
		$userGroup->add_cont($userLabel);
		
		$frm->add_cont($userGroup);
		
		// Password floating label input
		$passGroup = new mwmod_mw_bootstrap_html_elem("div");
		$passGroup->addClass("form-floating mb-3");
		
		$passInput = new mwmod_mw_bootstrap_html_elem("input");
		$passInput->set_att("type", "password");
		$passInput->set_att("name", "login_pass");
		$passInput->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("input_pass"));
		$passInput->set_att("placeholder", $this->lng_common_get_msg_txt("password", "Contraseña"));
		$passInput->set_att("required", "required");
		$passInput->set_att("autocomplete", "current-password");
		$passInput->addClass("form-control");
		$passGroup->add_cont($passInput);
		
		$passLabel = new mwmod_mw_bootstrap_html_elem("label");
		$passLabel->set_att("for", $this->get_ui_elem_id("input_pass"));
		$passLabel->add_cont($this->lng_common_get_msg_txt("password", "Contraseña"));
		$passGroup->add_cont($passLabel);
		
		$frm->add_cont($passGroup);
		
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
		$submitDiv->addClass("d-grid gap-2");
		
		$submitBtn = new mwmod_mw_bootstrap_html_elem("button");
		$submitBtn->set_att("type", "submit");
		$submitBtn->addClass("btn btn-primary btn-lg");
		$submitBtn->add_cont($this->lng_common_get_msg_txt("login", "Iniciar sesión"));
		$submitDiv->add_cont($submitBtn);
		
		$frm->add_cont($submitDiv);
		
		return $frm->get_as_html();
	}
	
	/**
	 * Output main UI init JS
	 */
	function output_ui_init_js($userman) {
		$js = new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		
		$url = $_SERVER['REQUEST_URI'] ?? $this->get_on_ok_url();
		$this->ui_js_init_params->set_prop("onokurl", $url);
		$this->ui_js_init_params->set_prop("please_wait", $this->lng_common_get_msg_txt("please_wait", "Por favor, espere"));
		$this->ui_js_init_params->set_prop("seconds", $this->lng_common_get_msg_txt("seconds_lc", "segundos"));
		
		// Token expired messages
		$this->ui_js_init_params->set_prop("token_expired_msg", $this->lng_common_get_msg_txt("session_expired_reload", "La sesión ha expirado. Por favor, recarga la página."));
		$this->ui_js_init_params->set_prop("reload_btn_text", $this->lng_common_get_msg_txt("reload_page", "Recargar página"));
		
		if ($userman->login_session_token_enabled()) {
			$this->ui_js_init_params->set_prop("requestTokenMode", true);
		}
		
		$var = $this->get_js_ui_man_name();
		$js->add_cont($var . ".init(" . $this->ui_js_init_params->get_as_js_val() . ");\n");
		
		echo $js->get_js_script_html();
	}
}
?>
