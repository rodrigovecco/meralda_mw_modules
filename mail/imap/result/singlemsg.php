<?php
class mwmod_mw_mail_imap_result_singlemsg extends mwmod_mw_mail_imap_result_abs{
	var $msg_uid;
	var $msg;
	function __construct($msg_uid,$conn){
		$this->msg_uid=$msg_uid;
		$this->set_conn($conn);
	}
	function init_msg_full_data(){
		if(!$msg=$this->get_message()){
			return;
		}
		$msg->get_body();
		$msg->get_body_html();
		$msg->get_structure();
		//falta
			
	}
	
	function get_debug_info(){
		$r=array();
		$r["ok"]=$this->result_ok();
		$r["msg_uid"]=$this->msg_uid;
		if($msg=$this->get_message()){
			$r["msg"]=$msg->get_debug_info();	
		}
		return $r;
			
	}
	
	function get_message(){
		if(isset($this->msg)){
			return $this->msg;	
		}
		$this->msg=false;
		$this->init_result();
		if(!is_array($this->raw_result)){
			return false;
		}
		if(!sizeof($this->raw_result)){
			return false;	
		}
		if(!$o=$this->raw_result[0]){
			return false;	
		}
		$msg=new mwmod_mw_mail_imap_result_msg($this);
		$msg->set_overview($o);
		$this->msg=$msg;
		return $this->msg;	
			
	}
	
	function load_result(){
		$msg_uid=round($this->msg_uid+0);
		if($msg_uid<1){
			return false;
		}
		
		
		
		if(!$mbox=$this->get_imap_stream()){
			return false;
		}
		
		if($this->raw_result = imap_fetch_overview($mbox,"{$msg_uid}",FT_UID)){
			return true;
		}
		return false;
		
	}
	
	
}
?>