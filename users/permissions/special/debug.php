<?php
class mwmod_mw_users_permissions_special_debug extends mwmod_mw_users_permissions_permission{
	
	function __construct($cod,$namedef,$roles_codes,$permisionsman){
		$this->init($cod,$namedef,$roles_codes,$permisionsman);	
		$this->set_catch_all_requests(true);
		$this->set_allways_allow_main_user(true);
	}
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
	
}
?>