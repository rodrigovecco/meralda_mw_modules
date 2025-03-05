<?php
class mwmod_mw_html_manager_uipreparers_ajax extends mwmod_mw_html_manager_uipreparers_abs{
	function __construct($ui=false){
		$this->set_ui($ui);
	}
	
	function add_js($ui=false){
		if(!$jsman=$this->get_js_man($ui)){
			return false;	
		}
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");

	}
	function add_css($ui=false){
	}
}
?>