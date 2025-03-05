<?php
class mwmod_mw_bruteforce_ui_activityui extends mwmod_mw_ui_base_dxtbladmin{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		
		$this->set_def_title($this->lng_get_msg_txt("activity","Actividad"));
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
		$col=$datagrid->add_column_string("last_username_attempted",$this->lng_get_msg_txt("last_username_attempted","Último usuario"));
		$col=$datagrid->add_column_date("last_attempt",$this->lng_get_msg_txt("last_attempt","Último intento"));
		$col->js_data->set_prop("dataType","datetime");
		$col=$datagrid->add_column_date("lock_until",$this->lng_get_msg_txt("lock_until","Próxima fecha permitida"));
		$col->js_data->set_prop("dataType","datetime");



		


		$col=$datagrid->add_column_number("failed_attempts",$this->lng_get_msg_txt("historical_failed","Intentos fallidos"));
		$col=$datagrid->add_column_number("historical_failed_attempts",$this->lng_get_msg_txt("historical_failed_attempts","Intentos fallidos históricos"));
		$col=$datagrid->add_column_number("historical_successful_attempts",$this->lng_get_msg_txt("historical_successful_attempts","Intentos exitosos históricos"));
		
		
		
		
			
	}
	function allowInsert(){
		return false;
	}
	function allowUpdate(){
		return false;
	}
	function load_items_man(){
		$man=$this->mainap->get_submanager("bruteforce");
		return $man->activity;
	}

	
	
}
?>