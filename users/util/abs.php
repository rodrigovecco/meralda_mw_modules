<?php

abstract class mwmod_mw_users_util_abs extends mw_apsubbaseobj{
	private $userman;
	
	function load_userman(){
		return 	$this->mainap->get_user_manager();
	}
	final function __get_priv_userman(){
		if(!isset($this->userman)){
			if($man=$this->load_userman()){
				$this->userman=$man;	
			}
		}
		return $this->userman;
	}
	

	
}
?>