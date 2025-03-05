<?php
class mwmod_mw_mail_phpmailer_man extends mw_apsubbaseobj{
	function __construct(){
		//	
	}
	function new_phpmailer(){
		$obj=new PHPMailer();
		return $obj;	
	}
	function checkemail($add){
		if(mw_checkemail($add)){
			return $add;
		}
	}
	function preparePHPMailer($phpMailer=false){
		if(!$phpMailer){
			$phpMailer=$this->new_phpmailer();
		}
		if($man=$this->mainap->get_submanager("sysmail")){
			$man->cfg_phpmailer($phpMailer);

		}
		return $phpMailer;
		
	}
	
}
$subpathman=mw_get_main_ap()->get_sub_path_man("modulesext/phpmailer","system");
if($fullpath=$subpathman->get_file_path_if_exists("include.php")){
	require_once $fullpath;
}
/*
include_once("class.phpmailer.php");
include_once("class.pop3.php");
include_once("class.smtp.php");
*/
?>