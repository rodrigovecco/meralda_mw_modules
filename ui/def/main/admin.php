<?php
class mwmod_mw_ui_def_main_admin extends mwmod_mw_ui_def_main_def{
	function __construct($ap){
		$this->set_mainap($ap);	
		$this->subinterface_def_code="welcome";
		$this->url_base_path="/admin/";
		$this->enable_session_check();
		$this->logout_script_file="logout.php";
		$this->su_cods_for_side="users,cfg,uidebug,system";
	}
	function create_subinterface_cfg(){
		$si= new mwmod_mw_ui_def_cfg("cfg",$this);
		return $si;
	}
	function admin_user_ok(){
		if($user=$this->get_admin_current_user()){
			//return true;
			return $user->allow("admininterfase");	
		}
	}
}
?>