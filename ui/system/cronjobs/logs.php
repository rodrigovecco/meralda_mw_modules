<?php
class mwmod_mw_ui_system_cronjobs_logs extends mwmod_mw_ui_system_abs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_lngmsgsmancod("system");
		$this->set_def_title($this->lng_get_msg_txt("log","Registro"));
		$this->js_ui_class_name="mw_ui_grid";
		$this->debug_mode=true;
		
	}
	function execfrommain_getcmd_sxml_loaddatagrid($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$xml->set_prop("htmlcont",$this->lng_get_msg_txt("not_allowed","No permitido"));
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;
	
		}
		if(!$jobsman=$this->mainap->get_submanager("jobs")){
			$xml->root_do_all_output();
			return false;	
		}

		
		$xml->set_prop("ok",true);
		
		$datagrid=new mwmod_mw_devextreme_widget_datagrid(false);
		$datagrid->setFilerVisible();
		$datagrid->js_props->set_prop("columnAutoWidth",true);	
		$datagrid->js_props->set_prop("allowColumnResizing",true);	

		

		
		$col=$datagrid->add_column_string("manager","Manager");
		$manlookup=$col->set_lookup("id","id");
		$col=$datagrid->add_column_string("job","Job");
		$joblookup=$col->set_lookup("id","id");
		$col=$datagrid->add_column_date("date","Date");
		$col->js_data->set_prop("format","shortdateshorttime");
		$col=$datagrid->add_column_string("result","Result");
		$col=$datagrid->add_column_number("time","Time");
		$col->js_data->set_prop("format","fixedPoint");
		$col->js_data->set_prop("precision",4);

		$mancods=array();
		$logscods=array();
		$dataoptim=$datagrid->new_dataoptim_data_man();
		$handle=false;
		if($pm=$jobsman->get_logs_path_man()){
			if($pm->file_exists("cronjobs.csv")){
				$path=$pm->get_path();
				$handle = fopen($path."/cronjobs.csv", "r");
			}
			
		}
		$row=1;
		if($handle){
			while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
				
				if($row>2){
					$d=array(
						"manager"=>$data[0],
						"job"=>$data[1],
						"date"=>$data[2],
						"result"=>$data[3],
						"time"=>$data[4]+0,
					
					);
					if($c=$d["manager"]){
						$mancods[$c]=$c;	
					}
					if($c=$d["job"]){
						$logscods[$c]=$c;	
					}
					$dataoptim->add_data($d);
				}
				$row++;
			}
			fclose($handle);	
		}
		reset($mancods);
		foreach($mancods as $c){
			$manlookup->add_data(array("id"=>$c));	
		}
		reset($logscods);
		foreach($logscods as $c){
			$joblookup->add_data(array("id"=>$c));	
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
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem/devextreme_datagrid.js");

	}
	function do_exec_page_in(){
		
		if(!$jobsman=$this->mainap->get_submanager("jobs")){
			return false;	
		}
		if($_REQUEST["empty_log"]){
			$jobsman->clear_log();	
		}
		if($_REQUEST["update_list"]){
			$jobsman->update_managers_list_by_scan();	
		}
		
		$container=$this->get_ui_dom_elem_container();
		
		//
		$gridcontainer=$this->set_ui_dom_elem_id("datagrid_container");
		$body=$this->set_ui_dom_elem_id("datagrid_body");
		$loading=new mwcus_cus_templates_html_loading_placeholder();
		$body->add_cont($loading);
		$gridcontainer->add_cont($body);
		
		$container->add_cont($gridcontainer);
		
		$btnscontainer=$this->set_ui_dom_elem_id("btns");
		$btnscontainer->set_style("padding","10px");
		$btnscontainer->set_style("text-align","right");
		
		$url=$this->get_url(array("update_list"=>"true"));
		$btn=new mwmod_mw_bootstrap_html_specialelem_btn($this->lng_get_msg_txt("update_list","Actualizar lista"));
		$btn->set_att("onclick","window.location='$url'");
		$btnscontainer->add_cont($btn);

		
		$url=$this->get_url(array("empty_log"=>"true"));
		$btn=new mwmod_mw_bootstrap_html_specialelem_btn($this->lng_get_msg_txt("empty_log","Vaciar registro"),"danger");
		$btn->set_att("onclick","window.location='$url'");
		$btnscontainer->add_cont($btn);
		$container->add_cont($btnscontainer);
		
		
		
		$panel=new mwmod_mw_bootstrap_html_template_panelcollapsed();
		$panel->title_elem->add_cont($this->lng_get_msg_txt("information","Información"));
		$info_container=$this->set_ui_dom_elem_id("info");
		$panel->cont_elem->add_cont($info_container);
		$container->add_cont($panel);
		
		$e=$container->add_cont_elem();
		$e->add_cont("IP: ".$_SERVER['REMOTE_ADDR']);
		
		//
		
		$jsui=$this->new_ui_js();
		
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		$jsbefore->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		echo $container->get_as_html();
		$jsbefore->add_cont("ui.init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$jsbefore->add_cont("ui.set_container();\n");
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont($jsbefore,true);
		
		$js->add_cont("ui.loadGridManager();\n");
		$data=$jobsman->get_managers_debug_info();
		$datajs=new mwmod_mw_jsobj_obj($data);
		$js->add_cont("var jobsinfo=new mw_obj();\n");
		$js->add_cont("jobsinfo.set_params(".$datajs->get_as_js_val().");\n");
		$js->add_cont("jobsinfo.omit_type_on_debug_list=true;\n");
		
		$js->add_cont("var jobsinfo_list=jobsinfo.get_list_debug_elem(10);\n");
		$js->add_cont("if(jobsinfo_list){\n");
		$js->add_cont("var jobsinfo_c=ui.get_ui_elem('info');\n");
		$js->add_cont("if(jobsinfo_c){\n");
		$js->add_cont("jobsinfo_c.appendChild(jobsinfo_list);\n");
		$js->add_cont("}\n");
		$js->add_cont("}\n");
		
		
		
		
		
		
		echo $js->get_js_script_html();
		
		//mw_array2list_echo($data);
		
		

		

		
		

		
	}
	
}
?>