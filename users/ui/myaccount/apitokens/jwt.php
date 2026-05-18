<?php
/**
 * My Account — Tokens — Session (JWT)
 *
 * Generates a JWT bound to the user's password hash.
 * Inherits all of the user's permissions (no own scope).
 *
 * Uses the modern jsobj_inputs helpers promoted to mwmod_mw_ui_sub_withfrm
 * (loadModernInputsJs + renderFormToContainer).
 */
class mwmod_mw_users_ui_myaccount_apitokens_jwt extends mwmod_mw_users_ui_myaccount_abs {

	function __construct($cod, $parent) {
		$this->init_as_subinterface($cod, $parent);
		$this->set_def_title($this->lng_get_msg_txt("jwtSessionToken", "Token de sesión"));
	}

	function is_allowed() {
		if (!$user = $this->get_current_user()) {
			return false;
		}
		if (!$user->man->jwtMan) {
			return false;
		}
		return $this->allow("owntoken");
	}

	function prepare_before_exec_no_sub_interface() {
		parent::prepare_before_exec_no_sub_interface();
		$this->loadModernInputsJs();
	}

	function do_exec_page_in() {
		if (!$user = $this->get_current_user()) {
			return false;
		}

		$newToken = null;

		$inputMan = new mwmod_mw_helper_inputvalidator_request("newdata");
		if ($inputMan->is_req_input_ok()) {
			if ($inputMan->get_value_by_dot_cod("confirm")) {
				$newToken = $user->man->jwtMan->createTokenForUser($user);
			}
		}

		$frm = new mwmod_mw_jsobj_inputs_frmonpanel();
		$frm->set_prop("lbl", $this->lng_get_msg_txt("jwtSessionToken", "Token de sesión"));

		$mainGr = $frm->add_data_main_gr("newdata");

		$input = $mainGr->addNewCheckbox(
			"confirm",
			$this->lng_get_msg_txt(
				"userTokenGenerationConfirmMSG",
				"Comprendo que este token puede ser usado para ejecutar acciones en mi cuenta y firmarlas con mis credenciales."
			)
		);
		$input->setNotes($this->lng_get_msg_txt(
			"userTokenGenerationConfirmEXTRAINFO",
			"Los tokens se invalidan al cambiar contraseña."
		));

		if ($newToken) {
			$input = $mainGr->addNewChild("token", "textarea");
			$input->setLabel($this->lng_get_msg_txt("newToken", "Nuevo token"));
			$input->set_value($newToken);
			$input->setReadOnly(true);
		}

		$frm->add_submit($this->lng_get_msg_txt("generate", "Generar"));

		$this->renderFormToContainer($frm, "frmcontainer");

		return true;
	}
}
?>
