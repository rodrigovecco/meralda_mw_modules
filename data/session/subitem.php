<?php
class mwmod_mw_data_session_subitem extends mw_baseobj{
	private $mainitem;
	private $cod;

	function __construct($cod,$mainitem){
		$this->init($cod,$mainitem);	
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
		return $this->mainitem->set_data($data,$r);
	}
	function unset_data($key=false){
		if(!$r=$this->get_real_key($key)){
			return false;	
		}
		return $this->mainitem->unset_data($r);
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
		return $this->mainitem->get_data($r);
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
		if(!$r=$this->get_real_key($key)){
			return NULL;	
		}
		return $this->mainitem->get_sub_item($r);
	
	}
	
	
	final function init($cod,$mainitem){
		$this->mainitem=$mainitem;	
		$this->cod=$cod;	
	}
	final function __get_priv_mainitem(){
		return $this->mainitem;	
	}
	final function __get_priv_cod(){
		return $this->cod;	
	}
}

?>