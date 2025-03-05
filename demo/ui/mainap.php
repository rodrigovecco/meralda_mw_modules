<?php
class mwmod_mw_demo_ui_mainap extends mwmod_mw_demo_ui_abs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title("Aplicación");
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		echo "Aplicación principal";

		
	}
	
}
?>