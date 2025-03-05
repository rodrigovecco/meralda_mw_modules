<?php
class mwmod_mw_ui_debug_tests extends mwmod_mw_ui_sub_uiabs{
	var $subui_codes_for_mnu="";
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Pruebas");
		$this->subui_codes_for_mnu="data,dbextradata,icons,fixcont,output,crop";
		//$this->subui_codes_for_mnu="data";
		$this->subinterface_def_code="data";
		$this->addSubUIClass("data","mwmod_mw_ui_debug_tests_data");
		$this->addSubUIClass("dbextradata","mwmod_mw_ui_debug_tests_dbextradata");
		$this->addSubUIClass("icons","mwmod_mw_ui_debug_tests_icons");
		$this->addSubUIClass("fixcont","mwmod_mw_ui_debug_tests_fixcont");
		$this->addSubUIClass("output","mwmod_mw_ui_debug_tests_output");
		$this->addSubUIClass("crop","mwmod_mw_ui_debug_tests_crop");
		//$this->addSubUIClass("pdf","mwmod_mw_ui_debug_tests_pdf");
		
		
	}
	function _do_create_subinterface_child_output($cod){
		$ui=new mwmod_mw_ui_debug_tests_output($cod,$this);
		return $ui;	
	}
	function _do_create_subinterface_child_data($cod){
		$ui=new mwmod_mw_ui_debug_tests_data($cod,$this);
		return $ui;	
	}
	
	function add_2_sub_interface_mnu($mnu){
		//$item=new mwmod_mw_mnu_items_dropdown_single($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$mnu,$this->get_url());
		//$mnu->add_item_by_item($item);
		if($subs=$this->get_subinterfaces_by_code($this->subui_codes_for_mnu,true)){
			$item=new mwmod_mw_mnu_items_dropdown_single($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$mnu,$this->get_url());
			$mnu->add_item_by_item($item);
			foreach($subs as $su){
				$sitem=new mwmod_mw_mnu_mnuitem($su->get_cod_for_mnu(),$su->get_mnu_lbl(),$item,$su->get_url());
				$item->add_item_by_item($sitem);
				if($su->is_current()){
					$sitem->active=true;	
				}
			}
		}
		return $mnu;

	}
	function allowcreatesubinterfacechildbycode(){
		return true;	
	}

	function do_exec_page_in(){
		echo "...";
		
	}
	function do_exec_no_sub_interface(){
	}

	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>