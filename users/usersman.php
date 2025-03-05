<?php
//
class mwmod_mw_users_usersman extends mwmod_mw_users_usersmanabs{
	function __construct($ap,$tbl,$sessionvar="__current_user_data"){
		$this->init_tbl_mode($ap,$tbl,$sessionvar);
		
	}
	function register_permission_request_if_enabled($permission){
		if(!$this->check_str_key($permission)){
			return false;	
		}
		if(!$this->mainap->cfg){
			return false;
		}
		if($this->mainap->cfg->get_value_boolean("register_permissions_requests")){
			return $this->register_permission_request($permission);
		}
		return false;	
	}
	function register_permission_request($permission){
		if(!$this->check_str_key($permission)){
			return false;	
		}
		if(!$ado=$this->get_treedata_item("permissionsrequest","logs")){
			return false;	
		}
		if($ado->get_data("requested_dates.".$permission)){
			return true;
		}
		if(!$ado->get_data("log_creation_date")){
			$ado->set_data(date("Y-m-d H:i:s"),"log_creation_date");
		}
		$ado->set_data(date("Y-m-d H:i:s"),"log_update_date");
		$ado->set_data(date("Y-m-d H:i:s"),"requested_dates.".$permission);
		$ado->save();
		return true;
		
			
	}

	
}
?>