<?php
class mwmod_mw_addon_schev_ui_selitem extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->set_items_man($parent->items_man);
		
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("view_scheduled_task","Ver tarea programada"));
		$this->subinterface_def_code="edit";
		
	}
	function get_html_for_parent_chain_on_child_title(){
		return false;	
	}
	
	function do_exec_page_in(){
		echo "";	
	}
	function load_all_subinterfases(){
		
		$si=$this->add_new_subinterface(new mwmod_mw_addon_schev_ui_edit("edit",$this));
		
	}

	function is_allowed(){
		if($this->items_man){
			return $this->items_man->is_allowed_ui($this);	
		}
		return false;
	}

}
?>