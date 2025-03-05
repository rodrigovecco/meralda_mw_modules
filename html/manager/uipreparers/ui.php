<?php
class mwmod_mw_html_manager_uipreparers_ui extends mwmod_mw_html_manager_uipreparers_abs{
	function __construct($ui=false){
		$this->set_ui($ui);
	}
	
	function add_js($ui=false){
		if(!$jsman=$this->get_js_man($ui)){
			return false;	
		}
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("util.js");
		$jsman->add_item_by_cod_def_path("arraylist.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("validator.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("ui/mwuihelpers.js");
		$jsman->add_item_by_cod_def_path("inputs/inputs.js");
		$jsman->add_item_by_cod_def_path("inputs/elemschoise.js");
		$jsman->add_item_by_cod_def_path("inputs/minutes.js");
		$jsman->add_item_by_cod_def_path("inputs/other.js");
		$jsman->add_item_by_cod_def_path("inputs/frm.js");
		$jsman->add_item_by_cod_def_path("inputs/container.js");
		
		
		
		$jsman->add_jquery();
		
		$jsman->add_globalize();

	}
	function add_css($ui=false){
	}
}
?>