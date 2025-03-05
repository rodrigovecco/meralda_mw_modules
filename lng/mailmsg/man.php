<?php
class  mwmod_mw_lng_mailmsg_man extends mw_apsubbaseobj{
	
	private $lngman;
	
	private $_items=array();
	
	function __construct($lngman){
		
		$this->init($lngman);	
	}
	
	
	function create_item($cod){
		if(!$cod=$this->check_item_cod($cod)){
			return false;
		}
		$item= new mwmod_mw_lng_mailmsg_item($cod,$this);
		return $item;
			
	}
	function get_item_files_subpath($item){
		if(!$c=basename($item->cod)){
			return false;	
		}
		return "mailmsgs/$c";	
	}
	
	final function get_item($cod){
		if(!$cod=$this->check_item_cod($cod)){
			return false;
		}
		if($this->_items[$cod]??null){
			return 	$this->_items[$cod];
		}
		if($item=$this->create_item($cod)){
			$this->_items[$cod]=$item;
			return 	$this->_items[$cod];
		}
	}
	function check_item_cod($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		return basename($cod);
	}
	
	final function __get_priv_lngman(){
		return $this->lngman; 	
	}

	final function init($lngman){
		$this->lngman=$lngman;
		
		$this->set_mainap($lngman->mainap);	
	}

}
?>