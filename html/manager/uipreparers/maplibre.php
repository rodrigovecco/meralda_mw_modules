<?php
class mwmod_mw_html_manager_uipreparers_maplibre extends mwmod_mw_html_manager_uipreparers_abs{
	function __construct($ui=false){
		$this->set_ui($ui);
	}
	
	function add_js($ui=false){
		if(!$jsman=$this->get_js_man($ui)){
			return false;	
		}

		$item= new mwmod_mw_html_manager_item_jsexternal("maplibre","https://unpkg.com/maplibre-gl@^4.7.1/dist/maplibre-gl.js");
		$jsman->add_item_by_item($item);
		

	}
	function add_css($ui=false){
		if(!$cssman=$this->get_css_man($ui)){
			return false;	
		}

		$item= new mwmod_mw_html_manager_css("maplibre","https://unpkg.com/maplibre-gl@^4.7.1/dist/maplibre-gl.css");
		$cssman->add_item_by_item($item);
		

	}
}
?>