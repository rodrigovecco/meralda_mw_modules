<?php
class mwmod_mw_users_permissions_special_experimental extends mwmod_mw_users_permissions_permission{
	//no usado
	function __construct($cod,$namedef,$roles_codes,$permisionsman){
		$this->init($cod,$namedef,$roles_codes,$permisionsman);	
		$this->set_catch_all_requests(true);
		$this->set_allways_allow_main_user(false);
	}
	function allow($user,$params=false){
		if(!$this->is_enabled()){
			$this->allow_debug_info="Not enabled";
			return false;	
		}
		if(!$this->user_can_have($user)){
			$this->allow_debug_info="user can not have";
			return false;	
		}
		if(!$user){
			$this->allow_debug_info="no user";
			return false;	
		}
		
		if($user->is_main_user()){
			if($this->allways_allow_main_user()){
				$this->allow_debug_info="is_main_user";
				return true;	
			}
		}
		if($this->is_allowed_for_all_users()){
			$this->allow_debug_info="is_allowed_for_all_users";
			return true;	
		}
		if($user->get_permission($this->get_code())){
			if($this->allow_user_checked($user,$params)){
				$this->allow_debug_info="user has permission";
				return true;	
			}
		}
		if($this->parent_permissions_allow($user,$params)){
			return true;	
		}
		if($this->other_permission_allowed("debug",$user,$params)){
			$this->allow_debug_info="debug allowed";
			return true;	
		}
		/*
		if($user->get_permission($this->get_code())){
			return $this->allow_user_checked($user,$params);
		}
		*/
		return false;	
	}
	
	/*
	function allow($user,$params=false){
		if(!$this->is_enabled()){
			return false;	
		}
		if(!$this->user_can_have($user)){
			return false;	
		}
		if(!$user){
			return false;	
		}
		
		if($user->is_main_user()){
			if($this->allways_allow_main_user()){
				return true;	
			}
		}
		if($this->is_allowed_for_all_users()){
			return true;	
		}
		if($user->get_permission($this->get_code())){
			if($this->allow_user_checked($user,$params)){
				return true;	
			}
		}
		if($this->parent_permissions_allow($user,$params)){
			return true;	
		}
		
		return false;	
	}
	*/
	
	/*
	function load_enabled(){
		if(!$cfg=$this->mainap->get_cfg()){
			return true;	
		}
		if(!$cfg->get_value_boolean("debug_mode")){
			return false;	
		}
		if(!$cfg->get_value_boolean("debug_restrict_ips")){
			return true;	
		}
		if($ips=$cfg->is_on_list("debug_ips",$_SERVER['REMOTE_ADDR'])){
			return true;	
		}
		return false;
	}
	*/
	
}
?>