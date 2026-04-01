<?php
/**
 * Users2 - Full User Data Editor (Modern JS Inputs)
 * 
 * Admin interface for editing all user data in a single form:
 * - Access data (username, active)
 * - Basic data (name, email, etc.)
 * - Password change (optional)
 * - Roles assignment
 * - Groups
 * 
 * Has userimg as subinterface for profile image.
 */
class mwmod_mw_users2_ui_user_fulldata extends mwmod_mw_users2_ui_user_abs {
    
    public function __construct($cod, $parent) {
        $this->init_as_subinterface($cod, $parent);
        $this->set_def_title($this->lng_get_msg_txt("edit_user", "Editar usuario"));
    }
    
    /**
     * This interface manages its own menu
     */
    public function is_responsable_for_sub_interface_mnu() {
        return true;
    }
    
    /**
     * Build menu for this interface
     */
    public function create_sub_interface_mnu_for_sub_interface($su = false) {
        $mnu = new mwmod_mw_mnu_mnu();
        
        if ($this->parent_subinterface) {
            $item = $this->parent_subinterface->add_2_mnu($mnu);
        }
        
        $user = $this->get_or_set_current_item_by_req();
        
        $item = $this->add_2_mnu($mnu);
        $item->etq = $this->lng_get_msg_txt("editar", "Editar");
        
        // Add image subinterface to menu
        if ($subs = $this->get_subinterfaces_by_code("userimg", true)) {
            foreach ($subs as $su) {
                $su->set_current_edit_user($user);
                if ($su->is_allowed_for_current_item()) {
                    $su->add_2_sub_interface_mnu($mnu);
                }
            }
        }
        
        return $mnu;
    }
    
    /**
     * Allow creating subinterfaces by code
     */
    public function allowcreatesubinterfacechildbycode() {
        return true;
    }
    
    /**
     * Load subinterfaces
     */
    public function load_all_subinterfases() {
        $this->add_new_subinterface(new mwmod_mw_users2_ui_user_img("userimg", $this));
    }
    
    /**
     * Get or set current user from request
     */
    public function get_or_set_current_item_by_req() {
        if ($user = $this->get_current_item()) {
            return $user;
        }
        
        if (!$uman = $this->getUman()) {
            return false;
        }
        
        $iditem = $_REQUEST["iditem"] ?? null;
        if (!$iditem) {
            return false;
        }
        
        if (!$user = $uman->get_user($iditem)) {
            return false;
        }
        
        $this->set_current_item($user);
        $this->set_url_param("iditem", $user->get_id());
        
        return $user;
    }
    
    /**
     * Breadcrumb link
     */
    public function get_html_for_parent_chain_on_child_title() {
        $txt = $this->get_title_route();
        $url = $this->get_url();
        return "<a href='$url'>$txt</a>";
    }
    
    /**
     * Execute page - show full data form with modern JS inputs
     */
    public function do_exec_page_in() {
        if (!$user = $this->get_current_item()) {
            echo "<div class='alert alert-danger'>Error: No se pudo cargar el usuario</div>";
            return false;
        }
        
        if (!$user->allowadmin_admin()) {
            echo "<div class='alert alert-warning'>No tienes permisos para administrar este usuario</div>";
            return false;
        }
        
        if (!$dm = $this->getUserDataMan()) {
            echo "<div class='alert alert-danger'>Error: No se pudo obtener el Data Manager</div>";
            return false;
        }
        
        $msgs = new mwmod_mw_html_elem();
        $msgs->only_visible_when_has_cont = true;
        
        // Process form submission
        $inputMan = new mwmod_mw_helper_inputvalidator_request("fulldata");
        if ($inputMan->is_req_input_ok()) {
            $dm->saveFullData($inputMan, $user, $msgs);
        }
        
        // Build form with modern JS inputs
        $frm = new mwmod_mw_jsobj_inputs_frmonpanel();
        $frm->set_prop("lbl", $this->lng_get_msg_txt("edit_user", "Editar usuario"));
        
        // Main input group
        $mainGr = $frm->add_data_main_gr("fulldata");
        
        // Add all inputs (access, data, password, roles, groups)
        $dm->addFullDataInputs($mainGr, $user);
        
        // Submit button
        $frm->add_submit($this->lng_common_get_msg_txt("save", "Guardar"));
        
        // Render form
        $this->renderFormToContainer($frm, 'frmcontainer');
        
        // Show messages
        if ($msgs) {
            echo $msgs->get_as_html();
        }
        
        return true;
    }
}
?>
