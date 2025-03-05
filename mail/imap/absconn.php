<?php
abstract class mwmod_mw_mail_imap_absconn extends mw_apsubbaseobj{
	private $Host="localhost";
	private $Username;
	private $Password;
	private $Port=143;
	private $Options="/novalidate-cert";
	private $MailboxName="INBOX";
	private $imap_stream;
	
	function get_imap_stream(){
		return $this->__get_priv_imap_stream();	
	}
	function get_single_msg($uid){
		$result=new mwmod_mw_mail_imap_result_singlemsg($uid,$this);
		return $result;
		
	}
	function overview($lim=false,$start=1){
		$result=new mwmod_mw_mail_imap_result_overview($lim,$start,$this);
		return $result;
		
	}
	function ch_folder($mailbox=false){
		if($mailbox===false){
			$mailbox=$this->MailboxName;	
		}
		if($mbox=$this->get_imap_stream()){
			if(imap_reopen($mbox, "{".$this->Host."}".$mailbox)){
				return true;	
			}
			$this->close();
			return false;
		}
	}
	
	function open_imap_stream(){
		$s=imap_open($this->get_conn_string(), $this->Username,$this->Password);
		return $s;
	}
	final function unset_imap_stream(){
		unset($this->imap_stream);	
	}
	function close(){
		if($mbox=$this->get_imap_stream_if_opened()){
			imap_close($mbox);
			$this->unset_imap_stream();
		}
	}
	final function get_imap_stream_if_opened(){
		return 	$this->imap_stream;	
	}
	final function __get_priv_imap_stream(){
		if(!isset($this->imap_stream)){
			$this->imap_stream=$this->open_imap_stream();
		}
		return 	$this->imap_stream; 	
	}
	
	function get_conn_string(){
		$srtcon="{".$this->Host.":".$this->Port.$this->Options."}".$this->MailboxName;
		return $srtcon;
			
	}
	function set_cfg($cfg){
		if(!is_array($cfg)){
			return false;	
		}
		$this->set_Host($cfg["Host"]);
		$this->set_Username($cfg["Username"]);
		$this->set_Password($cfg["Password"]);
		$this->set_Port($cfg["Port"]);
		$this->set_Options($cfg["Options"]);
		$this->set_MailboxName($cfg["MailboxName"]);
		
	}
	final function __get_priv_Host(){
		return $this->Host; 	
	}
	final function __get_priv_Username(){
		return $this->Username; 	
	}
	final function __get_priv_Password(){
		return $this->Password; 	
	}
	final function __get_priv_Port(){
		return $this->Port; 	
	}
	final function __get_priv_Options(){
		return $this->Options; 	
	}
	final function __get_priv_MailboxName(){
		return $this->MailboxName; 	
	}
	final function set_Host($val){
		if($val){
			$this->Host=$val;	
		}
	}
	final function set_Username($val){
		if($val){
			$this->Username=$val;	
		}
	}
	final function set_Password($val){
		if($val){
			$this->Password=$val;	
		}
	}
	final function set_Port($val){
		if($val){
			$this->Port=$val;	
		}
	}
	final function set_Options($val){
		if(!is_null($val)){
			$this->Options=$val."";	
		}
	}
	final function set_MailboxName($val){
		if(!is_null($val)){
			$this->MailboxName=$val."";	
		}
	}

	
	
	
}
?>