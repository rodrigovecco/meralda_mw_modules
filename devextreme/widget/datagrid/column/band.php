<?php
class mwmod_mw_devextreme_widget_datagrid_column_band extends mwmod_mw_devextreme_widget_datagrid_column{
	function __construct($cod,$lbl=false){
		$this->init_column($cod,false,$lbl);
		$this->mw_js_colum_class="mw_devextreme_datagrid_column_band";
		$this->setDefOptions();
	}
	function setDefOptions(){
		$this->get_mw_js_colum_obj_params();
		$columns=$this->js_data->get_array_prop("columns");
		$this->js_data->set_prop("isBand",true);
		
	
	}
	function addCol($col){
		$col->js_data->set_prop("ownerBand",$this->index);
		
	}
	

	
}
?>