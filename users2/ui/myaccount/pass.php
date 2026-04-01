<?php
/**
 * My Account - Change Password
 * Uses modern JS input system (mwmod_mw_jsobj_inputs_*)
 * Delegates input creation to userdata manager (extensible pattern)
 */
class mwmod_mw_users2_ui_myaccount_pass extends mwmod_mw_users2_ui_myaccount_abs {
    
    function __construct($cod, $parent) {
        $this->init_as_subinterface($cod, $parent);
        $this->set_def_title($this->lng_get_msg_txt("change_password", "Cambiar contraseña"));
    }
    
    function do_exec_page_in() {
        if (!$user = $this->get_current_user()) {
            return false;
        }
        
        $dm = $user->get_user_data_man();
        $msgs = new mwmod_mw_html_elem();
        $msgs->only_visible_when_has_cont = true;
        
        // Process form submission
        $inputMan = new mwmod_mw_helper_inputvalidator_request("passdata");
        if ($inputMan->is_req_input_ok()) {
            $dm->savefromfrm_user_changepass($inputMan, $user, $msgs);
        }
        
        // Build form
        $frm = new mwmod_mw_jsobj_inputs_frmonpanel();
        $frm->set_prop("lbl", $this->lng_get_msg_txt("change_password", "Cambiar contraseña"));
        
        $mainGr = $frm->add_data_main_gr("passdata");
        
        // Delegate input creation to dataman (extensible)
        $dm->addChangePassInputs($mainGr, $user);
        
        // Submit button
        $frm->add_submit($this->lng_get_msg_txt("change_password", "Cambiar contraseña"));
        
        // Render form
        $this->renderFormToContainer($frm, 'frmcontainer');
        
        // Show messages if any
        if ($msgs) {
            echo $msgs->get_as_html();
        }
        
        return true;
    }
}
?>
