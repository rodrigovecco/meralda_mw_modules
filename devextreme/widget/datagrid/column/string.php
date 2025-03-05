<?php
class mwmod_mw_devextreme_widget_datagrid_column_string extends mwmod_mw_devextreme_widget_datagrid_column{
	function __construct($cod,$lbl=false){
		$this->init_column($cod,"string",$lbl);
	}
	function set_dataoptim_field($field){
		$field->text_mode();	
	}
	
	
	
}
?>