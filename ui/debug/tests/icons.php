<?php
class mwmod_mw_ui_debug_tests_icons extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		
		$this->set_def_title("Iconos");
		$this->js_ui_class_name="mw_ui_grid";
		
	}
	
	function do_exec_page_in(){
		$man=new mwmod_mw_bootstrap_man();
		$icons=$man->getIcons();
		//mw_array2list_echo($icons);
		
		
		
		$jsui=$this->new_ui_js();
		$datagrid=new mwmod_mw_devextreme_widget_datagrid("dgicons");
		$datagrid->setFilerVisible();
		//$datagrid->js_props->set_prop("allowColumnReordering",true);
		//$datagrid->js_props->set_prop("columnChooser.enabled",true);
		//$datagrid->js_props->set_prop("editing.editMode","row");	
		//$datagrid->js_props->set_prop("editing.editEnabled",true);	
		//$datagrid->js_props->set_prop("editing.removeEnabled",false);	
		//$datagrid->js_props->set_prop("editing.insertEnabled",true);
		
		$datagrid->js_props->set_prop("paging.pageSize",100);
		$col=$datagrid->add_column_string("id","ID");
		$col->js_data->set_prop("width",60);
		
		
		
		$col=$datagrid->add_column_string("lib","Librería");
		$col=$datagrid->add_column_string("name","Nombre");
		//$col=$datagrid->add_column_string("cusname","Descripción");
		$col=$datagrid->add_column_string("icon","Icono");
		$fncicon=new mwmod_mw_jsobj_functionext();
		$fncicon->add_fnc_arg("cellElement");
		$fncicon->add_fnc_arg("cellInfo");
		//$fnctogglesel->add_cont("alert('dddd');\n");
		$fncicon->add_cont("$('<span>')\n");
      	$fncicon->add_cont(".addClass(cellInfo.data['iconclass']+'')\n");
        $fncicon->add_cont(".appendTo(cellElement);\n");
		$col->js_data->set_prop("cellTemplate",$fncicon);	
		$col=$datagrid->add_column_string("iconclass","iconclass");
		
		
		
		
		
		$dataoptim=$datagrid->new_dataoptim_data_man();		
		$dataoptim->set_key("id");
		foreach($icons as $cod=>$icon){
			$dataoptim->add_data($icon->get_data_for_dg());	
		}
		
		echo $datagrid->get_html_container();
		$var=$this->get_js_ui_man_name();
		$jsdec=new mwmod_mw_jsobj_codecontainer();
		$jsdec->add_cont("var {$var}=".$jsui->get_as_js_val().";\n");
		
		echo $jsdec->get_js_script_html();
		
		
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		//$jsbefore->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		$jsbefore->add_cont("var ui={$var};\n");
		$gridhelper=$datagrid->new_mw_helper_js();
		$jsbefore->add_cont("\n ui.set_datagrid_man(".$gridhelper->get_as_js_val().");\n\n\n\n");	
		$columns=$datagrid->columns->get_items();
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$jsbefore->add_cont("ui.datagrid_man.add_colum(".$coljs->get_as_js_val().");\n");	
		}
		
		$jsbefore->add_cont("ui.datagrid_man.set_ds_from_optim(".$dataoptim->get_as_js_val().");\n");	
		
		$jsbefore->add_cont("ui.init(".$this->ui_js_init_params->get_as_js_val().");\n");
		
		
		
		$jsbefore->add_cont("ui.set_container();\n");
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont($jsbefore,true);
		
		$jsbefore->add_cont("ui.datagrid_man.create_data_grid();\n");	
		$js->add_cont("ui.set_grid('".$datagrid->container_id."')");
		echo $js->get_js_script_html();
		
		
		
		
		

		
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
	function is_allowed(){
		return $this->allow("debug");	
	}
	
}
?>