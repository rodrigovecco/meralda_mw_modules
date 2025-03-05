<?php
class mwmod_mw_devextreme_widget_datagrid_column_date extends mwmod_mw_devextreme_widget_datagrid_column{
	function __construct($cod,$lbl=false){
		$this->init_column($cod,"date",$lbl);
	}
	
	function set_dataoptim_field($field){
		$field->date_mode();	
	}
	
	
}
?>