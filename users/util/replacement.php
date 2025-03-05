<?php

class mwmod_mw_users_util_replacement extends mwmod_mw_users_util_abs{
	var $users=array();
	var $final_user;
	var $user;
	var $permition_cod;
	function __construct($user=false){
		if($user){
			$this->set_orig_user_by_id_or_item($user);	
		}
	}
	function get_debug_data(){
		$d=array(
			"orig"=>$this->user,
			"final"=>$this->get_final_user(),
			"list"=>$this->users,
			
		
		);
		return $d;
	}
	function get_replacement_for_user($user){
		if(!$new_id=$user->get_out_of_office_replacement_id()){
			return false;	
		}
		if($this->users[$new_id]){
			return false;
		}
		if(!$new_user=$this->userman->get_user($new_id)){
			return false;	
		}
		$this->users[$new_user->get_id()]=$new_user;
		if($this->is_user_ok($new_user)){
			return 	$new_user;
		}
		return $this->get_replacement_for_user($user);
		
	}
	function load_final_user(){
		if(!$this->user){
			return false;
		}
		if($this->is_user_ok($this->user)){
			return $this->user;	
		}
		return $this->get_replacement_for_user($this->user);
	}
	function get_final_user(){
		if(isset($this->final_user)){
			return $this->final_user;
		}
		$this->final_user=$this->load_final_user();
		return $this->final_user;
	}
	function is_user_ok($user){
		if(!$user->is_active()){
			return false;
		}
		if($user->is_out_of_office()){
			return false;
		}
		if($this->permition_cod){
			if(!$user->allow($this->permition_cod)){
				return false;
			}
		}
		return true;
		
		
		
	}
	function set_orig_user_by_id_or_item($user){
		unset($this->final_user);
		unset($this->user);
		$this->users=array();
		if(!$user){
			return false;	
		}
		if(is_object($user)){
			return $this->set_orig_user($user);
				
		}
		if($u=$this->userman->get_user($user)){
			return $this->set_orig_user($u);	
		}
	
	}
	function set_orig_user($user){
		unset($this->final_user);
		$this->users=array();
		$this->users[$user->get_id()]=$user;
		$this->user=$user;
		return $user;
		
	}
	
	

	
}
?>