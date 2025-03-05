<?php
abstract class mwmod_mw_mail_mailer_man_abs extends mw_apsubbaseobj{
	private $_cfg_data;
	private $_phpmailerman;
	private $_mail_queue_man;

	function __construct(){
		//	
	}
	function load_mail_queue_man(){
		return false;//extender	
	}
	final function get_mail_queue_man(){
		if(isset($this->_mail_queue_man)){
			return $this->_mail_queue_man;	
		}
		$this->_mail_queue_man=false;
		if($man=$this->load_mail_queue_man()){
			$this->_mail_queue_man=$man;	
		}
		return $this->_mail_queue_man;	
	}
	function cfg_phpmailer($mailer){
		//PHPMailer
		if($val=$this->get_cfg_data("auth.Hostname")){
			$mailer->Hostname=$val;
		}
		if($val=$this->get_cfg_data("auth.Host")){
			$mailer->Host=$val;
		}
		if($val=$this->get_cfg_data("auth.SMTPAuth")){
			$mailer->SMTPAuth  = true;
		}
		if($val=$this->get_cfg_data("auth.Username")){
			$mailer->Username=$val;
			$mailer->From=$val;
			$mailer->Sender=$val;
		}
		
		if($val=$this->get_cfg_data("auth.Port")){
			$mailer->Port=$val;
		}
		if($val=$this->get_cfg_data("auth.From")){
			$mailer->From=$val;
		}
		if($val=$this->get_cfg_data("auth.Sender")){
			$mailer->Sender=$val;
		}
		if($val=$this->get_cfg_data("auth.Password")){
			$mailer->Password=$val;
		}
		if($val=$this->get_cfg_data("SMTPKeepAlive")){
			$mailer->SMTPKeepAlive     = true;
		}
		
		if($val=$this->get_cfg_data("auth.useSMTPssl")){
			$mailer->useSMTPssl     = true;
			$mailer->SMTPSecure="ssl";
		}
		if($val=$this->get_cfg_data("auth.SMTPSecure")){
			$mailer->SMTPSecure=$val;
		}
		
		if($val=$this->get_cfg_data("auth.Helo")){
			$mailer->Helo=$val;
		}
		if($val=$this->get_cfg_data("header.Organization")){
			$mailer->addCustomHeader("Organization",$val);
		}
		
		
		$mailer->IsSMTP();
		if($val=$this->get_cfg_data("auth.FromName")){
			$mailer->FromName=$val;
		}
		if($val=$this->get_cfg_data("replyto.address")){
			$val1=$this->get_cfg_data("replyto.name")."";
			$mailer->addReplyTo($val,$val1);
		}
		
		$this->cfg_phpmailer_extra($mailer);
		$this->setupPhpMailer($mailer);
		
	}
	function cfg_phpmailer_extra($mailer){
		//extender
	}
	function setupPhpMailer($phpmailer){
		
	}

	function new_temp_msg(){
		$msg=new mwmod_mw_mail_queue_tempmsg();
		return $msg;	
	}
	function queue_msg($temp_msg){
		if($man=$this->get_mail_queue_man()){
			return $man->queue_msg($temp_msg);	
		}
			
	}
	function send_temp_msg($temp_msg){
		if(!$mailer=$this->new_phpmailer()){
			return false;	
		}
		if(!$temp_msg->prepare_mailer($mailer)){
			return false;
		}
		return $mailer->send();
	}

	
	function new_phpmailer(){
		if(!$man=$this->get_phpmailer_man()){
			return false;	
		}
		if(!$obj=$man->new_phpmailer()){
			return false;	
		}
		$obj->CharSet="utf-8";
		$this->cfg_phpmailer($obj);
		return $obj;
		//if(!$obj=$this->	
	}
	final function get_phpmailer_man(){
		if(!isset($this->_phpmailerman)){
			$this->_phpmailerman=new mwmod_mw_mail_phpmailer_man();	
		}
		return $this->_phpmailerman;
	}
	
	function load_cfg_data(){
		return false;	
	}
	final function init_cfg_data(){
		if(isset($this->_cfg_data)){
			return;
		}
		$this->_cfg_data=array();
		if($data=$this->load_cfg_data()){
			if(is_array($data)){
				$this->_cfg_data=$data;
			}
		}
	}
	final function get_cfg_data($cod=false){
		$this->init_cfg_data();
		if(!$cod){
			return $this->_cfg_data;	
		}else{
			return mw_array_get_sub_key($this->_cfg_data,$cod);
		}
		
	}
	
}
?>