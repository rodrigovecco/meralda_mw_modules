<?php
//
class mwmod_mw_users_groups_man extends mwmod_mw_manager_man{
	private $usersman;
	function __construct($usersman){
		$this->init_gr_man($usersman,"user_groups");	
	}
	function create_item($tblitem){
		
		$item=new mwmod_mw_users_groups_item($tblitem,$this);
		return $item;
	}
	
	function add_admin_interface($ui){
		$si=$ui->add_new_subinterface(new mwmod_mw_users_groups_ui_admin("admingroups",$ui));	
	}
	final function __get_priv_usersman(){
		return $this->usersman; 	
	}

	function allow_admin(){
		return $this->mainap->current_admin_user_allow("adminusers");	
	}
	
	final function init_gr_man($usersman,$code,$tblname=false){
		$this->usersman=$usersman;
		$this->init($code,$this->usersman->mainap,$tblname);
		$this->enable_strdata(true);
		$this->enable_treedata(true);
		//$this->set_mainap($this->usersman->mainap);	
	}

}
?>