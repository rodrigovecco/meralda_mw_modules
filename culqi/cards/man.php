<?php
class  mwmod_mw_culqi_cards_man extends mwmod_mw_manager_man{
	function __construct($salesman,$tblcod="culqi_cards"){
		$this->init_from_mainman($tblcod,$salesman);
	}
	function create_item($tblitem){
		$item=new mwmod_mw_culqi_cards_item($tblitem,$this);
		return $item;
	}

	final function init_from_mainman($tblcod,$salesman){
		$this->init($tblcod,$salesman->mainap);
		$this->salesman=$salesman;
	}
	final function __get_priv_salesman(){
		return $this->salesman;
	}


}
?>