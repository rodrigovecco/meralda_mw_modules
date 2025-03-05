<?php
//
class mwmod_mw_users_def_usersman extends mwmod_mw_users_usersman{
	function __construct($ap,$tbl,$sessionvar="__current_user_data"){
		$this->init_tbl_mode($ap,$tbl,$sessionvar);
		
	}
	function create_user_data_man(){
		$man= new mwmod_mw_users_def_userdata($this);
		$man->admin_user_id_enabled=false;
		$man->user_must_change_password_enabled=false;
		return $man;
	}

	function create_pass_policy(){
		$man= new mwmod_mw_users_passpolicy($this);
		$man->change_password_on_remember_ui_enabled=true;
		$man->must_contain_uppers=false;
		$man->must_contain_lowers=false;
		$man->must_contain_numbers=false;
		return $man;
		
	}
	function create_user_mailer(){
		$man= new mwmod_mw_users_usermailer_def($this);
		$man->set_msg_enabled("user_reset_pass_request");
		return $man;
	}


}
?>