<?php
class mwmod_mw_ui_debug_cfg extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Configuración");
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		if(!$this->mainap->cfg){
			return false;	
		}
		mw_array2list_echo($this->mainap->cfg->get_debug_data());
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>