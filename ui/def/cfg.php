<?php
class mwmod_mw_ui_def_cfg extends mwmod_mw_ui_base_basesubuia{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		
		$this->set_def_title($this->lng_get_msg_txt("configuration","Configuración"));
		$this->sucods="bruteforce";

		
	}

	
	
	function _do_create_subinterface_child_bruteforce($cod){
		$ui=new mwmod_mw_bruteforce_ui_main($cod,$this);
		return $ui;	
	}
	


	
	
	
	function prepare_mnu_item($item){
		$item->addInnerHTML_icon("fa fa-cogs");
	}
	
	function do_exec_no_sub_interface(){
	}
	
	function is_allowed(){
		return $this->allow("admin");
	}
	
}
?>