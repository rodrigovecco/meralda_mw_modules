<?php

class  mwmod_mw_kcfinder_cfgsetter extends mw_apsubbaseobj{
	public $params;
	public $config;
	function __construct($params,&$config){
		$this->init($params,$config);
	}
	function set_upload_url_by_item($item,$subpath=false){
		if(!$pathman=$item->get_sub_path_man($subpath,true)){
			return false;
		}
		if(!$p=$pathman->check_and_create_path()){
			return false;	
		}
		$url=$item->get_public_url_path()."";
		if($subpath){
			$url.=$subpath;	
		}
		$p=str_replace("\\","/",realpath($p));
		$this->set_cfg("uploadURL",$url);
		$this->set_cfg("uploadDir",$p);
		return true;
		
		
	}
	
	
	function set_upload_url_by_rel($subpath){
		if(!$pathman=$this->mainap->get_sub_path_man($subpath,"userfilespublic")){
			return false;
		}
		if(!$p=$pathman->check_and_create_path()){
			return false;	
		}
		$url=$this->mainap->get_public_userfiles_url_path()."/$subpath";
		$this->set_cfg("uploadURL",$url);
		$p=str_replace("\\","/",realpath($p));
		$this->set_cfg("uploadDir",$p);
		return true;
		
		
	}
	function enabled(){
		$this->set_cfg("disabled",false);	
	}
	function set_cfg($cod,$val){
		if(!$cod){
			return false;	
		}
		mw_array_set_sub_key($cod,$val,$this->config);
	}
	function get_cfg($cod=false){
		if(!$cod){
			return $this->config;	
		}
		return mw_array_get_sub_key($this->config,$cod);
	}
	function allow($action,$params=false){
		if($man=$this->mainap->get_user_manager()){
			return $man->allow($action,$params);	
		}
	
	}
	
	final function init($params,&$config){
		$this->params=$params;
		$this->config = &$config;
		$this->set_mainap();	
	}

}
?>