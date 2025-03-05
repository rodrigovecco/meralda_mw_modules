<?php
abstract class mwmod_mw_modulesman_ui_abs extends mwmod_mw_ui_sub_uiabs{
	private $modulesman;
	function initui($cod,$maininterface){
		$this->init_as_subinterface($cod,$maininterface);
		$this->set_lngmsgsmancod("modulesman");
		
	}
	function is_responsable_for_sub_interface_mnu(){
		return false;	
	}
	

	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		echo "...";

		
	}
	final function __get_priv_modulesman(){
		if(!isset($this->modulesman)){
			$this->modulesman=$this->mainap->get_submanager("modulesman");
		}
		
		return $this->modulesman; 	
	}
	
	function is_allowed(){
		return $this->allow("debug");	
	}
	
}
?>