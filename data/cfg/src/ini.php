<?php

class mwmod_mw_data_cfg_src_ini extends mwmod_mw_data_cfg_src_abs{
	private $file;
	function __construct($cod,$file){
		$this->set_cod($cod);
		$this->set_file($file);
		
	}
	function get_debug_data_add(&$r){
		$r["file"]=$this->file;
	}
	
	function load_values(){
		if(!$file=$this->file){
			return false;	
		}
		if(!is_string($file)){
			return false;	
		}
		if(@!$values=parse_ini_file ($file)){
			return false;
		}
		return $values;
		
	}
	final function set_file($file){
		$this->file=$file;
	}
	final function __get_priv_file(){
		return $this->file;	
	}
	

}


?>