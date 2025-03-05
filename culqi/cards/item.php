<?php
class  mwmod_mw_culqi_cards_item extends mwmod_mw_manager_item{
	function __construct($tblitem,$man){
		$this->init($tblitem,$man);	
	}
	function isEnabled(){
		if($this->get_data("deleted")){
			return false;	
		}
		return true;
	}
	
	
}
?>