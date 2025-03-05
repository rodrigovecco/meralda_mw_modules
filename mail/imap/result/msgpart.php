<?php
class mwmod_mw_mail_imap_result_msgpart extends mw_apsubbaseobj{
	private $msg;
	private $conn;
	private $resultset;
	var $data;
	private $index;
	var $isattachment;
	var $attachment_filename;
	var $attachment_name;
	var $attachment_cont;
	
	function __construct($index,$data,$msg){
		$this->set_msg($msg);
		$this->set_index($index);
		$this->set_data($data);	
	}
	function dl_attachment(){
		if(!$this->is_attachment()){
			return false;	
		}
		$c=$this->get_attachment_cont();
		$filename=$this->get_attachment_filename();
		ob_end_clean();
		header("Content-Type: application/octet-stream utf8");
		header("Content-Disposition: attachment; filename=\"{$filename}\"");
		echo $c;
		return true;

		
	}
	function load_attachment_cont(){
		if(!$this->is_attachment()){
			return false;	
		}
		if(!$msguid=$this->msg->uid){
			return false;
		}
		if(!$this->data){
			return false;
		}
		if(!$mbox=$this->msg->get_imap_stream()){
			return false;
		}
		if(!$c=imap_fetchbody($mbox, $msguid, $this->index,FT_UID)){
			return false;	
		}
		if($this->data->encoding == 3) {
			$c=base64_decode($c);	
			
		}elseif($this->data->encoding == 4) {
			$c=quoted_printable_decode($c);	
		}
		return $c;
	}
	function get_attachment_cont(){
		if(!isset($this->attachment_cont)){
			$this->attachment_cont=$this->load_attachment_cont()."";
		}
		return $this->attachment_cont;
	}
	function is_attachment(){
		$this->init_attachment();
		return $this->isattachment;	
	}
	function init_attachment(){
		if(isset($this->isattachment)){
			return $this->isattachment;	
		}
		$this->isattachment=false;
		$this->attachment_filename=false;
		$this->attachment_name=false;
		
		if(!$this->data){
			return false;	
		}
		if($this->data->ifdparameters) {
			foreach($this->data->dparameters as $object) {
				if(strtolower($object->attribute) == 'filename') {
					$this->attachment_filename=$object->value;	
				}
			}
		}
		if($this->data->ifparameters) {
			foreach($this->data->parameters as $object) {
				if(strtolower($object->attribute) == 'name') {
					$this->attachment_name=$object->value;	
				}
			}
		}
		if(($this->attachment_filename)and($this->attachment_name)){
			$this->isattachment=true;	
		}
		
		
		
		return $this->isattachment;	
	}
	function get_attachment_filename(){
		if(!$this->is_attachment()){
			return false;	
		}
		return $this->attachment_filename;
	}
	function get_attachment_data(){
		$r=array();
		$r["index"]=$this->index;
		$r["ok"]=$this->is_attachment();
		$r["name"]=$this->attachment_name;
		$r["filename"]=$this->attachment_filename;
		
		
		//$r["data"]=$this->obj2array($this->data);
		return $r;
			
	}
	
	function get_debug_info(){
		$r=array();
		$r["index"]=$this->index;
		$r["data"]=$this->obj2array($this->data);
		return $r;
			
	}
	function set_data($obj){
		if(!$obj){
			return false;
		}
		if(!is_object($obj)){
			return false;
		}
		$this->data=$obj;
		
	}
	final function set_index($index){
		$this->index=$index;
	}
	final function set_msg($msg){
		$this->msg=$msg;
		$this->set_resultset($msg->resultset);
	}
	final function set_resultset($resultset){
		$this->resultset=$resultset;
		$this->set_conn($resultset->conn);
	}
	final function set_conn($conn){
		$this->conn=$conn;
	}
	function obj2array($obj){
		return $this->resultset->obj2array($obj);
	}
	final function __get_priv_resultset(){
		return $this->resultset; 	
	}
	final function __get_priv_conn(){
		return $this->conn; 	
	}
	final function __get_priv_msg(){
		return $this->msg; 	
	}
	final function __get_priv_index(){
		return $this->index; 	
	}
	function get_imap_stream(){
		return $this->conn->get_imap_stream();	
	}

	
}
?>