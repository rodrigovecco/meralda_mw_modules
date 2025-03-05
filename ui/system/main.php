<?php
class mwmod_mw_ui_system_main extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_lngmsgsmancod("system");
		$this->set_def_title($this->lng_get_msg_txt("system","Sistema"));
		$this->subinterface_def_code="cronjobs";
		
	}
	function get_html_for_parent_chain_on_child_title(){
		return false;	
	}

	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
	}
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function add_2_side_mnu($mnu,$checkallowed=true){
		if(!$mnu){
			return false;	
		}
		if($checkallowed){
			if(!$this->is_allowed_on_mnu()){
				return false;
			}
		}
		$dontselcurrent=false;	
		
		
		
		if(!$subs=$this->get_subinterfaces_by_code("cronjobs",true)){
			return false;
		}
		if(!sizeof($subs)){
			return false;	
		}
		if($subs){
			$mnuitem=new mwmod_mw_mnu_items_dropdown_side($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$mnu);
			$mnuitem->addInnerHTML_icon("fas fa-fw fa-folder");
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
		$item->addInnerHTML_icon("fa fa-keyboard-o");
	}
	
	
	function load_all_subinterfases(){
		
		$si=$this->add_new_subinterface(new mwmod_mw_ui_system_cronjobs("cronjobs",$this));
		
	}

	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){

		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	
}
?>