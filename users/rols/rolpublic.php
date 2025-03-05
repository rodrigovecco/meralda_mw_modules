<?php
class mwmod_mw_users_rols_rolpublic extends mwmod_mw_users_rols_rol{
	function __construct($cod,$namedef,$man){
		$this->init($cod,$namedef,$man);	
	}
	function is_allowed_for_no_user(){
		return true;
	}
	function is_allowed_for_any_user(){
		return true;
	}
	function is_assignable(){
		return false;
	}
	function user_can_have($user){
		return true;	
			
	}
	function can_add_permision($item){
		return false;//falta
	}
	function user_has_rol($user){
		if(!$this->user_can_have($user)){
			return false;	
		}
		return true;
		
	}


}
?>