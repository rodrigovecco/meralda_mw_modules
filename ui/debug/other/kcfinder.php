<?php
class mwmod_mw_ui_debug_other_kcfinder extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->get_msg("KCFinder"));
		$this->js_ui_class_name="mw_ui_debug_test";
		
	}
	function do_exec_no_sub_interface(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		/*
		$util=new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->add_jquery_ui();
		$util->add_js_item_by_cod_def_path("url.js");
		$util->add_js_item_by_cod_def_path("ajax.js");
		$util->add_js_item_by_cod_def_path("mw_objcol.js");
		$util->add_js_item_by_cod_def_path("ui/mwui.js");
		$util->add_js_item_by_cod_def_path("ui/mwui_grid.js");
		
		$util->add_js_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		*/
		$item=new mwmod_mw_html_manager_item_jsexternal("mw_test_ui","/res/debug/mw_test_ui.js");
		$util->add_js_item($item);
		
		
		/*


		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		*/
		
	}
	
	function do_exec_page_in(){
		echo "ddd";
	}

	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>