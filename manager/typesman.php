<?php

abstract class  mwmod_mw_manager_typesman extends mw_apsubbaseobj{
	private $_types;
	private $man;
	
	function create_types(){
		return false;
		//extender
			
	}
	final function init($man){
		$this->man=$man;
		//$this->set_lngmsgsmancod("mctasks");
		//$this->set_mainap();
		
	}
	
	
	final function get_type($cod){
		if(!$cod){
			return false;	
		}
		$this->init_types();
		return $this->_types[$cod];	
	}
	final function get_types(){
		$this->init_types();
		return $this->_types;	
	}
	final function init_types(){
		if(isset($this->_types)){
			return;	
		}
		$this->_types=array();
		if(!$items=$this->create_types()){
			return;	
		}
		foreach($items as $type){
			if($type->cod){
				$this->_types[$type->cod]=$type;
			}
		}
	}
	final function __get_priv_man(){
		return $this->man; 	
	}


}
?>