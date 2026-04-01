<?php
/**
 * My Account container - Users2 module
 * Manages My Account subinterfaces (data, pass, img, token)
 */
class mwmod_mw_users2_ui_myaccount_myaccount extends mwmod_mw_ui_base_basesubuia {
    
    function __construct($cod, $maininterface) {
        $this->init_as_main_or_sub($cod, $maininterface);
        $this->set_def_title($this->lng_get_msg_txt("my_account", "Mi cuenta"));
        $this->subinterface_def_code = "data";
        $this->sucods = "data,pass,img,token";
    }
    
    function _do_create_subinterface_child_data($cod) {
        return new mwmod_mw_users2_ui_myaccount_data($cod, $this);
    }
    
    function _do_create_subinterface_child_pass($cod) {
        return new mwmod_mw_users2_ui_myaccount_pass($cod, $this);
    }
    
    function _do_create_subinterface_child_img($cod) {
        return new mwmod_mw_users2_ui_myaccount_img($cod, $this);
    }
    
    function _do_create_subinterface_child_token($cod) {
        return new mwmod_mw_users2_ui_myaccount_token($cod, $this);
    }
    
    function do_exec_no_sub_interface() {
    }
    
    function do_exec_page_in() {
    }
    
    function is_allowed() {
        return $this->allow("editmydata");
    }
}
?>
