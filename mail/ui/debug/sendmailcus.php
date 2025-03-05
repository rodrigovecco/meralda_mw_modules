<?php
class mwmod_mw_mail_ui_debug_sendmailcus extends mwmod_mw_mail_ui_debug_sendmail{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Cus cfg");
		
	}
	function get_php_mailer(){
		//sin configurar
		
		$mailerman=$this->mainap->get_submanager("sysmail");
		if(!$man=$mailerman->get_phpmailer_man()){
			return false;	
		}
		if(!$phpmailer=$man->new_phpmailer()){
			return false;	
		}
		$phpmailer->CharSet="utf-8";
		$phpmailer->IsSMTP();
		$this->cfg_phpmailer($phpmailer);
		/*
		
		
		$mailerman=$this->mainap->get_submanager("sysmail");
		
		$phpmailer=$mailerman->new_phpmailer();
		
		$phpmailer->DKIM_domain="pastipan.onmicrosoft.com";
		//$this->cfg_phpmailer($phpmailer);
		*/
		
		return $phpmailer;
	}
	function cfg_phpmailer($mailer){
		$mailer->Host="facipub.facipub.net";
		$email="pastipan@novoingenios.com";
		$mailer->Username=$email;
		$mailer->From=$email;
		$mailer->Sender=$email;
		$mailer->SMTPAuth  = true;
		
		$mailer->Port="465";
		$mailer->Password="asdf234rtf24f";
		$mailer->SMTPKeepAlive     = true;
		$mailer->useSMTPssl     = true;
		$mailer->SMTPSecure="ssl";//ssl, tls
		$mailer->IsSMTP();
		$mailer->FromName="Pastipan NI";


		//ok mailtest@novoingenios.com
		/*
		$mailer->Host="novoingenios.com";
		$email="mailtest@novoingenios.com";
		$mailer->Username=$email;
		$mailer->From=$email;
		$mailer->Sender=$email;
		//$mailer->Hostname="";
		$mailer->SMTPAuth  = true;
		
		$mailer->Port="465";
		$mailer->Password="asdf234rtf24f";
		$mailer->SMTPKeepAlive     = true;
		$mailer->useSMTPssl     = true;
		$mailer->SMTPSecure="ssl";//ssl, tls
		$mailer->IsSMTP();
		$mailer->FromName="TEST $email";
		
		*/
		
		/*
		ok desde server
		$mailer->Host="meditarydar.com";
		$email="mailtest@meditarydar.com";
		$mailer->Username=$email;
		$mailer->From=$email;
		$mailer->Sender=$email;
		$mailer->SMTPAuth  = true;
		
		$mailer->Port="465";
		$mailer->Password="asdf234rtf24f";
		$mailer->SMTPKeepAlive     = true;
		$mailer->useSMTPssl     = true;
		$mailer->SMTPSecure="ssl";//ssl, tls
		$mailer->IsSMTP();
		$mailer->FromName="Alguien";
		
		*/
	}
	
	
	function do_exec_page_in_bot(){
		echo "<p>Cambiar configuración en ".__FILE__."</p>";	
	}

	
}
?>