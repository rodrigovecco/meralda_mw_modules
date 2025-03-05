<?php
//
class mwmod_mw_users_usermailer_item_resetpass extends mwmod_mw_users_usermailer_item{
	function __construct($cod,$man){
		$this->init($cod,$man);
	}
	function getReadyPHPprocessorsGrForUser($user){
		if(!$phgr=$this->new_ph_processors_gr()){
			return false;	
		}
		if(!$user){
			return false;	
		}
		
		
		
		$ds=$phgr->get_or_create_ph_src();
		if(!$pass=$user->reset_password()){
			return false;
		}
		$this->prepare_ph_src_ap($ds);
		$this->prepare_ph_src_for_user($ds,$user);
		if($this->man->ui_reset_password){
			$this->man->ui_reset_password->prepare_ph_src($ds,$user);	
		}
		$dsitem=$ds->get_or_create_item("user");
		$dsitem->add_item_by_cod($pass,"new_password");
	
		return $phgr;
		

		
	}
	
}
?>