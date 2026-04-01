<?php
/**
 * Forced password change subinterface.
 * 
 * Shown when the user's must_change_pass flag is set.
 * Does NOT ask for current password (the user just logged in and was redirected here).
 * Only asks for new password + confirmation.
 * On success, clears must_change_pass and redirects to main interface.
 */
class mwmod_mw_users2_ui_forcechangepass extends mwmod_mw_users2_ui_abs {
    
    function __construct($cod, $maininterface) {
        $this->init_as_main_or_sub($cod, $maininterface);
        $this->set_def_title($this->lng_get_msg_txt("must_change_password_title", "Cambio de contraseña obligatorio"));
    }
    
    function is_allowed(): bool {
        if (!$user = $this->get_current_user()) {
            return false;
        }
        return $user->allow("admininterfase");
    }
    
    /**
     * This subinterface IS the security action itself, so it must be accessible.
     */
    function isAllowedDuringForcedSecurityAction(){
        return true;
    }
    
    function prepare_before_exec_no_sub_interface(): void {
        $this->loadModernInputsJs();
    }
    
    function do_exec_page_in() {
        if (!$user = $this->get_current_user()) {
            return false;
        }
        
        $dm = $user->get_user_data_man();
        $msgs = new mwmod_mw_html_elem();
        $msgs->only_visible_when_has_cont = true;
        
        // Process form submission
        $inputMan = new mwmod_mw_helper_inputvalidator_request("forcepassdata");
        if ($inputMan->is_req_input_ok()) {
            if ($dm->saveForceChangePass($inputMan, $user, $msgs)) {
                // Success - redirect to main interface
                $url = $this->maininterface->get_url();
                echo '<script>window.location.href=' . json_encode($url) . ';</script>';
                return true;
            }
        }
        
        // Info alert
        $infoAlert = new mwmod_mw_bootstrap_html_specialelem_alert(
            $this->lng_get_msg_txt("must_change_password_info", "Por seguridad, debes cambiar tu contraseña antes de continuar."),
            "warning"
        );
        
        // Build form
        $frm = new mwmod_mw_jsobj_inputs_frmonpanel();
        $frm->set_prop("lbl", $this->lng_get_msg_txt("must_change_password_title", "Cambio de contraseña obligatorio"));
        
        $mainGr = $frm->add_data_main_gr("forcepassdata");
        
        // Delegate input creation to dataman
        $dm->addForceChangePassInputs($mainGr, $user);
        
        // Submit button
        $frm->add_submit($this->lng_get_msg_txt("change_password", "Cambiar contraseña"));
        
        // Render
        $container = $this->get_ui_dom_elem_container_empty();
        $container->add_cont($infoAlert);
        $frmContainer = $this->set_ui_dom_elem_id('frmcontainer');
        $container->add_cont($frmContainer);
        
        // Messages
        if ($msgs) {
            $container->add_cont($msgs);
        }
        
        $container->do_output();
        
        $js = new mwmod_mw_jsobj_jquery_docreadyfnc();
        $this->set_ui_js_params();
        $var = $this->get_js_ui_man_name();
        
        $js->add_cont($var . ".init(" . $this->ui_js_init_params->get_as_js_val() . ");\n");
        $js->add_cont("var frm=" . $frm->get_as_js_val() . ";\n");
        $js->add_cont("frm.append_to_container(" . $var . ".get_ui_elem('frmcontainer'));\n");
        
        echo $js->get_js_script_html();
        
        return true;
    }
}
?>
