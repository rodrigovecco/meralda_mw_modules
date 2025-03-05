<?php
//
class mwmod_mw_bruteforce_activity_item extends mwmod_mw_manager_item{
	function __construct($tblitem,$man){
		$this->init($tblitem,$man);	
		
	}
	function isLocked(){
		if($date=$this->get_data_as_date("lock_until")){
			$t=strtotime($date);
			if($t>time()){
				return $t;
			}
		}
		return false;
	}
	

}
?>