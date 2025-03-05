<?php
class mwmod_mw_demo_ui extends mwmod_mw_ui_base_basesubui{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title($this->lng_get_msg_txt("demo","Demo"));
		$this->sucods="inputs,icons,extmods";

		
	}
	function allowcreatesubinterfacechildbycode(){
		
		return true;	
	}

	
	function _do_create_subinterface_child_extmods($cod){
		$ui=new mwmod_mw_demo_ui_extmods($cod,$this);
		return $ui;	
	}
	function _do_create_subinterface_child_icons($cod){
		$ui=new mwmod_mw_demo_ui_icons($cod,$this);
		return $ui;	
	}
	function _do_create_subinterface_child_inputs($cod){
		$ui=new mwmod_mw_demo_ui_inputs($cod,$this);
		return $ui;	
	}
	function _do_create_subinterface_child_qr($cod){
		$ui=new mwmod_mw_demo_ui_qr($cod,$this);
		return $ui;	
	}
	function _do_create_subinterface_child_mainap($cod){
		$ui=new mwmod_mw_demo_ui_mainap($cod,$this);
		return $ui;	
	}
	function _do_create_subinterface_child_data($cod){
		$ui=new mwmod_mw_demo_ui_data($cod,$this);
		return $ui;	
	}



	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		
		
		return $mnu;
	}
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	
	function add_mnu_items_side($mnu){
		
		
		$mnuitem=new mwmod_mw_mnu_items_dropdown1($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$mnu);
		$this->prepare_mnu_item($mnuitem);
		$mnu->add_item_by_item($mnuitem);
	}
	function prepare_mnu_item($item){
		$item->addInnerHTML_icon("glyphicon glyphicon-star");
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		echo "<p>This is a Demo UI</p>";

		
	}
	function is_allowed(){
		return $this->allow("admin");
	}
	
}
?>