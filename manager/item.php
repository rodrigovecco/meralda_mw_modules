<?php
class  mwmod_mw_manager_item extends mwmod_mw_manager_itemabs{
	function __construct($tblitem,$man){
		$this->init($tblitem,$man);	
	}
	final function __get_items_path(){
		if(!$p=$this->man->__get_items_path()){
			return false;	
		}
		if(!$id=$this->get_id()){
			return false;	
		}
		return $p."/".$id;
	}
	
}


?>