<?php
class mwmod_mw_users_rols_rolall extends mwmod_mw_users_rols_rol{
	function __construct($cod,$namedef,$man){
		$this->init($cod,$namedef,$man);	
	}
	function is_allowed_for_any_user(){
		return true;
	}
	function is_assignable(){
		return false;
	}
	function can_add_permision($item){
		return false;
	}
	function user_has_rol($user){
		if(!$this->user_can_have($user)){
			return false;	
		}
		if(!$user){
			
			return false;	
		}
		return $user->is_active();
		
	}

}
?>