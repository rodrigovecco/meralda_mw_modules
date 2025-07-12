<?php
class mwmod_mw_tempitems_item extends mwmod_mw_manager_item{
	function __construct($tblitem,$man){
		$this->init($tblitem,$man);	
		$this->enable_jsondata();
		$this->enable_strdata();
	}

	
}
?>