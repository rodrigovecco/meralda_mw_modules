<?php
class mwmod_mw_ui_debug_dx_datagrid extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("DataGrid");
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
		$jsui=$this->new_ui_js();
		$datagrid=new mwmod_mw_devextreme_widget_datagrid("datagridtest");
		$datagrid->setFilerVisible();
		$datagrid->js_props->set_prop("allowColumnReordering",true);
		$datagrid->js_props->set_prop("columnChooser.enabled",true);
		$datagrid->js_props->set_prop("editing.editMode","row");	
		$datagrid->js_props->set_prop("editing.editEnabled",true);	
		$datagrid->js_props->set_prop("editing.removeEnabled",false);	
		$datagrid->js_props->set_prop("editing.insertEnabled",true);
		
		
		$col=$datagrid->add_column_number("id","ID");
		$col->js_data->set_prop("width",60);
		
		$col=$datagrid->add_column_string("s1","Serie 1");
		$col->set_link_mode($this->get_url(),array("iditem"=>"id"));
		$col=$datagrid->add_column_string("s2","Serie 2");
		
		$dataoptim=$datagrid->new_dataoptim_data_man();		
		$dataoptim->set_key("id");
		
		$dataoptim->add_data(array("id"=>1,"s1"=>6,"s2"=>4));	
		$dataoptim->add_data(array("id"=>2,"s1"=>5,"s2"=>2));	
		$dataoptim->add_data(array("id"=>3,"s1"=>2,"s2"=>4));	
		$dataoptim->add_data(array("id"=>4,"s1"=>8,"s2"=>3));	
		$dataoptim->add_data(array("id"=>5,"s1"=>3,"s2"=>4));	
		$dataoptim->add_data(array("id"=>6,"s1"=>5,"s2"=>2));	
		$dataoptim->add_data(array("id"=>7,"s1"=>4,"s2"=>4));	
		
		echo $datagrid->get_html_container();
		$var=$this->get_js_ui_man_name();
		$jsdec=new mwmod_mw_jsobj_codecontainer();
		$jsdec->add_cont("var {$var}=".$jsui->get_as_js_val().";\n");
		$fnctogglesel=new mwmod_mw_jsobj_functionext();
		//$fnctogglesel->add_cont("alert('dddd');\n");
		$fnctogglesel->add_cont("if(!this.datagrid_man){ return false }\n");
		$fnctogglesel->add_cont("var dg=this.datagrid_man.get_data_grid();\n");
		$fnctogglesel->add_cont("if(!dg){ return false }\n");
		$fnctogglesel->add_cont("if(this.selection_on){
			this.selection_on= false;
			dg.option({selection:{mode:'none'}});
			
		}else{
			this.selection_on= true;
			dg.option({selection:{mode:'multiple'}});
		}\n");
		$jsdec->add_cont("{$var}.togglesel=".$fnctogglesel->get_as_js_val().";\n");
		
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
		$btn=new mwmod_mw_bootstrap_html_specialelem_btn("Toggle");
		$btn->set_att("onclick","{$var}.togglesel()");
		echo $btn->get_as_html();

		
		
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
	}
	
}
?>