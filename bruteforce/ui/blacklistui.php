<?php
class mwmod_mw_bruteforce_ui_blacklistui extends mwmod_mw_ui_base_dxtbladmin{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		
		$this->set_def_title($this->lng_get_msg_txt("blackList","Lista negra"));
		$this->js_ui_class_name="mw_ui_grid_remote";
		$this->editingMode="cell";

		
	}
	function getTopHtml($container){
		
	}
	function add_cols($datagrid){
		$col=$datagrid->add_column_number("id","ID");
		$col->js_data->set_prop("width",60);
		$col->js_data->set_prop("allowEditing",false);
		$col->js_data->set_prop("visible",false);
		$col=$datagrid->add_column_string("ip_address","IP");
		$col=$datagrid->add_column_string("reason",$this->lng_get_msg_txt("reason","Razón"));
		$col=$datagrid->add_column_date("banned_on",$this->lng_get_msg_txt("date","Fecha"));
		$col->js_data->set_prop("dataType","datetime");
		$col->js_data->set_prop("allowEditing",false);

		
		
			
	}
	function load_items_man(){
		$man=$this->mainap->get_submanager("bruteforce");
		return $man->blacklist;
	}

	
	
}
?>