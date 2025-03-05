<?php
class mwmod_mw_paymentapi_debugui_debugdata extends mwmod_mw_paymentapi_debugui_abs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Debug data");
		
	}
	function do_exec_page_in(){
		//echo get_class($this->parent_subinterface->paymentModulesMan);
		
		if(!$man=$this->paymentModulesMan){
			echo "No modules man";
			return false;
		}
		mw_array2list_echo($man->get_debug_data());
		
		
		
	}
}
?>