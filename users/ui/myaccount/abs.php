<?php
class mwmod_mw_users_ui_myaccount_abs extends mwmod_mw_ui_sub_withfrm{
	
	
	function do_exec_no_sub_interface(){
		/*
		if(!$uman=$this->mainap->get_user_manager()){
			return false;	
		}
		if(!$user=$uman->get_user($_REQUEST["iditem"])){
			return false;	
		}
		$this->set_current_item($user);
		$this->set_url_param("iditem",$user->get_id());
		*/

		
	}
	
	function is_allowed(){
		return $this->allow("editmydata");	
	}
	
}
?>