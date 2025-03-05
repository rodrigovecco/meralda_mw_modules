<?php
//not used
class mwmod_mw_demo_ui_demo extends mwmod_mw_demo_ui_abs{
	var $subui_codes_for_mnu="";
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title("Demo");
		
		$this->subui_codes_for_mnu="mainap,data";
		$this->subinterface_def_code="mainap";
		$this->addSubUIClass("data","mwmod_mw_demo_ui_data");
		
	}
	function allowcreatesubinterfacechildbycode(){
		return true;	
	}
	function _do_create_subinterface_child_mainap($cod){
		$ui=new mwmod_mw_demo_ui_mainap($cod,$this);
		return $ui;	
	}

	
	function before_exec(){
		///esto se ha hecho ya que sus subinterfaces requieren scripñts que antes se cargaban con todas
		/*
		$p=new mwmod_mw_html_manager_uipreparers_default($this);
		$p->preapare_ui();

		
		$this->add_req_js_scripts();	
		$this->add_req_css();
		*/
	}

	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function add_2_side_mnu($mnu,$checkallowed=true){
		//return false;
		if(!$mnu){
			return false;	
		}
		if($checkallowed){
			
			if(!$this->is_allowed_on_mnu()){
				return false;
			}
			
		}
		$dontselcurrent=false;	
		
		
		
		if(!$subs=$this->get_subinterfaces_by_code($this->subui_codes_for_mnu,true)){
			return false;
		}
		if(!sizeof($subs)){
			return false;	
		}
		if($subs){
			$mnuitem=new mwmod_mw_mnu_items_dropdown_side($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$mnu);
			$mnuitem->addInnerHTML_icon("glyphicon glyphicon-star");
			$mnu->add_item_by_item($mnuitem);
			if(!$dontselcurrent){
				if($this->is_in_exec_chain()){
					$mnuitem->set_active(true);	
				}
			}
			if($this->selected_as_current){
				$mnuitem->set_active(true);	
				
			}
			
			foreach($subs as $su){
				$su->add_as_sub_mnu_item($mnuitem);	
				if($su->is_current()){
					$mnuitem->set_active(true);		
				}
			}
			
			return $mnuitem;
		}
	}
	function prepare_mnu_item($item){
		$item->addInnerHTML_icon("glyphicon glyphicon-star");
	}
	
	

	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		echo "...";

		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	
}
?>