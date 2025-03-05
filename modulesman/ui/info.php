<?php
class mwmod_mw_modulesman_ui_info extends mwmod_mw_modulesman_ui_abs{
	function __construct($cod,$maininterface){
		$this->initui($cod,$maininterface);
		$this->set_def_title($this->lng_common_get_msg_txt("information","Información"));
		$this->js_ui_class_name="mw_ui_grid";
		
	}
	
	function execfrommain_getcmd_sxml_loaddatagrid($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$xml->set_prop("htmlcont",$this->lng_get_msg_txt("not_allowed","No permitido"));
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;
	
		}
		
		$xml->set_prop("ok",true);
		
		$datagrid=new mwmod_mw_devextreme_widget_datagrid(false);
		$datagrid->setFilerVisible();
		$datagrid->js_props->set_prop("columnAutoWidth",true);	
		$datagrid->js_props->set_prop("allowColumnResizing",true);	
		

		

		
		$col=$datagrid->add_column_string("manager","manager");
		$col=$datagrid->add_column_string("mode","mode");
		$col=$datagrid->add_column_string("cod","cod");
		$col=$datagrid->add_column_string("type","type");
		$col=$datagrid->add_column_number("version","version");
		$col=$datagrid->add_column_string("path","path");
		
		$dataoptim=$datagrid->new_dataoptim_data_man();
		
		
		if($data=$this->modulesman->get_csv_data()){
			foreach($data as $row=>$d){
				$dataoptim->add_data($d);	
				
			}
		}
		$columns=$datagrid->columns->get_items();
		$gridhelper=$datagrid->new_mw_helper_js();
		$list=$gridhelper->get_array_prop("columns");
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$list->add_data($coljs);
			
		}
		$gridhelper->set_prop("dsoptim",$dataoptim);
		$js=new mwmod_mw_jsobj_obj();
		$js->set_prop("datagridman",$gridhelper);
		$xml_js=new mwmod_mw_data_xml_js("jsresponse",$js);
		$xml->add_sub_item($xml_js);
		$xml->root_do_all_output();
		
		
		
		
			
	}
	
	function do_exec_no_sub_interface(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);


		$jsman=$this->maininterface->jsmanager;
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem/devextreme_datagrid.js");

	}
	
	function execfrommain_getcmd_dl_dlinfocsv($params=array(),$filename=false){
		if(!$this->is_allowed()){
			return false;	
		}
		if(!$pm=$this->modulesman->get_info_path_man()){
			return false;	
		}
		if(!$path=$pm->check_and_create_path()){
			return false;	
		}
		//echo $path."<br>";
		if(!$pm->file_exists("info.csv")){
			echo "File does no exist";
			return;
		}
		ob_end_clean();
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=info.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile($path."/info.csv");


		
	}
	function do_exec_page_in(){
		
		//mw_array2list_echo($this->modulesman->get_csv_data());
		
		$container=$this->get_ui_dom_elem_container();
		
		//
		$gridcontainer=$this->set_ui_dom_elem_id("datagrid_container");
		$body=$this->set_ui_dom_elem_id("datagrid_body");
		$loading=new mwcus_cus_templates_html_loading_placeholder();
		$body->add_cont($loading);
		$gridcontainer->add_cont($body);
		
		$container->add_cont($gridcontainer);
		
		$url=$this->get_exec_cmd_dl_url("dlinfocsv",false,"info.csv");
		$container->add_cont( "<div><a href='$url' target='_blank'>Download</a></div>");
		
		
		//
		
		$jsui=$this->new_ui_js();
		
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		$jsbefore->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		echo $container->get_as_html();
		$jsbefore->add_cont("ui.init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$jsbefore->add_cont("ui.set_container();\n");
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont($jsbefore,true);
		
		$js->add_cont("ui.loadGridManager()");
		echo $js->get_js_script_html();
		
		
		
	}
	function is_responsable_for_sub_interface_mnu(){
		return false;	
	}

	
}
?>