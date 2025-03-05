<?php
class mwmod_mw_devextreme_widget_datagrid_column_html extends mwmod_mw_devextreme_widget_datagrid_column{
	function __construct($cod,$lbl=false){
		$this->init_column($cod,"string",$lbl);
		$this->set_cell_template_html();
	}
	function set_cell_template_html(){
		$js="
		$('<div>'+options.value+'</div>')
                    .appendTo(container);
					";
					
		$fnc=new mwmod_mw_jsobj_function($js);
		$fnc->add_fnc_arg("container");
		$fnc->add_fnc_arg("options");
		$this->js_data->set_prop("cellTemplate",$fnc);	
	}
	
	
	
}
?>