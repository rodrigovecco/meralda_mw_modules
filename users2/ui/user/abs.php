<?php
/**
 * Users2 - User Sub-Interface Base Class
 * 
 * Base class for all user editing sub-interfaces.
 * Provides common functionality for userdata, userpass, userrols, userimg.
 */
abstract class mwmod_mw_users2_ui_user_abs extends mwmod_mw_users2_ui_abs {
    
    /**
     * @var mwmod_mw_users2_userabs|null Current user being edited
     */
    protected $current_edit_user = null;
    
    /**
     * Check permission
     */
    public function is_allowed() {
        return $this->allow("adminusers");
    }
    
    /**
     * Check if allowed for current item
     */
    public function is_allowed_for_current_item() {
        if (!$this->is_allowed()) {
            return false;
        }
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
     * Set current edit user
     * @param mwmod_mw_users2_userabs|null $user
     */
    public function set_current_edit_user($user) {
        $this->current_edit_user = $user;
    }
    
    /**
     * Get current edit user
     * @return mwmod_mw_users2_userabs|null
     */
    public function get_current_edit_user() {
        if ($this->current_edit_user) {
            return $this->current_edit_user;
        }
        
        // Try to get from parent
        if ($this->parent_subinterface && method_exists($this->parent_subinterface, "get_or_set_current_item_by_req")) {
            $user = $this->parent_subinterface->get_or_set_current_item_by_req();
            if ($user) {
				$this->setRequestParam("iditem", $user->get_id());
                $this->current_edit_user = $user;
                return $user;
            }
        }
        
        return null;
    }
    
    /**
     * Get user data manager
     * @return mwmod_mw_users2_userdata|null
     */
    public function getUserDataMan() {
        if (!$uman = $this->getUman()) {
            return null;
        }
        return $uman->get_user_data_man();
    }
    
    /**
     * Prepare before execution - load modern inputs JS in header
     */
    public function prepare_before_exec_no_sub_interface(): void {
        $this->loadModernInputsJs();
    }
    
    /**
     * Execute without sub interface
     */
    public function do_exec_no_sub_interface() {
        $user = $this->get_current_edit_user();
        if ($user) {
            $this->set_current_item($user);
        }
    }
}
?>
