<?php
/**
 * My Account - Access Token
 * Uses modern JS input system (mwmod_mw_jsobj_inputs_*)
 */
class mwmod_mw_users2_ui_myaccount_token extends mwmod_mw_users2_ui_myaccount_abs {
    
    function __construct($cod, $parent) {
        $this->init_as_subinterface($cod, $parent);
        $this->set_def_title($this->lng_get_msg_txt("accessToken", "Token de acceso"));
    }
    
    function do_exec_page_in() {
        if (!$user = $this->get_current_user()) {
            return false;
        }
        
        $newToken = null;
        
        // Process form submission
        $inputMan = new mwmod_mw_helper_inputvalidator_request("newdata");
        if ($inputMan->is_req_input_ok()) {
            if ($inputMan->get_value_by_dot_cod("confirm")) {
                $newToken = $user->man->jwtMan->createTokenForUser($user);
            }
        }
        
        // Build form
        $frm = new mwmod_mw_jsobj_inputs_frmonpanel();
        $frm->set_prop("lbl", $this->lng_get_msg_txt("accessToken", "Token de acceso"));
        
        $mainGr = $frm->add_data_main_gr("newdata");
        
        // Confirmation checkbox
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
        
        // Show generated token if available
        if ($newToken) {
            $input = $mainGr->addNewChild("token", "textarea");
            $input->setLabel($this->lng_get_msg_txt("newToken", "Nuevo token"));
            $input->set_value($newToken);
            $input->setReadOnly(true);
        }
        
        // Submit button
        $frm->add_submit($this->lng_get_msg_txt("generate", "Generar"));
        
        // Render form
        $this->renderFormToContainer($frm, 'frmcontainer');
        
        return true;
    }
    
    function is_allowed(): bool {
        if (!$user = $this->get_current_user()) {
            return false;
        }
        if (!$user->man->jwtMan) {
            return false;
        }
        return $this->allow("owntoken");
    }
}
?>
