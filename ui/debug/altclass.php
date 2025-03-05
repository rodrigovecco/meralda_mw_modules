<?php
class mwmod_mw_ui_debug_altclass extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Class alternativos");
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		return false;
		$mainman=$GLOBALS["__mw_autoload_manager"];
		$mainman->keep_reports=true;
		$pman=$mainman->get_pref_man("mwalt");
		if($pman->set_alt("test","modea")){
			echo "ok";	
		}
		
		$o=new mwalt_test_obj();
		$o->test();
		$o=new mwalt_test_sub_other();
		$o->test();
		mw_array2list_echo($mainman->reports);
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>