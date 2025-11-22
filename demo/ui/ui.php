<?php
class mwmod_mw_demo_ui_ui extends mwmod_mw_demo_ui_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title($this->lng_get_msg_txt("userInterfaces","Interfaces de usuario"));
		$this->sucods="htmleditor";

		
	}

	function _do_create_subinterface_child_htmleditor($cod){
		$ui=new mwmod_mw_demo_ui_ui_htmleditor($cod,$this);
		return $ui;	
	}
	
	function do_exec_page_in(){
		
		echo "<div class='card'><div class='card-body'>";
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			echo "<ul>";
			foreach($subs as $su){
				echo "<li><a href='".$su->get_url()."'>".$su->get_mnu_lbl()."</a></li>";	
			}
			echo "</ul>";
			
		}
		echo "</div></div>";
		
	}
	function add_2_sub_interface_mnu($mnu){
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
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

	}
	function allowcreatesubinterfacechildbycode(){
		return true;	
	}
	
	
}
?>