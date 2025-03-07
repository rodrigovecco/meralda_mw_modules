<?php
//
class mwmod_mw_users_usermailer_item_resetpassrequest extends mwmod_mw_users_usermailer_item{
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
		if(!$token=$user->create_reset_password_token()){
			return false;
		}
		
		$ds=$phgr->get_or_create_ph_src();
		$this->prepare_ph_src_ap($ds);
		$this->prepare_ph_src_for_user($ds,$user);
		$dsitem=$ds->get_or_create_item("user");
		$dsitem->add_item_by_cod($token,"reset_pass_code");
		if($this->man->ui_reset_password){
			$this->man->ui_reset_password->prepare_ph_src($ds,$user);	
		}
	
		return $phgr;
		

		
	}
	
}
?>