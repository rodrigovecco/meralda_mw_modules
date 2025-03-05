<?php
class mwmod_mw_ui_debug_db_tests extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Pruebas");
		$this->js_ui_class_name="mw_ui_grid";
		
	}
	function do_exec_no_sub_interface(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		
		
		
	}
	function do_exec_page_in(){
		$dbman=$this->mainap->get_submanager("db");
		mw_array2list_echo($dbman->get_tbl_managers());
		
		$tblman=$dbman->get_tbl_manager("datatest");
		mw_array2list_echo($tblman->get_tbl_fields());
		
		/*
		if($tblitem=$tblman->get_item(15)){
			mw_array2list_echo($tblitem->get_data());
			$tblitem->update(array("name"=>"probando db"));	
		}
		*/
		$query=$tblman->new_query();
		mw_array2list_echo($dbman->query_debug("select * from sss"));
		
		
		//$query->where->add_where_crit("id",15);
		mw_array2list_echo($tblman->get_items_by_sql($query->get_sql()));
		
		//$query->select->add_select("id");
		//$query->select->add_select("name");
		//$query->where->add_where_str_list("name",array("ccccc","ddddd"));
		
		$jsui=$this->new_ui_js();
		$datagrid=new mwmod_mw_devextreme_widget_datagrid("datagridtest");
		$datagrid->setFilerVisible();
		$datagrid->js_props->set_prop("allowColumnReordering",true);
		$datagrid->js_props->set_prop("columnChooser.enabled",true);
		$gridhelper=$datagrid->new_mw_helper_js_from_query($query);
		
		
		
		
		
		
		
		
		echo $datagrid->get_html_container();
		$var=$this->get_js_ui_man_name();
		$jsdec=new mwmod_mw_jsobj_codecontainer();
		$jsdec->add_cont("var {$var}=".$jsui->get_as_js_val().";\n");
		
		echo $jsdec->get_js_script_html();
		
		
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		//$jsbefore->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		$jsbefore->add_cont("var ui={$var};\n");
		
		$jsbefore->add_cont("\n ui.set_datagrid_man(".$gridhelper->get_as_js_val().");\n\n\n\n");	
		$jsbefore->add_cont("ui.init(".$this->ui_js_init_params->get_as_js_val().");\n");
		
		
		
		$jsbefore->add_cont("ui.set_container();\n");
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont($jsbefore,true);
		
		$jsbefore->add_cont("ui.datagrid_man.init_from_params();\n");	
		$jsbefore->add_cont("ui.datagrid_man.create_data_grid();\n");	
		$js->add_cont("ui.set_grid('".$datagrid->container_id."')");
		echo $js->get_js_script_html();

		mw_array2list_echo($query->get_debug_data());
		
		
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
	}
	
}
?>