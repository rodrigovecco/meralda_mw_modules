<?php

class  mwmod_mw_users_tokens_item extends mwmod_mw_manager_item{
	private $user;
	
	function __construct($tblitem,$man){
		$this->init($tblitem,$man);	
	}
	function checkUser(){
		if(!$user=$this->__get_priv_user()){
			return false;	
		}
		
		if(!$str=$this->get_data("innerkey")){
			return false;
		}
		if(!$str1=$this->man->buildInnerKey($user->tblitem->get_data("pass"))){
			return false;	
		}
		if($str===$str1){
			return $user;	
		}
	}
	function loadUser(){
		return $this->man->usersMan->get_user($this->get_data("user_id"));
	}
	final function __get_priv_user(){
		if(!isset($this->user)){
			if(!$this->user=$this->loadUser()){
				$this->user=false;	
			}
		}
		return $this->user; 	
	}
	
	
	
	function isValid(){
		if(!$this->get_data("active")){
			return false;	
		}
		if($time=$this->tblitem->get_data_as_time("exp_date")){
			if(time()>$time){
				return false;
			}
		}
		return true;
	}
	function get_token_str(){
		return $this->man->encodetoken($this);	
	}
	
}


?>