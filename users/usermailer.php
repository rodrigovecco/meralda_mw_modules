<?php
//
class mwmod_mw_users_usermailer extends mw_apsubbaseobj{
	private $man;
	private $_msg_cfg=array();
	var $last_error_html="";
	var $ui_reset_password;//mwmod_mw_subui_rememberlogindata
	//responsabel de envío de correo a usuario, extender para habilitar
	private $mailMsgsMan;
	
	private $_items;
	
	function __construct($man){
		$this->init($man);
		
	}
	function getDebugData(){
		$r=array(
			"cfg"=>$this->get_msgs_cfg_full()
		);
		if($items=$this->getItems()){
			$r["items"]=array();
			foreach($items as $cod=> $item){
				$r["items"][$cod]=$item->getDebugData();	
			}
				
		}
		return $r;	
	}
	function itemIsEnabled($cod){
		if($item=$this->getItem($cod)){
			return $item->isEnabled();	
		}
	}
	final function getItem($cod){
		$this->initItems();
		if($cod){
			return $this->_items[$cod];	
		}
	}
	final function getItems(){
		$this->initItems();
		return $this->_items;	
	}
	function loadItems(){
		$r=array();
		return $r;
	}
	final function initItems(){
		if(isset($this->_items)){
			return;	
		}
		$this->_items=array();
		if($items=$this->loadItems()){
			foreach($items as $item){
				$c=$item->cod;
				$this->_items[$c]=$item;	
			}
		}
		
	}
	
	
	final function __get_priv_mailMsgsMan(){
		if(!isset($this->mailMsgsMan)){
			$this->mailMsgsMan=$this->loadMailMsgsMan();
		}
		
		return $this->mailMsgsMan; 	
	}
	function loadMailMsgsMan(){
		if(!$lngman=$this->mainap->get_submanager("lng")){
			return false;	
		}
		if($mailmsgsman=$lngman->get_mail_msgs_man()){
			return $mailmsgsman;
		}
			
	}
	function get_mail_msgs_man(){
		return $this->__get_priv_mailMsgsMan();	
	}
	
	
	function new_ph_processors_gr($cod){
		if(!$mailmsgsman=$this->get_mail_msgs_man()){
			return false;
		}
		if($mailmsgsitem=$mailmsgsman->get_item($cod)){
			return $mailmsgsitem->new_ph_processors_gr();	
		}
		
		/*
		if(!$lngman=$this->mainap->get_submanager("lng")){
			return false;	
		}
		if(!$mailmsgsman=$lngman->get_mail_msgs_man()){
			return false;
		}
		if($mailmsgsitem=$mailmsgsman->get_item($cod)){
			return $mailmsgsitem->new_ph_processors_gr();	
		}
		*/

	}
	function new_new_phpmailer_for_user_and_phgr($phgr,$user=false){
		if(!$phpmailer=$this->new_new_phpmailer_for_user($user)){
			return false;	
		}
		$phpmailer->msgHTML($phgr->get_text_final("html"));
		$phpmailer->AltBody=$phgr->get_text_final("plain");
		$phpmailer->Subject=$phgr->get_text_final("subject");
		return $phpmailer;

	}
	function new_new_phpmailer_for_user($user=false){
		if(!$mailerman=$this->mainap->get_submanager("sysmail")){
			return false;
		}
		$phpmailer=$mailerman->new_phpmailer();
		if($user){
			if($mail=$user->get_email()){
				$phpmailer->addAddress($mail);	
			}
		}
		return $phpmailer;
		
	}
	function prepare_ph_src_for_user($ds,$user){
		if(!$user){
			return false;
		}
		$dsitem=$ds->get_or_create_item("user");
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
	function msg_reset_password_request_enabled(){
		return $this->is_msg_enabled("reset_password_request");
	}
	
	function msg_reset_password_request_send($user){
		if(!$phgr=$this->new_ph_processors_gr("user_reset_pass_request")){
			return false;	
		}
		if(!$user){
			return false;	
		}
		if(!$token=$user->create_reset_password_token()){
			return false;
		}
		$ds=$phgr->get_or_create_ph_src();
		$this->prepare_ph_src_ap($ds);
		$this->prepare_ph_src_for_user($ds,$user);
		$dsitem=$ds->get_or_create_item("user");
		$dsitem->add_item_by_cod($token,"reset_pass_code");
		
		if($this->ui_reset_password){
			$this->ui_reset_password->prepare_ph_src($ds,$user);	
		}
		
		if(!$phpmailer=$this->new_new_phpmailer_for_user_and_phgr($phgr,$user)){
			return false;
		}
		return $phpmailer->send();
	}
	function msg_reset_password_send($user){
		if(!$phgr=$this->new_ph_processors_gr("user_reset_pass")){
			return false;	
		}
		if(!$user){
			return false;	
		}
		$ds=$phgr->get_or_create_ph_src();
		if(!$pass=$user->reset_password()){
			return false;
		}
		$this->prepare_ph_src_ap($ds);
		$this->prepare_ph_src_for_user($ds,$user);
		if($this->ui_reset_password){
			$this->ui_reset_password->prepare_ph_src_new_pass($ds,$user);	
		}
		$dsitem=$ds->get_or_create_item("user");
		$dsitem->add_item_by_cod($pass,"new_password");
		if(!$phpmailer=$this->new_new_phpmailer_for_user_and_phgr($phgr,$user)){
			return false;
		}
		return $phpmailer->send();
	}
	
	function msg_on_pass_change_enabled(){
		return $this->is_msg_enabled("on_pass_change");
	}
	function msg_on_pass_change_send($user){
		//falta
		return false;	
	}

	function msg_on_user_created_enabled(){
		return $this->is_msg_enabled("on_user_created");
	}
	function msg_on_user_created_send($user){
		return false;	
	}
	
	
	final function init($man){
		$this->man=$man;
		$ap=$man->mainap;
		
		$this->set_mainap($ap);
		$this->set_lngmsgsmancod("user");
					
	}

	function set_msg_enabled($msgcod){
		if(!$msgcod){
			return false;	
		}
		$msgcod=str_replace(" ",",",$msgcod);
		$msgcod=str_replace("\n",",",$msgcod);
		$msgcod=str_replace("\r",",",$msgcod);
		$msgcod=str_replace(",,",",",$msgcod);
		$msgcod=str_replace(",,",",",$msgcod);
		$a=explode(",",$msgcod);
		$r=0;
		foreach($a as $c){
			if($c=trim($c)){
				if($this->set_msg_cfg($c,true,"enabled")){
					$r++;	
				}
			}
		}
		return $r;
	}
	
	function is_msg_enabled($msgcod){
		return $this->get_msg_cfg($msgcod,"enabled");
	}
	final function get_msgs_cfg_full(){
		return $this->_msg_cfg;
	}
	final function get_msg_cfg($msgcod,$cfgcod=false){
		if(!$msgcod=$this->check_str_key_alnum_underscore(trim($msgcod))){
			return false;
		}
		if(!$cfgcod){
			return $this->_msg_cfg[$msgcod];
		}else{
			return mw_array_get_sub_key($this->_msg_cfg,$msgcod.".".$cfgcod);
		}
	}
	
	final function set_msg_cfg($msgcod,$val,$cfgcod=false){
		if(!$msgcod=$this->check_str_key_alnum_underscore(trim($msgcod))){
			return false;
		}
		if(!$cfgcod){
			if(is_array($val)){
				$this->_msg_cfg[$msgcod]=$val;
				return true;	
			}
			return false;
		}else{
			mw_array_set_sub_key($msgcod.".".$cfgcod,$val,$this->_msg_cfg);
			return true;
		}
	}
	final function __get_priv_man(){
		return $this->man; 	
	}
	
}
?>