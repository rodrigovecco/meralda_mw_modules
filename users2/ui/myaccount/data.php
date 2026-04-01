<?php
/**
 * My Account - Edit Profile Data
 * Uses modern JS input system (mwmod_mw_jsobj_inputs_*)
 * Delegates input creation to userdata manager (extensible pattern)
 */
class mwmod_mw_users2_ui_myaccount_data extends mwmod_mw_users2_ui_myaccount_abs {
    
    function __construct($cod, $parent) {
        $this->init_as_subinterface($cod, $parent);
        $this->set_def_title($this->lng_get_msg_txt("mydata", "Mis datos"));
    }
    
    function do_exec_page_in() {
        if (!$user = $this->get_current_user()) {
            return false;
        }
        
        $dm = $user->get_user_data_man();
        $msg = '';
        
        // Process form submission
        $inputMan = new mwmod_mw_helper_inputvalidator_request("userdata");
        if ($inputMan->is_req_input_ok()) {
            $dm->savefromfrm_user_data($inputMan, $user, $msg);
        }
        
        // Build form
        $frm = new mwmod_mw_jsobj_inputs_frmonpanel();
        $frm->set_prop("lbl", $this->lng_get_msg_txt("mydata", "Mis datos"));
        
        $mainGr = $frm->add_data_main_gr("userdata");
        
        // Delegate input creation to dataman (extensible)
        $dm->addMyAccountDataInputs($mainGr, $user);
        
        // Submit button
        $frm->add_submit($this->lng_common_get_msg_txt("save", "Guardar"));
        
        // Render form
        $this->renderFormToContainer($frm, 'frmcontainer');
        
        // Show message if any
        if ($msg) {
            $alert = new mwmod_mw_bootstrap_html_specialelem_alert($msg);
            echo $alert->get_as_html();
        }
        
        return true;
    }
}
?>
