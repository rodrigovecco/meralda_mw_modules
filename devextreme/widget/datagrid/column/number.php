<?php
class mwmod_mw_devextreme_widget_datagrid_column_number extends mwmod_mw_devextreme_widget_datagrid_column{
	function __construct($cod,$lbl=false){
		$this->init_column($cod,"number",$lbl);
	}
	function set_dataoptim_field($field){
		$field->numeric_mode();	
	}
	
	function get_value($val){
		return $val+0;
	}
	
	
}
?>