<?php
class mwmod_mw_ui_debug_dx_contextmnu extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Menú contextual");
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
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_context_menu.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		
		
		
	}
	function do_exec_page_in(){
		$jsui=$this->new_ui_js();
		echo "<div id='xxxxx'>asdasdasd</div>";
		echo "<div id='xxxxx1'><a href='index.php' target='_blank'>asdasdasd</a></div>";
		echo "<div id='xxxxx2'>aaaaa</div>";
		$cmjs= new mwmod_mw_devextreme_contextmenu_mnu();
		$ch=$cmjs->add_mnu_item("a","A");
		
		$sch=$ch->add_mnu_item("a","AA");
		$sch->setLinkModeNewWindow("index.php?interface=uidebug");
		
		//$onr=$sch->addEventListener("rendered");
		//$onr->add_fnc_arg("e");
		//$onr->add_cont("console.log('rendered',e);\n");
		//$onr->add_cont("e.zzz=1;\n");
		//link_mode
		
		$sch=$ch->add_mnu_item("b","BB");
		$sch=$ch->add_mnu_item("c","CC");
		$ch=$cmjs->add_mnu_item("b","B");
		$sch=$ch->add_mnu_item("a","AA");
		$sch=$ch->add_mnu_item("b","BB");
		$oncl=$sch->onClick();
		$oncl->add_cont("console.log('xxxxxx',e);\n");
		
		$sch=$ch->add_mnu_item("c","CC");
		
		$ch=$cmjs->add_mnu_item("c","C");
		
		$ch=$cmjs->add_mnu_item("d","D");
		$sch=$ch->add_mnu_item("a","AA");
		$sch=$ch->add_mnu_item("b","BB");
		$sch=$ch->add_mnu_item("c","CC");
		
		$itemslist=$ch->get_array_prop("children");
		$itemslist->add_data("xxxx");
		
		$jsdec=new mwmod_mw_jsobj_codecontainer();
		$jsdec->add_cont("var cm=".$cmjs->get_as_js_val().";\n");
		$jsdec->add_cont("cm.createContextMenuOnTarget(mw_get_element_by_id('xxxxx'));\n");
		$jsdec->add_cont("cm.createContextMenuOnTarget(mw_get_element_by_id('xxxxx1'));\n");
		$jsdec->add_cont("cm.createContextMenuOnTarget(mw_get_element_by_id('xxxxx2'));\n");
		
		echo $jsdec->get_js_script_html();
		//return;
		$datagrid=new mwmod_mw_devextreme_widget_datagrid("datagridtest");
		
		$cmjs1= new mwmod_mw_devextreme_contextmenu_mnu();
		$ch=$cmjs1->add_mnu_item("desc","descargar");
		$ch->setLinkModeNewWindow("index.php?interface=uidebug");
		
		
		
		$ch=$cmjs1->add_mnu_item("a","A");
		$oncl=$ch->onSetDxOptions();
		$oncl->add_cont("console.log('onSetDxOptions o',options);\n");
		$oncl->add_cont("console.log('onSetDxOptions elem',elem);\n");
		$oncl->add_cont("console.log('onSetDxOptions man',man);\n");
		
		//$oncl->add_cont("e.options.text=e.options.text+' x '+e.evtn.row.data.s3;\n");
		$oncl->add_cont("options.text=options.text+' x '+man.getCurrentData('s3','none');\n");
		
		
		$sch=$ch->add_mnu_item("a","descargar");
		$sch->setLinkModeNewWindow("index.php?interface=uidebug");
		
		//$onr=$sch->addEventListener("rendered");
		//$onr->add_fnc_arg("e");
		//$onr->add_cont("console.log('rendered',e);\n");
		//$onr->add_cont("e.zzz=1;\n");
		//link_mode
		
		$sch=$ch->add_mnu_item("b","BB");
		//$oncl=$sch->onOptionsForDataGird();
		//$oncl->add_cont("console.log('onOptionsForDataGird e',e);\n");
		//$oncl->add_cont("e.options.text=e.options.text+' '+e.row.data.s3;\n");
		
		
		$sch=$ch->add_mnu_item("c","CC");
		$ch=$cmjs1->add_mnu_item("b","B");
		$sch=$ch->add_mnu_item("a","AA");
		$sch=$ch->add_mnu_item("b","BB");
		$oncl=$sch->onClick();
		$oncl->add_cont("console.log('xxxxxx',e);\n");
		
		$sch=$ch->add_mnu_item("c","CC");
		
		$ch=$cmjs1->add_mnu_item("c","C");
		
		$ch=$cmjs1->add_mnu_item("d","D");
		$sch=$ch->add_mnu_item("a","AA");
		$sch=$ch->add_mnu_item("b","BB");
		$sch=$ch->add_mnu_item("c","CC");
		
		
		
		//function set_js_event($cod,$jsfnc){
		$cmp=new mwmod_mw_jsobj_functionext();
		$cmp->add_fnc_arg("e");
		
		//$gridhelper->addEventListener("contextMenuPreparing");
		$cmp->add_cont("console.log('cmp',e);\n");
		$cmp->add_cont("var cm=".$cmjs1->get_as_js_val().";\n");
		$cmp->add_cont("cm.add2DXdatagridOnContextMenuPreparing(e);\n");
		
		$datagrid->set_js_event("contextMenuPreparing",$cmp);

		
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
		$col=$datagrid->add_column_string("s3","Serie 3");
		//$col->set_mw_js_colum_class("mw_devextreme_datagrid_column_contextmenu");
		
		
		$dataoptim=$datagrid->new_dataoptim_data_man();		
		$dataoptim->set_key("id");
		
		$dataoptim->add_data(array("id"=>1,"s1"=>6,"s2"=>4,"s3"=>"Hola"));	
		$dataoptim->add_data(array("id"=>2,"s1"=>5,"s2"=>2,"s3"=>"Hola 1"));	
		$dataoptim->add_data(array("id"=>3,"s1"=>2,"s2"=>4,"s3"=>"Hola 2"));	
		$dataoptim->add_data(array("id"=>4,"s1"=>8,"s2"=>3,"s3"=>"Hola 3"));	
		$dataoptim->add_data(array("id"=>5,"s1"=>3,"s2"=>4,"s3"=>"Hola 4"));	
		$dataoptim->add_data(array("id"=>6,"s1"=>5,"s2"=>2,"s3"=>"Hola 5"));	
		$dataoptim->add_data(array("id"=>7,"s1"=>4,"s2"=>4,"s3"=>"Hola 6"));	
		
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
		/*
		$cmp=$gridhelper->addEventListener("contextMenuPreparing");
		$cmp->add_cont("console.log('cmp',e);\n");
		*/
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