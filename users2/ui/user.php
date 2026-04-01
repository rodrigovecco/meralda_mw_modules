<?php
/**
 * Users2 - Individual User Container
 * 
 * Container for editing a specific user.
 * Has subinterfaces: userdata, userpass, userrols, userimg
 */
class mwmod_mw_users2_ui_user extends mwmod_mw_ui_base_basesubuia {
    
    public $mainItemReqParam = "iditem";
    
    public function __construct($cod, $parent) {
        $this->init_as_subinterface($cod, $parent);
        $this->set_def_title($this->lng_get_msg_txt("user", "Usuario"));
        $this->subinterface_def_code = "fulldata";
        $this->sucods = "fulldata,userimg";
    }    
    /**
     * Allow creating subinterfaces by code
     */
    public function allowcreatesubinterfacechildbycode() {
        return true;
    }    
    /**
     * Get users manager from parent
     * @return mwmod_mw_users2_usersman|null
     */
    public function getUman() {
        if ($this->parent_subinterface) {
            return $this->parent_subinterface->getUman();
        }
        return null;
    }
    
    /**
     * Check permission
     */
    public function is_allowed() {
        return $this->allow("adminusers");
    }
    
    /**
     * Get or set current user from request
     * @return mwmod_mw_users2_userabs|false
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
     * Create subinterfaces
     */
    public function _do_create_subinterface_child_userdata($cod) {
        return new mwmod_mw_users2_ui_user_data($cod, $this);
    }
    
    public function _do_create_subinterface_child_userpass($cod) {
        return new mwmod_mw_users2_ui_user_pass($cod, $this);
    }
    
    public function _do_create_subinterface_child_userrols($cod) {
        return new mwmod_mw_users2_ui_user_rols($cod, $this);
    }
    
    public function _do_create_subinterface_child_userimg($cod) {
        return new mwmod_mw_users2_ui_user_img($cod, $this);
    }
    
    public function _do_create_subinterface_child_fulldata($cod) {
        return new mwmod_mw_users2_ui_user_fulldata($cod, $this);
    }
    
    /**
     * Menu configuration
     */
    public function is_responsable_for_sub_interface_mnu() {
        return true;
    }
    
    public function create_sub_interface_mnu_for_sub_interface($su = false) {
        $mnu = new mwmod_mw_mnu_mnu();
        
        // Parent link (users list)
        if ($this->parent_subinterface) {
            $this->parent_subinterface->add_2_mnu($mnu);
        }
        
        $user = $this->get_or_set_current_item_by_req();
        
        // This user
        $item = $this->add_2_mnu($mnu);
        $item->etq = $this->lng_get_msg_txt("information", "Información");
        
        // Sub menus
        if ($subs = $this->get_subinterfaces_by_code($this->sucods, true)) {
            foreach ($subs as $sub) {
                $sub->set_current_edit_user($user);
                if ($sub->is_allowed_for_current_item()) {
                    $sub->add_2_sub_interface_mnu($mnu);
                }
            }
        }
        
        return $mnu;
    }
    
    /**
     * Title for breadcrumb
     */
    public function get_title_route() {
        if ($user = $this->get_or_set_current_item_by_req()) {
            return $user->get_idname();
        }
        return $this->get_title();
    }
    
    public function get_html_for_parent_chain_on_child_title() {
        $txt = $this->get_title_route();
        $url = $this->get_url();
        return "<a href='$url'>$txt</a>";
    }
    
    public function get_title_for_box() {
        if ($user = $this->get_current_item()) {
            return $user->get_real_and_idname();
        }
        return $this->get_title();
    }
    
    /**
     * Execute without sub interface - show user info
     */
    public function do_exec_no_sub_interface() {
        if (!$uman = $this->getUman()) {
            return false;
        }
        
        $iditem = $_REQUEST["iditem"] ?? null;
        if (!$user = $uman->get_user($iditem)) {
            return false;
        }
        
        $this->set_current_item($user);
        $this->set_url_param("iditem", $user->get_id());
    }
    
    /**
     * Execute page - show user info (readonly view)
     */
    public function do_exec_page_in() {
        if (!$user = $this->get_current_item()) {
            return false;
        }
        
        
    }
}
?>
