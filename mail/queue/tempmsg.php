<?php
class mwmod_mw_mail_queue_tempmsg extends mw_apsubbaseobj{
	public $From;
	public $FromName;
	public $Subject = '';
	public $HTMLBody = '';
	public $AltBody = '';
	public $priority=0;
	public $htmlBasedir="";
	
	public $isHtml=0;
	private $_address_list=array();
	private $attachment=array();
	
	
	function __construct(){
		//	
	}
	function prepare_queue_msg($msg_item){
		
		return $msg_item->set_data_from_temp_msg($this);
		
		
	}
	
	
	function prepare_mailer($mailer){
		if(!$this->addAddresses2mailer($mailer)){
			return false;	
		}
		if(!$this->addContent2mailer($mailer)){
			return false;	
		}
		return true;
		
	}
	function check(){
		if($list=$this->getToAddresses()){
			return true;
		}
		if($list=$this->getCcAddresses()){
			return true;
		}
		if($list=$this->getBccAddresses()){
			return true;
		}
		return false;
			
	}
	function get_html_body(){
		return $this->HTMLBody;	
	}
	function addContent2mailer($mailer){
		if($attachments=$this->getAttachments()){
			foreach($attachments as $d){
				$mailer->addAttachment($d["path"], $d["name"], $d["encoding"], $d["type"], $d["disposition"]);
			}
		}
		
		if($this->Subject){
			$mailer->Subject=$this->Subject;	
		}
		if($this->isHtml){
			$mailer->msgHTML($this->get_html_body(),$this->htmlBasedir);
			if($this->AltBody){
				$mailer->AltBody=$this->AltBody;
			}
		}else{
			$mailer->isHTML(false);
			$mailer->Body=$this->AltBody;
		}
		return true;
	}
	function get_short_debug_info_str(){
		$strlist=array();
		if($list=$this->getToAddresses()){
			$slist=array();
			foreach($list as $d){
				$slist[]=$d["address"];
			}
			$strlist[]="TO: ".implode("; ",$slist);
		}
		if($list=$this->getCcAddresses()){
			$slist=array();
			foreach($list as $d){
				$slist[]=$d["address"];
			}
			$strlist[]="CC: ".implode("; ",$slist);
		}
		if($list=$this->getBccAddresses()){
			$slist=array();
			foreach($list as $d){
				$slist[]=$d["address"];
			}
			$strlist[]="BCC: ".implode("; ",$slist);
		}
		if($this->Subject){
			$strlist[]=	"Subject: ".$this->Subject;
		}
		return implode("\n",$strlist);
		
	}

	function addAddresses2mailer($mailer){
		$num=0;
		if($this->From){
			$mailer->setFrom($this->From,$this->FromName);	
		}
		if($list=$this->getToAddresses()){
			foreach($list as $d){
				if($mailer->addAddress($d["address"],$d["name"])){
					$num++;	
				}
			}
		}
		if($list=$this->getCcAddresses()){
			foreach($list as $d){
				if($mailer->addCC($d["address"],$d["name"])){
					$num++;	
				}
			}
		}
		if($list=$this->getBccAddresses()){
			foreach($list as $d){
				if($mailer->addBCC($d["address"],$d["name"])){
					$num++;	
				}
			}
		}
		if($list=$this->getReplyToAddresses()){
			foreach($list as $d){
				if($mailer->addReplyTo($d["address"],$d["name"])){
					$num++;	
				}
			}
		}
		return $num;
			
	}
	
	
	function msgHTML($message,$basedir=""){
		$this->HTMLBody=$message;
		$this->htmlBasedir=$basedir;
		$this->isHtml=1;
			
	}
    final function getAttachments(){
       return $this->attachment;
    }
	final function addAttachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment'){
		 if (!@is_file($path)) {
			return false;	 
		 }
		 $d=array(
		 	"path"=>$path,
		 	"name"=>$name,
		 	"encoding"=>$encoding,
		 	"type"=>$type,
		 	"disposition"=>$disposition,
		 
		 );
		 $this->attachment[]=$d;
		 return true;
		 
	}
	private final function addAnAddress($kind,$address, $name = ''){
		if(!$address=$this->validateAddress($address)){
			return false;	
		}
		if(!$this->_address_list[$kind]){
			$this->_address_list[$kind]=array();	
		}
		$this->_address_list[$kind][$address]=array(
			"address"=>$address,
			"name"=>$name,
		
		);
		
			
	}
	
	final function getToAddresses(){
		return $this->getAddresses("to");
	}
	final function getCcAddresses(){
		return $this->getAddresses("cc");
	}
	final function getBccAddresses(){
		return $this->getAddresses("bcc");
	}
	final function getReplyToAddresses(){
		return $this->getAddresses("ReplyTo");
	}
	private final function getAddresses($kind ){
		if(!$this->_address_list[$kind]){
			return false;
		}
		$r=array();
		$x=1;
		$a=$this->_address_list[$kind];
		foreach($a as $d){
			$r[$x]=$d;
			$x++;	
		}
		return $r;
			
	}
	
	final function addBCC($address, $name = ''){
		return $this->addAnAddress('bcc', $address, $name);	
	}
	final function addAddress($address, $name = ''){
		return $this->addAnAddress('to', $address, $name);	
	}
	final function addCC($address, $name = ''){
		return $this->addAnAddress('cc', $address, $name);	
	}
	final function addReplyTo($address, $name = ''){
		$this->addAnAddress('ReplyTo', $address, $name);	
		/*
		if(!$address=$this->validateAddress($address)){
			return false;	
		}
		$this->Reply=$address;
		$this->ReplyName=$name;
		
		return true;
		*/
		
		//$this->addAnAddress('Reply-To', $address, $name);	
	}
	
	function setFrom($address, $name = ''){
		if(!$address=$this->validateAddress($address)){
			return false;	
		}
		$this->From=$address;
		$this->FromName=$name;
		return true;
		
	}

	function validateAddress($address){
		if(!$address=trim($address)){
			return false;	
		}
		$address=strtolower($address);
		if(!mw_checkemail($address,false)){
			return false;	
		}
		return $address;
	}
	
}
?>