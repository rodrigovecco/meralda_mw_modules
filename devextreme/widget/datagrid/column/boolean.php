<?php
class mwmod_mw_devextreme_widget_datagrid_column_boolean extends mwmod_mw_devextreme_widget_datagrid_column{
	function __construct($cod,$lbl=false){
		$this->init_column($cod,"boolean",$lbl);
		$this->js_data->set_prop("trueText",$this->lng_common_get_msg_txt("yes","Sí"));	
		$this->js_data->set_prop("falseText",$this->lng_common_get_msg_txt("no","No"));	
	}
	function set_dataoptim_field($field){
		$field->boolean_mode();	
	}
	
	function get_value($val){
		if($val){
			return true;	
		}
		return false;	
	}
	
	
	
}
?>