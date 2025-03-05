<?php
abstract class mwmod_mw_mail_imap_result_abs extends mw_apsubbaseobj{
	private $conn;
	private $_result_ok;
	var $mailbox_info;
	var $raw_result;
	final function set_conn($conn){
		$this->conn=$conn;
	}
	function get_debug_info(){
		$r=array();
		$r["ok"]=$this->result_ok();
		$r["mailbox_info"]=$this->obj2array($this->mailbox_info);
		return $r;
			
	}
	function obj2array($obj){
		if($obj){
			if((is_object($obj))or(is_array($obj))){
				$r=array();
				foreach($obj as $k=>$v){
					if(is_string($v)){
						$vv=iconv_mime_decode($v,0,'UTF-8');
						//$vv=imap_utf8($v);
						//$vv=($v);
					}elseif((is_object($v))or(is_array($v))){
						$vv=$this->obj2array($v);
					}else{
						$vv=$v;	
					}
					$r[$k]=$vv;	
				}
				return $r;
			}
		}
	}
	function load_result(){
		//extender
		return false;	
	}
	function get_imap_stream(){
		return $this->conn->get_imap_stream();	
	}
	final function init_result(){
		if(!isset($this->_result_ok)){
			if($this->load_result()){
				$this->_result_ok=true;	
			}else{
				$this->_result_ok=false;	
			}
		}
		return $this->_result_ok;
	}
	final function result_ok(){
		$this->init_result();
		return $this->_result_ok;	
	}
	
	final function __get_priv_conn(){
		return $this->conn; 	
	}

	
}
?>