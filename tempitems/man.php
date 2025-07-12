<?php
//todo: cron delete expired items
class mwmod_mw_tempitems_man extends mwmod_mw_manager_man{
	function __construct($mainap,$code="temp_items"){
		$this->init($code,$mainap,$code);	
	
	}
	function create_item($tblitem){
		$item=new mwmod_mw_tempitems_item($tblitem,$this);
		return $item;
	}

}
?>