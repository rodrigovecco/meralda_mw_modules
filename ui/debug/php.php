<?php
class mwmod_mw_ui_debug_php extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("PHP");
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		echo date("Y-m-d H:i:s")."<br>";
		phpinfo();
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>