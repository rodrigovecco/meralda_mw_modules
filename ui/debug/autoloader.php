<?php
class mwmod_mw_ui_debug_autoloader extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Autoloader");
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		//$client = new Google_Client(array('client_id' => "sss"));
		//echo get_class($client); 
		$ap= mw_get_main_ap();
		mw_array2list_echo($ap->get_debug_info(),"AP");
		$man= mw_get_autoload_manager();
		mw_array2list_echo($man->get_debug_info(),"AUTOLOADER");
		//phpinfo();
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>