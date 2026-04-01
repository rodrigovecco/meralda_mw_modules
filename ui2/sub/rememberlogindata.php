<?php
/**
 * UI2 Remember Login Data Interface
 * 
 * Modern version of password reset UI using JS inputs system.
 * Supports both password reset request (by email) and password change (with token).
 * 
 * @todo WIP - EN PAUSA - Requiere revisión de la integración con JS inputs modernos
 */
class mwmod_mw_ui2_sub_rememberlogindata extends mwmod_mw_subui_rememberlogindata {
	
	function __construct($cod, $maininterface) {
		parent::__construct($cod, $maininterface);
	}
	
	/**
	 * Prepare UI - load modern inputs JS
	 */
	function prepare_before_exec_no_sub_interface() {
		$p = new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
		
		$jsman = $this->maininterface->jsmanager;
		$jsman->add_item_by_cod("/res/js/util.js");
		$jsman->add_item_by_cod("/res/js/ajax.js");
		$jsman->add_item_by_cod("/res/js/url.js");
		$jsman->add_item_by_cod("/res/js/inputs/inputs.js");
		$jsman->add_item_by_cod("/res/js/inputs/container.js");
		$jsman->add_item_by_cod("/res/js/inputs/frm.js");
		$jsman->add_item_by_cod("/res/js/validator.js");
		
		$item = $this->create_js_man_ui_header_declaration_item();
		$jsman->add_item_by_item($item);
	}
	
	/**
	 * Request form - email input with optional captcha
	 */
	function get_request_frm_html() {
		$uman = $this->get_related_user_man();
		if (!$uman) return false;
		
		$container = new mwmod_mw_bootstrap_html_elem("div");
		$container->addClass("remember-form-container");
		
		// Form element
		$frm = new mwmod_mw_bootstrap_html_elem("form");
		$frm->set_att("method", "post");
		$frm->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("requestform"));
		
		// Inputs wrapper
		$inputsWrapper = new mwmod_mw_bootstrap_html_elem("div");
		
		// Input container for JS inputs
		$inputsDiv = new mwmod_mw_bootstrap_html_elem("div");
		$inputsDiv->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("requestinputs"));
		$inputsWrapper->add_cont($inputsDiv);
		
		// Captcha container
		if ($this->use_captcha) {
			$captchaDiv = new mwmod_mw_bootstrap_html_elem("div");
			$captchaDiv->addClass("mt-3 captcha-container");
			$captchaDiv->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("requestcaptcha"));
			$inputsWrapper->add_cont($captchaDiv);
		}
		
		$frm->add_cont($inputsWrapper);
		
		// Submit button
		$submitDiv = new mwmod_mw_bootstrap_html_elem("div");
		$submitDiv->addClass("d-grid gap-2 mt-3");
		$submitBtn = new mwmod_mw_bootstrap_html_elem("button");
		$submitBtn->set_att("type", "submit");
		$submitBtn->addClass("btn btn-success btn-lg");
		$submitBtn->add_cont($this->lng_get_msg_txt("send", "Enviar"));
		$submitDiv->add_cont($submitBtn);
		$frm->add_cont($submitDiv);
		
		$container->add_cont($frm);
		
		return $container->get_as_html();
	}
	
	/**
	 * Reset password form - username, token, new password
	 */
	function get_resetpass_frm_html() {
		$uman = $this->get_related_user_man();
		if (!$uman) return false;
		
		$pass_policy = $uman->get_pass_policy();
		if (!$pass_policy) return false;
		
		$container = new mwmod_mw_bootstrap_html_elem("div");
		$container->addClass("resetpass-form-container");
		
		// Form element
		$frm = new mwmod_mw_bootstrap_html_elem("form");
		$frm->set_att("method", "post");
		$frm->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("resetform"));
		
		// Inputs wrapper
		$inputsWrapper = new mwmod_mw_bootstrap_html_elem("div");
		
		// Input container for JS inputs
		$inputsDiv = new mwmod_mw_bootstrap_html_elem("div");
		$inputsDiv->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("resetinputs"));
		$inputsWrapper->add_cont($inputsDiv);
		
		// Captcha container
		if ($this->use_captcha) {
			$captchaDiv = new mwmod_mw_bootstrap_html_elem("div");
			$captchaDiv->addClass("mt-3 captcha-container");
			$captchaDiv->set_att("id", $this->get_ui_elem_id_and_set_js_init_param("resetcaptcha"));
			$inputsWrapper->add_cont($captchaDiv);
		}
		
		$frm->add_cont($inputsWrapper);
		
		// Submit button
		$submitDiv = new mwmod_mw_bootstrap_html_elem("div");
		$submitDiv->addClass("d-grid gap-2 mt-3");
		$submitBtn = new mwmod_mw_bootstrap_html_elem("button");
		$submitBtn->set_att("type", "submit");
		$submitBtn->addClass("btn btn-success btn-lg");
		$submitBtn->add_cont($this->can_change_password() 
			? $this->lng_get_msg_txt("changepassword", "Cambiar contraseña")
			: $this->lng_get_msg_txt("send", "Enviar"));
		$submitDiv->add_cont($submitBtn);
		$frm->add_cont($submitDiv);
		
		$container->add_cont($frm);
		
		return $container->get_as_html();
	}
	
	/**
	 * Execute page - render layout and add modern inputs JS
	 */
	function do_exec_page_in() {
		parent::do_exec_page_in();
		$this->output_modern_inputs_init_js();
	}
	
	/**
	 * Output JS initialization for modern inputs
	 */
	function output_modern_inputs_init_js() {
		$reset_pass_mode = (mw_array_get_sub_key($_REQUEST, "action") == "resetpass");
		
		$js = new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont("(function() {\n");
		
		if ($reset_pass_mode) {
			$this->output_reset_form_inputs_js($js);
		} else {
			$this->output_request_form_inputs_js($js);
		}
		
		$js->add_cont("})();\n");
		
		echo $js->get_js_script_html();
		
		// Render captcha if needed
		if ($this->use_captcha) {
			$this->output_captcha_render_js($reset_pass_mode);
		}
	}
	
	/**
	 * JS for request form (email input)
	 */
	function output_request_form_inputs_js($js) {
		$inputsId = $this->get_ui_elem_id("requestinputs");
		$formId = $this->get_ui_elem_id("requestform");
		
		$js->add_cont("  var inputsman = new mw_datainput_item_frmonpanel();\n");
		$js->add_cont("  inputsman.setTitleMode('floating');\n");
		$js->add_cont("  inputsman.setPanel(document.getElementById('" . $inputsId . "'));\n");
		
		$js->add_cont("  var emailInput = inputsman.addNewChild('reqdata[user_email]', 'input');\n");
		$js->add_cont("  emailInput.setTitle('" . addslashes($this->lng_get_msg_txt("email", "Correo")) . "');\n");
		$js->add_cont("  emailInput.setRequired(true);\n");
		$js->add_cont("  inputsman.addValidation2List(emailInput, 'required', '" . addslashes($this->lng_common_get_msg_txt("required_field", "Campo requerido")) . "');\n");
		$js->add_cont("  inputsman.addValidation2List(emailInput, 'email', '" . addslashes($this->lng_get_msg_txt("invalid_email", "Correo electrónico inválido")) . "');\n");
		
		if ($this->email) {
			$js->add_cont("  emailInput.setValue('" . addslashes($this->email) . "');\n");
		}
		
		$js->add_cont("  inputsman.render();\n");
		$js->add_cont("  window._rememberInputsMan = inputsman;\n");
		
		$js->add_cont("  var form = document.getElementById('" . $formId . "');\n");
		$js->add_cont("  if (form) {\n");
		$js->add_cont("    form.addEventListener('submit', function(e) {\n");
		$js->add_cont("      if (!inputsman.validate()) { e.preventDefault(); return false; }\n");
		$js->add_cont("    });\n");
		$js->add_cont("  }\n");
	}
	
	/**
	 * JS for reset password form
	 */
	function output_reset_form_inputs_js($js) {
		$inputsId = $this->get_ui_elem_id("resetinputs");
		$formId = $this->get_ui_elem_id("resetform");
		$can_change = $this->can_change_password();
		
		$js->add_cont("  var inputsman = new mw_datainput_item_frmonpanel();\n");
		$js->add_cont("  inputsman.setTitleMode('floating');\n");
		$js->add_cont("  inputsman.setPanel(document.getElementById('" . $inputsId . "'));\n");
		
		$js->add_cont("  var grU = inputsman.addNewGr('resetpassdata[u]');\n");
		
		// Username
		$js->add_cont("  var unameInput = grU.addNewChild('uname', 'input');\n");
		$js->add_cont("  unameInput.setTitle('" . addslashes($this->lng_get_msg_txt("user", "Usuario")) . "');\n");
		$js->add_cont("  unameInput.setRequired(true);\n");
		$js->add_cont("  inputsman.addValidation2List(unameInput, 'required', '" . addslashes($this->lng_common_get_msg_txt("required_field", "Campo requerido")) . "');\n");
		
		if ($uname = $_REQUEST["uname"] ?? null) {
			$js->add_cont("  unameInput.setValue('" . addslashes($uname) . "');\n");
		}
		
		// Token
		$js->add_cont("  var tokenInput = grU.addNewChild('rptoken', 'input');\n");
		$js->add_cont("  tokenInput.setTitle('" . addslashes($this->lng_get_msg_txt("reset_pass_code", "Código de reestablecimiento de contraseña")) . "');\n");
		$js->add_cont("  tokenInput.setRequired(true);\n");
		$js->add_cont("  inputsman.addValidation2List(tokenInput, 'required', '" . addslashes($this->lng_common_get_msg_txt("required_field", "Campo requerido")) . "');\n");
		
		if ($rptoken = $_REQUEST["rptoken"] ?? null) {
			$js->add_cont("  tokenInput.setValue('" . addslashes($rptoken) . "');\n");
		}
		
		// Password fields
		if ($can_change) {
			$js->add_cont("  var grPass = grU.addNewGr('pass');\n");
			
			$js->add_cont("  var passInput = grPass.addNewChild('pass', 'password');\n");
			$js->add_cont("  passInput.setTitle('" . addslashes($this->lng_get_msg_txt("new_password", "Nueva contraseña")) . "');\n");
			$js->add_cont("  passInput.setRequired(true);\n");
			$js->add_cont("  inputsman.addValidation2List(passInput, 'required', '" . addslashes($this->lng_common_get_msg_txt("required_field", "Campo requerido")) . "');\n");
			
			$this->addPasswordPolicyValidationsJs($js, 'passInput');
			
			$js->add_cont("  var passConfirmInput = grPass.addNewChild('pass1', 'password');\n");
			$js->add_cont("  passConfirmInput.setTitle('" . addslashes($this->lng_get_msg_txt("confirm_password", "Confirmar contraseña")) . "');\n");
			$js->add_cont("  passConfirmInput.setRequired(true);\n");
			$js->add_cont("  inputsman.addValidation2List(passConfirmInput, 'required', '" . addslashes($this->lng_common_get_msg_txt("required_field", "Campo requerido")) . "');\n");
			$js->add_cont("  inputsman.addValidation2List(passConfirmInput, function() {\n");
			$js->add_cont("    return passInput.getValue() === passConfirmInput.getValue();\n");
			$js->add_cont("  }, '" . addslashes($this->lng_get_msg_txt("passwords_dont_match", "Las contraseñas no coinciden")) . "');\n");
		}
		
		$js->add_cont("  inputsman.render();\n");
		$js->add_cont("  window._resetInputsMan = inputsman;\n");
		
		$js->add_cont("  var form = document.getElementById('" . $formId . "');\n");
		$js->add_cont("  if (form) {\n");
		$js->add_cont("    form.addEventListener('submit', function(e) {\n");
		$js->add_cont("      if (!inputsman.validate()) { e.preventDefault(); return false; }\n");
		$js->add_cont("    });\n");
		$js->add_cont("  }\n");
	}
	
	/**
	 * Add password policy validations
	 */
	function addPasswordPolicyValidationsJs($js, $inputVarName) {
		$uman = $this->get_related_user_man();
		if (!$uman) return;
		
		$pass_policy = $uman->get_pass_policy();
		if (!$pass_policy) return;
		
		if ($minLen = $pass_policy->min_length ?? 0) {
			$js->add_cont("  inputsman.addValidation2List({$inputVarName}, 'minLength:{$minLen}', '" . 
				addslashes($this->lng_get_msg_txt("password_min_length", "La contraseña debe tener al menos {$minLen} caracteres")) . "');\n");
		}
		
		if ($pass_policy->require_uppercase ?? false) {
			$js->add_cont("  inputsman.addValidation2List({$inputVarName}, function() { return /[A-Z]/.test({$inputVarName}.getValue()); }, '" . 
				addslashes($this->lng_get_msg_txt("password_require_uppercase", "La contraseña debe contener al menos una mayúscula")) . "');\n");
		}
		
		if ($pass_policy->require_lowercase ?? false) {
			$js->add_cont("  inputsman.addValidation2List({$inputVarName}, function() { return /[a-z]/.test({$inputVarName}.getValue()); }, '" . 
				addslashes($this->lng_get_msg_txt("password_require_lowercase", "La contraseña debe contener al menos una minúscula")) . "');\n");
		}
		
		if ($pass_policy->require_number ?? false) {
			$js->add_cont("  inputsman.addValidation2List({$inputVarName}, function() { return /[0-9]/.test({$inputVarName}.getValue()); }, '" . 
				addslashes($this->lng_get_msg_txt("password_require_number", "La contraseña debe contener al menos un número")) . "');\n");
		}
		
		if ($pass_policy->require_special ?? false) {
			$js->add_cont("  inputsman.addValidation2List({$inputVarName}, function() { return /[!@#$%^&*(),.?\":{}|<>]/.test({$inputVarName}.getValue()); }, '" . 
				addslashes($this->lng_get_msg_txt("password_require_special", "La contraseña debe contener al menos un carácter especial")) . "');\n");
		}
	}
	
	/**
	 * Render captcha using legacy system
	 */
	function output_captcha_render_js($reset_pass_mode) {
		if (!$this->use_captcha) return;
		
		$captchaInput = new mwmod_mw_helper_captcha_input("captcha");
		$captchaInput->captcha_cod = "captcha";
		$captchaInput->set_attr_name_prefix($reset_pass_mode ? "resetpassdata" : "reqdata");
		
		$captchaHtml = $captchaInput->get_bootstrap_fg_html();
		$captchaHtml = addslashes(str_replace(array("\r", "\n"), '', $captchaHtml));
		
		$containerId = $reset_pass_mode ? 
			$this->get_ui_elem_id("resetcaptcha") : 
			$this->get_ui_elem_id("requestcaptcha");
		
		$js = new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont("document.getElementById('" . $containerId . "').innerHTML = '" . $captchaHtml . "';\n");
		
		echo $js->get_js_script_html();
	}
	
	/**
	 * Allow during forced security action
	 */
	function isAllowedDuringForcedSecurityAction() {
		return true;
	}
}
?>
