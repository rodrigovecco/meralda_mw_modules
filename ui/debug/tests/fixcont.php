<?php
class mwmod_mw_ui_debug_tests_fixcont extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		
		$this->set_def_title("Contenido fijo");
		
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		$man=$this->mainap->get_submanager("fixcontent");
		$sub=$man->getSubMan("test1");
		$sub=$man->getSubMan("test1/aaa");
		$sub=$man->getSubMan("/test1/aaa");
		$sub=$man->getSubMan("/test1/aaa/");
		$sub=$man->getSubMan("test1/aaa/");
		
		echo $man->getContent("hello.html");
		
		
		mw_array2list_echo($man->get_debug_data());

		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	
}
?>