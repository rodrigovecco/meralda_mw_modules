<?php
/**
 * Users2 - Edit User Data
 * 
 * Admin interface for editing user basic data.
 * Uses modern JS inputs pattern.
 */
class mwmod_mw_users2_ui_user_data extends mwmod_mw_users2_ui_user_abs {
    
    public function __construct($cod, $parent) {
        $this->init_as_subinterface($cod, $parent);
        $this->set_def_title($this->lng_get_msg_txt("data", "Datos"));
    }
    
    /**
     * Execute no sub interface
     */
    public function do_exec_no_sub_interface() {
        parent::do_exec_no_sub_interface();
    }
    
    /**
     * Execute page: show edit form
     */
    public function do_exec_page_in() {
        if (!$user = $this->get_current_edit_user()) {
            echo "<div class='alert alert-danger'>Error: No se pudo cargar el usuario</div>";
            return false;
        }
        
        if (!$dm = $this->getUserDataMan()) {
            echo "<div class='alert alert-danger'>Error: No se pudo obtener el Data Manager</div>";
            return false;
        }
        
        $msgs = new mwmod_mw_html_elem();
        $msgs->only_visible_when_has_cont = true;
        
        // Process form submission
        $inputMan = new mwmod_mw_helper_inputvalidator_request("userdata");
        if ($inputMan->is_req_input_ok()) {
            if ($nd = $inputMan->get_value_by_dot_cod_as_list("data")) {
                if ($dm->saveUserData($nd, $user, $msgs)) {
                    // Success
                }
            }
        }
        
        // Build form
        $frm = new mwmod_mw_jsobj_inputs_frmonpanel();
        $frm->set_prop("lbl", $this->lng_get_msg_txt("edit_user_data", "Datos del usuario"));
        
        // Main input group
        $mainGr = $frm->add_data_main_gr("userdata");
        
        // Username (readonly in edit mode)
        $input = $mainGr->addNewChild("username", "input");
        $input->setLabel($dm->lng_get_msg_txt("user_name", "Nombre de usuario"));
        $input->set_value($user->get_idname());
        $input->setReadOnly(true);
        
        // Editable data group - delegate to dataman
        $dataGr = $mainGr->addNewGr("data");
        $dm->addUserDataInputs($dataGr, $user);
        
        // Submit button
        $frm->add_submit($this->lng_common_get_msg_txt("save", "Guardar"));
        
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
