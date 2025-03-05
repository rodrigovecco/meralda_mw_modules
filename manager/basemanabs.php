<?php

abstract class  mwmod_mw_manager_basemanabs extends mw_apsubbaseobj{
	private $code;
	private $_treedataman;
	private $jsonDataMan;
	private $_strdataman;
	
	private $_can_create_strdata=false;
	private $_can_create_treedata=false;
	private $_can_create_jsondata=false;
	/*
	final function init($code,$ap){
		$this->code=basename($code);
		$this->set_mainap($ap);	
	}
	*/
	final function setManCode($code){
		$this->code=basename($code);

	}
	final function enable_strdata($val=true){
		$this->_can_create_strdata=$val;
	}
	final function enable_treedata($val=true){
		$this->_can_create_treedata=$val;
	}
	final function enable_jsondata($val=true){
		$this->_can_create_jsondata=$val;
	}
	function getJsonDataItem($code="data",$path=false){
		if($m=$this->__get_priv_jsonDataMan()){
			return $m->get_datamanager($code,$path);	
		}
	}
	
	function createJsonDataMan(){
		if(!$this->jsonDataManEnabled()){
			return false;
		}
		if(!$p=$this->__get_man_path()){
			return false;	
		}
		$path=$p."/data";
		$man=new mwmod_mw_data_json_man($path);
		return $man;
	}
	final function jsonDataManEnabled(){
		return $this->_can_create_jsondata;
	}

	final function __get_priv_jsonDataMan(){
		if(!isset($this->jsonDataMan)){
			if(!$this->jsonDataMan=$this->createJsonDataMan()){
				$this->jsonDataMan=false;
			}
		}
		return $this->jsonDataMan;
	}
	function get_public_url_path(){
		if(!$code=basename($this->code)){
			return false;
		}
		if(!$p=$this->mainap->get_public_userfiles_url_path()){
			return false;	
		}
		$p.="/man/".$code;
		return $p;
		
	}
	
	function get_strdata_item($code="data",$path=false){
		if($m=$this->get_strdataman()){
			return $m->get_datamanager($code,$path);	
		}
	}
	final function get_strdataman(){
		if(isset($this->_strdataman)){
			return 	$this->_strdataman;
		}
		if($m=$this->get_init_strdataman()){
			$this->_strdataman=$m;
			return 	$this->_strdataman;
		}
	}
	final function can_create_strdata(){
		
		return $this->_can_create_strdata;	
	}
	function get_init_strdataman(){
		if(!$this->can_create_strdata()){
			return false;		
		}
		if(!$p=$this->get_strdata_path()){
			return false;		
		}
		
		$m= new mwmod_mw_data_str_man($p);
		return $m;
	}
	function get_strdata_path(){
		if(!$p=$this->__get_man_path()){
			return false;	
		}
		
		return $p."/str";	
	}
	
	function get_treedata_item($code="data",$path=false){
		if($m=$this->get_treedataman()){
			return $m->get_datamanager($code,$path);	
		}
	}
	
	final function can_create_treedata(){
		return $this->_can_create_treedata;	
	}

	final function get_treedataman(){
		if(isset($this->_treedataman)){
			return 	$this->_treedataman;
		}
		if($m=$this->get_init_treedataman()){
			$this->_treedataman=$m;
			return 	$this->_treedataman;
		}
	}
	function get_init_treedataman(){
		if(!$this->can_create_treedata()){
			return false;		
		}
		if(!$p=$this->get_treedata_path()){
			return false;		
		}
		$m= new mwmod_mw_data_tree_man($p);
		return $m;
	}
	function get_treedata_path(){
		if(!$p=$this->__get_man_path()){
			return false;	
		}
		
		return $p."/data";	
	}

	
	
	
	function __get_man_path(){
		if(!$this->code){
			return false;
		}
		if(!$code=basename($this->code)){
			return false;
		}
		$p="man/".$code;
		return $p;
			
	}
	final function ___get_path($subpath=false,$public=true){
		if($public){
			$mode="userfilespublic";		
		}else{
			$mode="userfiles";	
		}
		if(!$code=basename($this->code)){
			return false;
		}
		$p=$code;
		if($subpath){
			$p.="/".$subpath;	
		}
		
		return $this->mainap->get_sub_path($p,$mode);
			
	}

	

	final function __get_priv_code(){
		return $this->code; 	
	}
	function __call($a,$b){
		return false;	
	}

}
?>