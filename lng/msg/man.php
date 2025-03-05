<?php
class  mwmod_mw_lng_msg_man extends mw_apsubbaseobj{
	private $cod;
	private $man;
	private $_def_man;
	private $_mans_for_lngs=array();
	
	function __construct($cod,$man){
		$this->init($cod,$man);	
	}
	function re_write_def_msgs_mans_if_needed(){
		//debería ser llamado el morir php si esta en modo programacaion
		if(!$def=$this->get_def_msgs_man()){
			return false;	
		}
		$def->re_write_msgs_if_needed();
	}
	
	function get_msg_txt($cod,$def=false,$params=false){
		if($item=$this->get_msg_item($cod,$def)){
			return $item->get_msg_txt($params);	
		}
		return $def;
	}
	function get_current_lng_man(){
		return $this->man->get_current_lng_man();	
	}
	final function get_man_for_lng($lng){
		if(!$lng){
			return false;	
		}
		$cod=$lng->code;
		if(!isset($this->_mans_for_lngs[$cod])or(!$this->_mans_for_lngs[$cod])){
			$this->_mans_for_lngs[$cod]= new mwmod_mw_lng_msg_bylngman($lng,$this);	
		}
		return $this->_mans_for_lngs[$cod];
	}

	function get_current_lng_msg_item($cod,$def=false){
		if(!$lng=$this->get_current_lng_man()){
			return false;	
		}
		if(!$man=$this->get_man_for_lng($lng)){
			return false;	
		}
		return $man->get_item($cod);

	}
	function get_msg_item($cod,$def=false){
		if($item=$this->get_current_lng_msg_item($cod,$def)){
			return $item;	
		}
		return $this->get_def_msg_item($cod,$def);
			
	}
	function get_def_msg_item($cod,$def=false){
		$man=$this->get_def_msgs_man();
		return $man->get_or_create_item($cod,$def);
			
	}

	final function get_def_msgs_man(){
		if($this->_def_man){
			return 	$this->_def_man;
		}
		$this->_def_man= new mwmod_mw_lng_msg_lngdefman($this);
		return 	$this->_def_man;
	}
	final function __get_priv_cod(){
		return $this->cod; 	
	}
	final function __get_priv_man(){
		return $this->man; 	
	}

	final function init($cod,$man){
		$ap=$man->mainap;
		$this->man=$man;
		$this->cod=basename($cod);
		$this->set_mainap($ap);	
		
	}

}
?>