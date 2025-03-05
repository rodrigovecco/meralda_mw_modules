<?php
class mwmod_mw_devextreme_formatter extends mw_apsubbaseobj{
	
	function __construct(){
			
	}
	function format_datagrid($widget){
		$widget->js_props->set_prop("columnAutoWidth",true);
		$widget->js_props->set_prop("allowColumnResizing",true);
	}

}
?>