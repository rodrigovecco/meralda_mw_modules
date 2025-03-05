<?php
class mwmod_mw_ui_debug_debugasmain extends mwmod_mw_bootstrap_ui_main{
	function __construct($ap){
		$this->set_mainap($ap);	
		
		$this->url_base_path="/debug/";
		$this->subinterface_def_code="uidebug";
		$this->set_lngmsgsmancod("debug");
	}
	
	function add_mnu_items_side($mnu){
		$this->add_sub_interface_to_mnu_by_code($mnu,"uidebug");
	}
	

	
	
	function get_ui_title_for_nav(){
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return $this->get_page_title();	
		}

		return $this->get_page_title()." - ".$msg_man->get_msg_txt("tests","Pruebas");	
	}
	
	function load_all_subinterfases(){
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_uidebug("uidebug",$this));
	}
	/*

	function admin_user_ok(){
		return true;
	}
	*/
	function is_allowed(){
		return $this->allow("debug");	
	}
	
	
}
?>