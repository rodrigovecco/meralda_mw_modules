<?php
class mwmod_mw_data_session_item extends mw_baseobj{
	//sólo debe ser creado por mw_baseobjects_treedata
	private $mainman;
	
	private $cod;
	function __construct($mainman,$cod){
		$this->init($mainman,$cod);	
	}
	function getItem($cod,$subcode=false){
		$c=$cod;
		if($subcode){
			$c.=".".$subcode;	
		}
		return $this->get_sub_item($c);
	}
	function set_data($data,$key=false){
		if(!$r=$this->get_real_key($key)){
			return false;	
		}
		return $this->mainman->set_data($data,$r);
	}
	function unset_data($key=false){
		if(!$r=$this->get_real_key($key)){
			return false;	
		}
		return $this->mainman->unset_subdata($r);
	}
	function get_real_key($key=false){
		if($key===false){
			return $this->cod;	
		}
		if(!$key){
			return false;	
		}
		if(!is_string($key)){
			if(!is_numeric($key)){
				return false;
			}
		}
		return $this->cod.".".$key;	
	}
	
	function get_data($key=false){
		if(!$r=$this->get_real_key($key)){
			return NULL;	
		}
		return $this->mainman->get_data($r);
	}
	function get_sub_item($key){
		if(!$key){
			return false;	
		}
		if(!is_string($key)){
			if(!is_numeric($key)){
				return false;
			}
		}
		$item=new mwmod_mw_data_session_subitem($key,$this);
		return $item;
	
	}
	
	final function init($mainman,$cod){
		$this->cod=$cod;
		$this->mainman=$mainman;
	}
	final function __get_priv_mainman(){
		return $this->mainman;	
	}
	final function __get_priv_cod(){
		return $this->cod;	
	}

}


?>