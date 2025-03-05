<?php
class mwmod_mw_users_rols_rolmainadmin extends mwmod_mw_users_rols_rol{
	function __construct($cod,$namedef,$man){
		$this->init($cod,$namedef,$man);
		$this->is_permitions_option=false;	
	}
	function user_has_rol($user){
		if(!$this->user_can_have($user)){
			return false;	
		}
		if(!$user){
			
			return false;	
		}
		if($user->is_main_user()){
			return true;	
		}
		return false;	
		
	}
	function user_can_have($user){
		if(!$user){
			return false;	
		}
		if($user->is_main_user()){
			return true;	
		}
		return false;	
	}

	function is_assignable(){
		return false;
	}
	

}
?>