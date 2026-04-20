<?php
/**
 * UI2 Remember Login Data Interface
 * 
 * Modern version of password reset UI using native HTML Bootstrap elements.
 * Supports both password reset request (by email) and password change (with token).
 * Uses floating labels - no legacy JS inputs system.
 */
class mwmod_mw_ui2_sub_rememberlogindata extends mwmod_mw_subui_rememberlogindata {
	
	/** @var string JS class name for UI manager */
	var $js_ui_class_name = "mw_ui_rememberlogin";
	
	/**
	 * Prepare UI - load custom JS (no legacy inputs system)
	 */
	function prepare_before_exec_no_sub_interface() {
		$p = new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
		
		$jsman = $this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("util.js");
		$jsman->add_item_by_cod("/res/js/ui/mwui_rememberlogin.js");
		
		$item = $this->create_js_man_ui_header_declaration_item();
		$jsman->add_item_by_item($item);
	}
	
	// =========================================================================
	// Request Form (Email) - Native HTML
	// =========================================================================
	
	/**
	 * Request form - email input with optional captcha
	 * @return string HTML
	 */
	function get_request_frm_html() {
		$uman = $this->get_related_user_man();
		if (!$uman) return false;
		$container=$this->get_ui_dom_elem_container();
		
		
		// Form element
		$frm = new mwmod_mw_bootstrap_html_elem("form");
		$container->add_cont($frm);
		$frm->set_att("method", "post");
		$frm->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("form"));
		$frm->set_att("novalidate", "novalidate");
		
		// Email floating label input
		$emailGroup = new mwmod_mw_bootstrap_html_elem("div");
		$emailGroup->addClass("form-floating mb-3");
		
		$emailInput = new mwmod_mw_bootstrap_html_elem("input");
		$emailInput->set_att("type", "email");
		$emailInput->set_att("name", "reqdata[user_email]");
		$emailInput->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("input_email"));
		$emailInput->set_att("placeholder", $this->lng_get_msg_txt("email", "Correo electrónico"));
		$emailInput->set_att("required", "required");
		$emailInput->set_att("autocomplete", "email");
		$emailInput->addClass("form-control");
		if ($this->email) {
			$emailInput->set_att("value", htmlspecialchars($this->email));
		}
		$emailGroup->add_cont($emailInput);
		
		$emailLabel = new mwmod_mw_bootstrap_html_elem("label");
		$emailLabel->set_att("for", $this->get_ui_elem_id("input_email"));
		$emailLabel->add_cont($this->lng_get_msg_txt("email", "Correo electrónico"));
		$emailGroup->add_cont($emailLabel);
		
		$frm->add_cont($emailGroup);
		
		// Captcha
		if ($this->use_captcha) {
			$captchaHtml = $this->get_captcha_html("reqdata");
			$frm->add_cont($captchaHtml);
		}
		
		// Submit button
		$submitDiv = new mwmod_mw_bootstrap_html_elem("div");
		$submitDiv->addClass("d-grid gap-2 mt-3");
		
		$submitBtn = new mwmod_mw_bootstrap_html_elem("button");
		$submitBtn->set_att("type", "submit");
		$submitBtn->addClass("btn btn-success btn-lg");
		$submitBtn->add_cont($this->lng_get_msg_txt("send", "Enviar"));
		$submitDiv->add_cont($submitBtn);
		
		$frm->add_cont($submitDiv);
		
		return $container->get_as_html();
	}
	
	// =========================================================================
	// Reset Password Form - Native HTML
	// =========================================================================
	
	/**
	 * Reset password form - username, token, new password
	 * @return string HTML
	 */
	function get_resetpass_frm_html() {
		$uman = $this->get_related_user_man();
		if (!$uman) return false;
		
		$pass_policy = $uman->get_pass_policy();
		if (!$pass_policy) return false;
		
		$can_change = $this->can_change_password();
		
		// Form element
		$frm = new mwmod_mw_bootstrap_html_elem("form");
		$frm->set_att("method", "post");
		$frm->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("form"));
		$frm->set_att("novalidate", "novalidate");
		
		// Username floating label input
		$userGroup = new mwmod_mw_bootstrap_html_elem("div");
		$userGroup->addClass("form-floating mb-3");
		
		$userInput = new mwmod_mw_bootstrap_html_elem("input");
		$userInput->set_att("type", "text");
		$userInput->set_att("name", "resetpassdata[u][uname]");
		$userInput->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("input_user"));
		$userInput->set_att("placeholder", $this->lng_get_msg_txt("user", "Usuario"));
		$userInput->set_att("required", "required");
		$userInput->set_att("autocomplete", "username");
		$userInput->addClass("form-control");
		if ($uname = $_REQUEST["uname"] ?? null) {
			$userInput->set_att("value", htmlspecialchars($uname));
		}
		$userGroup->add_cont($userInput);
		
		$userLabel = new mwmod_mw_bootstrap_html_elem("label");
		$userLabel->set_att("for", $this->get_ui_elem_id("input_user"));
		$userLabel->add_cont($this->lng_get_msg_txt("user", "Usuario"));
		$userGroup->add_cont($userLabel);
		
		$frm->add_cont($userGroup);
		
		// Token floating label input
		$tokenGroup = new mwmod_mw_bootstrap_html_elem("div");
		$tokenGroup->addClass("form-floating mb-3");
		
		$tokenInput = new mwmod_mw_bootstrap_html_elem("input");
		$tokenInput->set_att("type", "text");
		$tokenInput->set_att("name", "resetpassdata[u][rptoken]");
		$tokenInput->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("input_token"));
		$tokenInput->set_att("placeholder", $this->lng_get_msg_txt("reset_pass_code", "Código de reestablecimiento"));
		$tokenInput->set_att("required", "required");
		$tokenInput->addClass("form-control");
		if ($rptoken = $_REQUEST["rptoken"] ?? null) {
			$tokenInput->set_att("value", htmlspecialchars($rptoken));
		}
		$tokenGroup->add_cont($tokenInput);
		
		$tokenLabel = new mwmod_mw_bootstrap_html_elem("label");
		$tokenLabel->set_att("for", $this->get_ui_elem_id("input_token"));
		$tokenLabel->add_cont($this->lng_get_msg_txt("reset_pass_code", "Código de reestablecimiento"));
		$tokenGroup->add_cont($tokenLabel);
		
		$frm->add_cont($tokenGroup);
		
		// Password fields if can change password
		if ($can_change) {
			// New password
			$passGroup = new mwmod_mw_bootstrap_html_elem("div");
			$passGroup->addClass("form-floating mb-3");
			
			$passInput = new mwmod_mw_bootstrap_html_elem("input");
			$passInput->set_att("type", "password");
			$passInput->set_att("name", "resetpassdata[u][pass][pass]");
			$passInput->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("input_pass"));
			$passInput->set_att("placeholder", $this->lng_get_msg_txt("new_password", "Nueva contraseña"));
			$passInput->set_att("required", "required");
			$passInput->set_att("autocomplete", "new-password");
			$passInput->addClass("form-control");
			$passGroup->add_cont($passInput);
			
			$passLabel = new mwmod_mw_bootstrap_html_elem("label");
			$passLabel->set_att("for", $this->get_ui_elem_id("input_pass"));
			$passLabel->add_cont($this->lng_get_msg_txt("new_password", "Nueva contraseña"));
			$passGroup->add_cont($passLabel);
			
			$frm->add_cont($passGroup);
			
			// Confirm password
			$passConfirmGroup = new mwmod_mw_bootstrap_html_elem("div");
			$passConfirmGroup->addClass("form-floating mb-3");
			
			$passConfirmInput = new mwmod_mw_bootstrap_html_elem("input");
			$passConfirmInput->set_att("type", "password");
			$passConfirmInput->set_att("name", "resetpassdata[u][pass][pass1]");
			$passConfirmInput->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("input_pass_confirm"));
			$passConfirmInput->set_att("placeholder", $this->lng_get_msg_txt("confirm_password", "Confirmar contraseña"));
			$passConfirmInput->set_att("required", "required");
			$passConfirmInput->set_att("autocomplete", "new-password");
			$passConfirmInput->addClass("form-control");
			$passConfirmGroup->add_cont($passConfirmInput);
			
			$passConfirmLabel = new mwmod_mw_bootstrap_html_elem("label");
			$passConfirmLabel->set_att("for", $this->get_ui_elem_id("input_pass_confirm"));
			$passConfirmLabel->add_cont($this->lng_get_msg_txt("confirm_password", "Confirmar contraseña"));
			$passConfirmGroup->add_cont($passConfirmLabel);
			
			$frm->add_cont($passConfirmGroup);
		}
		
		// Captcha
		if ($this->use_captcha) {
			$captchaHtml = $this->get_captcha_html("resetpassdata");
			$frm->add_cont($captchaHtml);
		}
		
		// Submit button
		$submitDiv = new mwmod_mw_bootstrap_html_elem("div");
		$submitDiv->addClass("d-grid gap-2 mt-3");
		
		$submitBtn = new mwmod_mw_bootstrap_html_elem("button");
		$submitBtn->set_att("type", "submit");
		$submitBtn->addClass("btn btn-success btn-lg");
		$submitBtn->add_cont($can_change 
			? $this->lng_get_msg_txt("changepassword", "Cambiar contraseña")
			: $this->lng_get_msg_txt("send", "Enviar"));
		$submitDiv->add_cont($submitBtn);
		
		$frm->add_cont($submitDiv);
		
		return $frm->get_as_html();
	}
	
	// =========================================================================
	// Captcha Helper
	// =========================================================================
	
	/**
	 * Get captcha HTML - renders captcha image + input with floating label
	 * @param string $prefix - Input name prefix (reqdata or resetpassdata)
	 * @return string HTML
	 */
	function get_captcha_html($prefix) {
		// Get captcha manager
		if (!$man = $this->mainap->get_submanager("captcha")) {
			return "";
		}
		
		// Create captcha item
		if (!$captchaItem = $man->new_item_for_input("captcha")) {
			return "";
		}
		
		$inputName = $prefix . "[captcha]";
		$inputId = $this->get_ui_elem_id_and_set_js_init_param("input_captcha");
		$label = $this->lng_get_msg_txt("input_captcha_code", "Ingresa el código de verificación");
		
		// Build HTML with Bootstrap 5 input-group
		$html = '<div class="mb-3">';
		$html .= '<label for="' . $inputId . '" class="form-label">' . htmlspecialchars($label) . '</label>';
		$html .= '<div class="input-group">';
		$html .= '<span class="input-group-text p-0">' . $captchaItem->get_img_html() . '</span>';
		$html .= '<input type="text" class="form-control" name="' . $inputName . '" id="' . $inputId . '" required autocomplete="off">';
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;
	}
	
	// =========================================================================
	// Page Execution
	// =========================================================================
	
	/**
	 * Execute page - render layout with centered form and add JS init
	 * Overrides parent to use Bootstrap 5 centering (justify-content-center + mt-5)
	 */
	function do_exec_page_in() {
		$reset_pass_mode = false;
		//die("que pasa");
		if (mw_array_get_sub_key($_REQUEST, "action") == "resetpass") {
			$reset_pass_mode = true;
			$this->set_url_param("action", "resetpass");
		}
		
		if (!$msg_man = $this->mainap->get_msgs_man_common()) {
			return false;
		}
		
		// Wrapper div
		$wrapper = new mwmod_mw_html_elem("div");
		$wrapper->addClass("auth-form-wrapper");
		
		// Card
		$card = new mwmod_mw_html_elem("div");
		$card->addClass("card card-default shadow-lg rounded-lg mt-5");
		$wrapper->add_cont($card);
		
		// Card header
		$cardHeader = new mwmod_mw_html_elem("div");
		$cardHeader->addClass("card-header");
		$card->add_cont($cardHeader);
		
		$cardTitle = new mwmod_mw_html_elem("h4");
		$cardTitle->addClass("card-title");
		if ($reset_pass_mode) {
			$cardTitle->add_cont($this->lng_get_msg_txt("reset_password", "Restablecer contraseña"));
		} else {
			$cardTitle->add_cont($this->lng_get_msg_txt("rememberlogindata", "Recuperar datos de acceso"));
		}
		$cardHeader->add_cont($cardTitle);
		
		// Card body
		$cardBody = new mwmod_mw_html_elem("div");
		$cardBody->addClass("card-body");
		$card->add_cont($cardBody);
		
		$this->panel_body = $cardBody;
		$this->alert_fail = new mwmod_mw_bootstrap_html_specialelem_alert(false, "danger");
		$this->alert_fail->only_visible_when_has_cont = true;
		$this->alert_fail->addClass("mt-3");
		
		if ($this->is_enabled()) {
			if ($reset_pass_mode) {
				$this->do_actions_reset_pass();
			} else {
				$this->do_actions();
			}
		} else {
			$alert = new mwmod_mw_bootstrap_html_specialelem_alert(
				$this->lng_get_msg_txt("this_funtion_is_disabled", "Esta función está deshabilitada"), 
				"danger"
			);
			$cardBody->add_cont($alert);
		}
		
		// Wrap with authentication layout
		$authLayout = new mwmod_mw_html_elem("div");
		$authLayout->set_att("id", "layoutAuthentication");
		
		$authContent = $authLayout->add_cont_elem();
		$authContent->set_att("id", "layoutAuthentication_content");
		$authContent->add_cont($wrapper);
		
		echo $authLayout->get_as_html();
		
		// JS init
		$this->output_js_init();
	}
	
	/**
	 * Output JS initialization
	 */
	function output_js_init() {
		$reset_pass_mode = (mw_array_get_sub_key($_REQUEST, "action") == "resetpass");
		$can_change = $this->can_change_password();
		
		$this->set_ui_js_params();
		
		// Mode
		$this->ui_js_init_params->set_prop("mode", $reset_pass_mode ? "reset" : "request");
		
		// Messages
		$this->ui_js_init_params->set_prop("msg_required", $this->lng_common_get_msg_txt("required_field", "Campo requerido"));
		$this->ui_js_init_params->set_prop("msg_invalid_email", $this->lng_get_msg_txt("invalid_email", "Correo electrónico inválido"));
		$this->ui_js_init_params->set_prop("msg_passwords_dont_match", $this->lng_get_msg_txt("passwords_dont_match", "Las contraseñas no coinciden"));
		
		// Password policy
		if ($reset_pass_mode && $can_change) {
			$this->add_password_policy_params();
		}
		
		// JS init
		$js = new mwmod_mw_jsobj_jquery_docreadyfnc();
		$var = $this->get_js_ui_man_name();
		$js->add_cont($var . ".init(" . $this->ui_js_init_params->get_as_js_val() . ");\n");
		
		echo $js->get_js_script_html();
	}
	
	/**
	 * Add password policy parameters to JS init
	 */
	function add_password_policy_params() {
		$uman = $this->get_related_user_man();
		if (!$uman) return;
		
		$pass_policy = $uman->get_pass_policy();
		if (!$pass_policy) return;
		
		if ($minLen = $pass_policy->min_length ?? 0) {
			$this->ui_js_init_params->set_prop("pass_min_length", $minLen);
			$this->ui_js_init_params->set_prop("msg_pass_min_length", 
				$this->lng_get_msg_txt("password_min_length", "La contraseña debe tener al menos $minLen caracteres"));
		}
		
		if ($pass_policy->must_contain_uppers ?? false) {
			$this->ui_js_init_params->set_prop("pass_require_uppercase", true);
			$this->ui_js_init_params->set_prop("msg_pass_require_uppercase", 
				$this->lng_get_msg_txt("password_require_uppercase", "La contraseña debe contener al menos una mayúscula"));
		}
		
		if ($pass_policy->must_contain_lowers ?? false) {
			$this->ui_js_init_params->set_prop("pass_require_lowercase", true);
			$this->ui_js_init_params->set_prop("msg_pass_require_lowercase", 
				$this->lng_get_msg_txt("password_require_lowercase", "La contraseña debe contener al menos una minúscula"));
		}
		
		if ($pass_policy->must_contain_numbers ?? false) {
			$this->ui_js_init_params->set_prop("pass_require_number", true);
			$this->ui_js_init_params->set_prop("msg_pass_require_number", 
				$this->lng_get_msg_txt("password_require_number", "La contraseña debe contener al menos un número"));
		}
		
		if ($pass_policy->must_contain_specials ?? false) {
			$this->ui_js_init_params->set_prop("pass_require_special", true);
			$this->ui_js_init_params->set_prop("msg_pass_require_special", 
				$this->lng_get_msg_txt("password_require_special", "La contraseña debe contener al menos un carácter especial"));
		}
	}
	
	/**
	 * Use single mode layout (no sidebar, authentication layout)
	 */
	function is_single_mode() {
		return true;
	}
	
	/**
	 * Allow during forced security action
	 */
	function isAllowedDuringForcedSecurityAction() {
		return true;
	}
}
?>
