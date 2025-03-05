<?php
class mwmod_mw_devextreme_widget_datagrid_column_booleantxt extends mwmod_mw_devextreme_widget_datagrid_column{
	function __construct($cod,$lbl=false){
		$this->init_column($cod,"string",$lbl);
		$this->yes_no_lookup();
		$this->js_data->set_prop("alignment","center");	
		//$this->js_data->set_prop("falseText",$this->lng_common_get_msg_txt("no","No"));	
	}
	function set_dataoptim_field($field){
		$field->boolean_mode();	
	}
	function yes_no_lookup(){
		$lookup=$this->set_lookup("cod","txt");	
		$lookup->add_data(array("cod"=>"yes","txt"=>$this->lng_common_get_msg_txt("yes","Sí")));	
		$lookup->add_data(array("cod"=>"no","txt"=>$this->lng_common_get_msg_txt("no","No")));	
	}
	function get_value($val){
		if($val){
			return "yes";	
		}
		return "no";	
	}
	
	
	
}
?>