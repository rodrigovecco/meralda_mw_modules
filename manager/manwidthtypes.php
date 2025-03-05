<?php

abstract class  mwmod_mw_manager_manwidthtypes extends mwmod_mw_manager_man{
	private $_types;
	function create_types(){
		return false;
		//extender
			
	}
	
	function create_item($tblitem){
		if(!$tcod=$tblitem->get_data("type")){
			return false;
		}
		if(!$type=$this->get_type($tcod)){
			return false;	
		}
		return $type->create_item($tblitem);
	}

	
	function validate_new_item_data(&$data){
		if(!is_array($data)){
			return false;
		}
		if(!$type=$this->get_type($data["type"]??null)){
			return false;	
		}
		$data["type"]=$type->cod;
		return $this->validate_new_item_data_sub($data);
		
		
	}

	
	final function get_type($cod){
		if(!$cod){
			return false;	
		}
		$this->init_types();
		return $this->_types[$cod]??null;	
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


}
?>