<?php
class mwmod_mw_ui_install_phpinfo extends mwmod_mw_ui_sub_withfrm{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_def_title($this->get_msg("PHPinfo"));
		
	}
	function do_exec_no_sub_interface(){
		if(!$this->allow_edit_user()){
			return false;	
		}
	}
	function do_exec_page_in(){
		
		phpinfo();

		
	}
	function allow_edit_user(){
		if(!$this->is_allowed()){
			return false;	
		}
		return $this->maininterface->get_cfg_data("setupmainuser.allowed");
	}
	function is_allowed(){
		return $this->maininterface->install_credentials_ok();
	}
	
}
?>