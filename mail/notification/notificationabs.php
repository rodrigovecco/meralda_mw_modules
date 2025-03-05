<?php
//20240118
abstract class mwmod_mw_mail_notification_notificationabs extends mw_apsubbaseobj{
	private $mailer_man;
	var $mail_msg_cod;
	var $user;
	
	var $mail_msg_item;//set on prepare
	var $ph_processors_gr;//set on prepare
	var $phpmailer;//set on prepare
	
	var $debug_mode=false;
	//fata verficar que no se repitan direcciones
	
	function checkEmail($mail){

		if(mw_checkemail($mail,false)){
			return trim($mail);

		}
	}
	function checkEmails($strList){
		if(!$strList){
			return false;
		}
		if(!is_string($strList)){
			return false;
		}
		$strList=str_replace(" ", ";", $strList);
		$strList=str_replace(",", ";", $strList);
		$a=explode(";",$strList);
		$r=array();
		foreach($a as $c){
			if($c=trim($c)){
				if($this->checkEmail($c)){
					$r[$c]=$c;
				}
			}
		}
		if(sizeof($r)){
			return $r;
		}
	}
	function prepare_and_send(){
		if($mailer=$this->prepare()){
			return $this->send_mailer_or_temp_msg($mailer);
			/*
			if(is_object($mailer)){
				if(is_a($mailer,"PHPMailer")){
					if($this->debug_mode){
						$mailer->SMTPDebug=4;
					}
					return $mailer->send();
				}
			}
			*/
		}
	}
	function prepare(){
		//extender
		//puede devolver PHPMailer o mwmod_mw_mail_queue_tempmsg		
	}
	function send_mailer_or_temp_msg($send_obj){
		// $send_obj PHPMailer o mwmod_mw_mail_queue_tempmsg		
		if(!$send_obj){
			return false;
		}
		if(!is_object($send_obj)){
			return false;	
		}
		if(is_a($send_obj,"PHPMailer")){
			if($this->debug_mode){
				$send_obj->SMTPDebug=4;
			}
			return $send_obj->send();
		}
		if(is_a($send_obj,"mwmod_mw_mail_queue_tempmsg")){
			
			return $this->send_temp_msg($send_obj);
		}
		
		
		
	}
	function send_temp_msg($temp_msg){
		// $temp_msg mwmod_mw_mail_queue_tempmsg
		if($this->queue_enabled()){
			return $this->queue_msg($temp_msg);
		}
		if(!$mailer=$this->new_phpmailer()){
			return false;	
		}
		if($this->debug_mode){
			$mailer->SMTPDebug=4;
		}
		if(!$temp_msg->prepare_mailer($mailer)){
			return false;
		}
		return $mailer->send();
	}
	function queue_msg($temp_msg){
		if($man=$this->get_mail_queue_man()){
			return $man->queue_msg($temp_msg);	
		}
			
	}
	function get_mail_queue_man(){
		if($man=$this->get_mailer_man()){
			return $man->get_mail_queue_man();	
		}
	}
	function queue_enabled(){
		return false;	
	}
	
	function get_prepare_debug_data(){
		$this->debug_mode=true;
		$send_obj=$this->prepare();
		$data=array();
		$data["class"]=get_class($this);
		
		
		
		if($this->user){
			$data["user"]=$this->user->get_id()." ".$this->user->get_idname();
		}
		if($this->mail_msg_item){
			$data["mail_msg_item"]["class"]=get_class($this->mail_msg_item);
			$data["mail_msg_item"]["code"]=$this->mail_msg_item->cod;
			$data["mail_msg_item"]["files"]=$this->mail_msg_item->get_files_locations_info();
			
		}
		if($this->ph_processors_gr){
			$data["subject"]=$this->ph_processors_gr->get_text_final("subject");
			$data["plain"]=nl2br($this->ph_processors_gr->get_text_final("plain"));
			$data["html"]=$this->ph_processors_gr->get_text_final("html");
			//$data["ph_processors_gr"]=$this->ph_processors_gr->get_debug_data_for_mail();	
		}
		if($this->phpmailer){
			$data["phpmailer"]["to"]=$this->phpmailer->getToAddresses();	
			$data["phpmailer"]["cc"]=$this->phpmailer->getCcAddresses();	
			$data["phpmailer"]["bcc"]=$this->phpmailer->getBccAddresses();	
			$data["phpmailer"]["Attachments"]=$this->phpmailer->getAttachments();	
			
			
		}
		if($send_obj){
			$data["send_obj"]["class"]=get_class($send_obj);	
			$data["send_obj"]["to"]=$send_obj->getToAddresses();	
			$data["send_obj"]["from"]=$send_obj->From;	
			$data["send_obj"]["cc"]=$send_obj->getCcAddresses();	
			$data["send_obj"]["bcc"]=$send_obj->getBccAddresses();	
			$data["send_obj"]["Attachments"]=$send_obj->getAttachments();	
				
		}
		return $data;
			
	}

	function debug(){
		
		/*
		$this->debug_mode=true;
		$send_obj=$this->prepare();
		$data=array();
		$data["class"]=get_class($this);
		
		
		
		if($this->user){
			$data["user"]=$this->user->get_id()." ".$this->user->get_idname();
		}
		if($this->mail_msg_item){
			$data["mail_msg_item"]["class"]=get_class($this->mail_msg_item);	
		}
		if($this->ph_processors_gr){
			$data["subject"]=$this->ph_processors_gr->get_text_final("subject");
			$data["plain"]=nl2br($this->ph_processors_gr->get_text_final("plain"));
			$data["html"]=$this->ph_processors_gr->get_text_final("html");
			//$data["ph_processors_gr"]=$this->ph_processors_gr->get_debug_data_for_mail();	
		}
		if($this->phpmailer){
			$data["phpmailer"]["to"]=$this->phpmailer->getToAddresses();	
			$data["phpmailer"]["cc"]=$this->phpmailer->getCcAddresses();	
			$data["phpmailer"]["bcc"]=$this->phpmailer->getBccAddresses();	
			$data["phpmailer"]["Attachments"]=$this->phpmailer->getAttachments();	
			
			
		}
		if($send_obj){
			$data["send_obj"]["class"]=get_class($send_obj);	
			$data["send_obj"]["to"]=$send_obj->getToAddresses();	
			$data["send_obj"]["cc"]=$send_obj->getCcAddresses();	
			$data["send_obj"]["bcc"]=$send_obj->getBccAddresses();	
			$data["send_obj"]["Attachments"]=$send_obj->getAttachments();	
				
		}
		*/
		//getToAddresses
		
		mw_array2list_echo($this->get_prepare_debug_data());
		
		
		//extender	
	}
	function can_send(){
		//extender		
	}
	
	function prepare_ph_processors_gr($phgr){
		if(!$ds=$phgr->get_or_create_ph_src()){
			return false;	
		}
		return $this->prepare_ph_src($ds);
	}
	function prepare_ph_src($ds){
		$this->prepare_ph_src_def($ds);
		$this->prepare_ph_src_additional($ds);
		return $ds;
	}
	function prepare_ph_src_additional($ds){
		//
	}
	function prepare_ph_src_def($ds){
		$this->prepare_ph_src_ap($ds);
		if($this->user){
			$this->prepare_ph_src_for_user($ds,$this->user);	
		}
	}
	function prepare_ph_src_for_user($ds,$user){
		if(!$user){
			return false;
		}
		$dsitem=$ds->get_or_create_item("user");
		return $this->prepare_data_src_user($dsitem,$user);
	}
	function prepare_data_src_user($dsitem,$user){
		if(!$user){
			return false;
		}
		$dsitem->add_item_by_cod($user->get_real_name_or_idname(),"full_name");
		$dsitem->add_item_by_cod($user->get_idname(),"idname");
			
	}
	
	
	function prepare_ph_src_ap($ds){
		$dsitem=$ds->get_or_create_item("app");
		if($lng=$this->mainap->get_current_lng_man()){
			if($txt=$lng->get_cfg_data("sitename")){
				$dsitem->add_item_by_cod($txt,"sitename");
			}
		}
	}

	
	
	function new_ph_processors_gr($cod=false){
		if(!$cod){
			$cod=$this->mail_msg_cod;	
		}
		if($mailmsgsitem=$this->get_mail_msg_item($cod)){
			return $mailmsgsitem->new_ph_processors_gr();	
		}
	}
	function get_mail_msg_item($cod=false){
		if(!$cod){
			$cod=$this->mail_msg_cod;	
		}
		if(!$mailmsgsman=$this->get_mail_msgs_man()){
			return false;
		}
		return $mailmsgsman->get_item($cod);

	}

	
	function get_mail_msgs_man(){
		if(!$lngman=$this->mainap->get_submanager("lng")){
			return false;	
		}
		if($mailmsgsman=$lngman->get_mail_msgs_man()){
			return $mailmsgsman;
		}
	
	}
	function new_phpmailer(){
		if(!$man=$this->get_mailer_man()){
			return false;	
		}
		return $man->new_phpmailer();
			
	}
	function new_temp_msg(){
		if(!$man=$this->get_mailer_man()){
			return false;	
		}
		return $man->new_temp_msg();
			
	}
	function populate_phpmailer($phpmailer,$phgr){
		//$phpmailer PHPMailer o mwmod_mw_mail_queue_tempmsg		
		$ok=false;
		if($subject=$phgr->get_text_final("subject")){
			$ok=true;	
		}
		if(!$html=$phgr->get_text_final("html")){
			$html=nl2br($phgr->get_text_final("plain"));	
		}
		$phpmailer->msgHTML($html);
		$phpmailer->AltBody=$phgr->get_text_final("plain");
		$phpmailer->Subject=$subject;
		return $ok;

	}

	final function __get_priv_mailer_man(){
		if(isset($this->mailer_man)){
			return $this->mailer_man;	
		}
		if(!$this->mailer_man=$this->load_mailer_man()){
			$this->mailer_man=false;	
		}
		return $this->mailer_man; 	
	}
	
	function load_mailer_man(){
		if($mailerman=$this->mainap->get_submanager("sysmail")){
			return $mailerman;
		}
	}
	function get_mailer_man(){
		return $this->__get_priv_mailer_man();	
	}
	
}

?>