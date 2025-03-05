<?php
//
class mwmod_mw_users_usermailer_def extends mwmod_mw_users_usermailer{
	function __construct($man){
		$this->init($man);
		
	}
	function loadItems(){
		$r=array();
		$r[]=new mwmod_mw_users_usermailer_item_resetpassrequest("user_reset_pass_request",$this);
		$r[]=new mwmod_mw_users_usermailer_item_resetpass("user_reset_pass",$this);
		return $r;
	}
	function msg_reset_password_request_enabled(){
		return $this->itemIsEnabled("user_reset_pass_request");
	}
	

	
	function msg_reset_password_request_send($user){
		if($item=$this->getItem("user_reset_pass_request")){
			return $item->sendForUser($user);	
		}
	}
	function msg_reset_password_send($user){
		if($item=$this->getItem("user_reset_pass")){
			return $item->sendForUser($user);	
		}
	}

	
}
?>