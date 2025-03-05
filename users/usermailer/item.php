<?php
//
abstract class mwmod_mw_users_usermailer_item extends mw_apsubbaseobj{
	private $man;
	private $cod;
	public $name;
	private $enabled;
	
	//public $useImgsBasePath=false;
	
	final function init($cod,$man){
		$this->cod=$cod;
		$this->man=$man;
	}
	
	
	function canSendForUser(){
		return $this->isEnabled();	
	}
	function sendForUser($user){
		if(!$this->canSendForUser()){
			return false;	
		}
		if(!$phpmailer=$this->getReadyMailerForUser($user)){
			return false;
		}
		return $phpmailer->send();
	}
	function getReadyMailerForUser($user){
		if(!$phgr=$this->getReadyPHPprocessorsGrForUser($user)){
			return false;
		}
		if(!$phpmailer=$this->new_new_phpmailer_for_user_and_phgr($phgr,$user)){
			return false;
		}
		return $phpmailer;
			
	}

	
	function getReadyPHPprocessorsGrForUser($user){
		if(!$phgr=$this->new_ph_processors_gr()){
			return false;	
		}
		if(!$user){
			return false;	
		}
		$ds=$phgr->get_or_create_ph_src();
		$this->prepare_ph_src_ap($ds);
		$this->prepare_ph_src_for_user($ds,$user);
		return $phgr;
		
		/*
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

		*/

		
	}

	function getDebugDataFull(){
		$r=$this->getDebugData();
		if($phgr=$this->new_ph_processors_gr()){
			$r["phgr"]=$phgr->get_debug_data();
		}

		return $r;	
	}
	
	
	function getDebugData(){
		$r=array(
			"cod"=>$this->cod,
			"name"=>$this->get_name(),
			"enabled"=>$this->isEnabled(),
		);
		return $r;	
	}
	function prepare_ph_src_for_user($ds,$user){
		return $this->man->prepare_ph_src_for_user($ds,$user);
	}
	
	function prepare_ph_src_ap($ds){
		return $this->man->prepare_ph_src_ap($ds);
	}
	function new_new_phpmailer_for_user_and_phgr($phgr,$user=false){
		return $this->man->new_new_phpmailer_for_user_and_phgr($phgr,$user);
	}
	
	function new_ph_processors_gr($cod=false){
		$cod=$this->getPHprocessoCod($cod);
		return $this->man->new_ph_processors_gr($cod);
	}
	function getPHprocessoCod($cod=false){
		if($cod){
			return $cod;	
		}
		return $this->cod;	
	}
	function getMailMsgSrcsItem($cod=false){
		$cod=$this->getPHprocessoCod($cod);
		if($this->man->mailMsgsMan){
			return 	$this->man->mailMsgsMan->get_item($cod);
		}
		//return $this->man->new_ph_processors_gr($cod);
		
	}
	
	
	
	function loadIsEnabled(){
		return $this->man->is_msg_enabled($this->cod);	
	}
	
	final function setEnabled($val=true){
		if($val){
			$this->enabled=true;	
		}else{
			$this->enabled=false;	
		}
	}
	final function isEnabled(){
		if(!isset($this->enabled)){
			if($this->loadIsEnabled()){
				$this->enabled=true;	
			}else{
				$this->enabled=false;	
				
			}
		}
		return $this->enabled;
	}
	
	function get_name(){
		if($this->name){
			return $this->name;	
		}
		return $this->cod;	
	}
	function get_id(){
		return $this->cod;	
	}
	final function __get_priv_man(){
		return $this->man; 	
	}
	final function __get_priv_cod(){
		return $this->cod; 	
	}

	
}
?>