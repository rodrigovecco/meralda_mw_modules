<?php
//
class mwmod_mw_users_org_usersman extends mwmod_mw_users_usersman{
	function __construct($ap,$tbl,$sessionvar="__current_user_data"){
		$this->init_tbl_mode($ap,$tbl,$sessionvar);
		
	}
	function create_groups_man(){
		$man= new mwmod_mw_users_groups_man($this);
		return $man;
	}

	function new_user($tblitem){
		$user= new mwmod_mw_users_org_user($this,$tblitem);
		return $user;
	}
	
	function create_user_mailer(){
		$man= new mwmod_mw_users_usermailer($this);
		$man->set_msg_enabled("reset_password_request");
		return $man;
	}
	
	function create_user_data_man(){
		$man= new mwmod_mw_users_org_userdata($this);
		return $man;
	}

	
}
?>