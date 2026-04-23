<?php
/**
 * Admin - New User Form
 * Uses modern JS input system (mwmod_mw_jsobj_inputs_*)
 * Delegates input creation to userdata manager (extensible pattern)
 */
class mwmod_mw_users2_ui_newuser extends mwmod_mw_users2_ui_abs {
    
    /** @var mwmod_mw_bootstrap_html_specialelem_alert|null */
    protected $alertMsg = null;
    
    public function __construct($cod, $parent) {
        $this->init_as_subinterface($cod, $parent);
        $this->set_def_title($this->lng_common_get_msg_txt("new_user", "Nuevo usuario"));
    }
    
    /**
     * Get users manager from parent interface
     * @return mwmod_mw_users2_usersman|null
     */
    protected function getUman() {
        if ($this->parent_subinterface) {
            return $this->parent_subinterface->getUman();
        }
        return null;
    }
    
    /**
     * Check if user has permission
     */
    public function is_allowed() {
        return $this->allow("adminusers");
    }
    
    /**
     * Prepare before execution (load JS)
     */
    public function prepare_before_exec_no_sub_interface() {
        $this->loadModernInputsJs();
    }
    
    /**
     * Execute page
     */
    public function do_exec_page_in() {
        if (!$uman = $this->getUman()) {
            return false;
        }
        
        $dm = $uman->get_user_data_man();
        
        // Initialize alert message container
        $this->alertMsg = new mwmod_mw_bootstrap_html_specialelem_alert(false, "danger");
        $this->alertMsg->only_visible_when_has_cont = true;
        
        // Process form submission
        $inputMan = new mwmod_mw_helper_inputvalidator_request("nduser_usernd");
        if ($inputMan->is_req_input_ok()) {
            if ($this->processNewUserForm($dm, $inputMan)) {
                // Success - show success message
                $this->alertMsg = new mwmod_mw_bootstrap_html_specialelem_alert(
                    $this->lng_get_msg_txt("user_created", "Usuario creado correctamente"),
                    "success"
                );
            }
        }
        
        // Build form
        $this->buildAndRenderForm($dm);
        
        return true;
    }
    
    /**
     * Process new user form submission
     * 
     * @param mwmod_mw_users2_userdata $dm Data manager
     * @param mwmod_mw_helper_inputvalidator_request $inputMan Input manager
     * @return bool True if user was created successfully
     */
    protected function processNewUserForm($dm, $inputMan): bool {
        // Use the parent class method that handles all validation and creation
        $dm->create_new_user_from_admin_ui($inputMan, $this);
        
        // Check if there's any error message from the process
        // The create_new_user_from_admin_ui method sets error messages via set_bottom_alert_msg
        return !$this->bottom_alert_msg;
    }
    
    /**
     * Build and render the form
     * 
     * @param mwmod_mw_users2_userdata $dm Data manager
     */
    protected function buildAndRenderForm($dm): void {
        // Build form
        $frm = new mwmod_mw_jsobj_inputs_frmonpanel();
        $frm->set_prop("lbl", $this->lng_common_get_msg_txt("new_user", "Nuevo usuario"));
        
        $mainGr = $frm->add_data_main_gr("nduser_usernd");
        
        // Delegate input creation to dataman (extensible)
        $dm->addNewUserInputs($mainGr);
        
        // Submit button
        $frm->add_submit($this->lng_common_get_msg_txt("create", "Crear"));
        
        // Render alert if there's a message
        if ($this->alertMsg) {
            echo $this->alertMsg->get_as_html();
        }
        
        // Render form
        $this->renderFormToContainer($frm, 'frmcontainer');
    }
    
    /**
     * Set bottom alert message (called by create_new_user_from_admin_ui)
     * Override parent method to also update local alertMsg
     * 
     * @param mixed $msg Alert message (string or HTML element)
     */
    function set_bottom_alert_msg($msg = false) {
        parent::set_bottom_alert_msg($msg);
        $this->alertMsg = $msg;
    }
}
?>
