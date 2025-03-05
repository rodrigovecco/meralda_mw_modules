<?php
class mwmod_mw_addon_schev_ui_debug extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->set_items_man($parent->items_man);
		
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("tests","Pruebas"));
		$this->js_ui_class_name="mw_ui_grid";
		
	}
	
	function do_exec_no_sub_interface(){
		$this->prepare_js_and_css_mans();
	}

	function do_exec_page_in(){
		
		
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		
		$frmjs=new mwmod_mw_jsobj_inputs_frmonpanel();
		//$frmjs->set_prop("lbl","test");
		$frmjs->add_submit($this->lng_get_msg_txt("test","Probar"));
		$inputsgrmain=$frmjs->add_data_main_gr("nd");
		$inputsgrdata=$inputsgrmain->add_data_gr("data");
		
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("start_date","mw_datainput_item_date"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("start_date","Fecha inicio"));
		$inputjs->set_prop("nohour",true);
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("days"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("days","Días"));
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		if($input->is_req_input_ok()){
			if($nd=$input->get_value_by_dot_cod_as_list("data")){
				$inputsgrdata->set_value($nd);
			}
			
		}
		$container=$this->get_ui_dom_elem_container_empty();
		$frmcontainer=$this->set_ui_dom_elem_id("frm");
		$container->add_cont($frmcontainer);
		//echo nl2br($js->get_as_js_val());
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		if($test=$this->get_test_tbl_data($input)){
			//mw_array2list_echo($test);
			/*
			$gridcontainer=$this->set_ui_dom_elem_id("datagrid_container");
			$body=$this->set_ui_dom_elem_id("datagrid_body");
			$loading=new mwcus_cus_templates_html_loading_placeholder();
			$body->add_cont($loading);
			$gridcontainer->add_cont($body);
			
			$container->add_cont($gridcontainer);
			*/
			
			$datagrid=new mwmod_mw_devextreme_widget_datagrid("datagridtest");
			$datagrid->setFilerVisible();
			$datagrid->js_props->set_prop("allowColumnReordering",true);
		
		
			$col=$datagrid->add_column_number("id","ID");
			$col->js_data->set_prop("width",60);
			$col->js_data->set_prop("visible",false);
			$col=$datagrid->add_column_date("date",$this->lng_common_get_msg_txt("date","Fecha"));
			$col->js_data->set_prop("format","longDate");
			
			$col=$datagrid->add_column_string("name",$this->lng_get_msg_txt("scheduled_task","Tarea programada"));
			$col=$datagrid->add_column_string("id_item",$this->lng_common_get_msg_txt("id","ID"));
			
			$dataoptim=$datagrid->new_dataoptim_data_man();
			
			foreach($test as $x=>$d){
				$dataoptim->add_data($d);	
			}
			$gridhelper=$datagrid->new_mw_helper_js();
			$jsbefore->add_cont("\n {$var}.set_datagrid_man(".$gridhelper->get_as_js_val().");\n\n\n\n");	
			$columns=$datagrid->columns->get_items();
			foreach($columns as $col){
				$coljs=$col->get_mw_js_colum_obj();
				$jsbefore->add_cont("{$var}.datagrid_man.add_colum(".$coljs->get_as_js_val().");\n");	
			}
			$jsbefore->add_cont("{$var}.datagrid_man.set_ds_from_optim(".$dataoptim->get_as_js_val().");\n");	
			$jsbefore->add_cont("{$var}.datagrid_man.create_data_grid();\n");
			$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
			$js->add_cont($jsbefore);
			$js->add_cont("{$var}.set_grid('".$datagrid->container_id."')");
			$container->add_cont( $datagrid->get_html_container());

			

			
				
				
		}else{
			$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
				
		}
		$container->do_output();
		$js->add_cont("var frm=".$frmjs->get_as_js_val().";\n");
		$js->add_cont("frm.append_to_container(".$var.".get_ui_elem('frm'));\n");
		echo $js->get_js_script_html();
		
		//
		
		
		

		
	}
	function get_test_tbl_data($input){
		if($data=$this->get_debug_data($input)){
			return $this->get_test_tbl_data_from_debug($data);	
		}
	}
	function get_test_tbl_data_from_debug($data){
		if(!is_array($data)){
			return false;	
		}
		$r=array();
		$x=0;
		foreach($data as $index=>$d){
			if(is_array($d["items"])){
				foreach($d["items"] as $id=>$item){
					$x++;
					$dd=array(
						"id"=>$x,
						"date"=>$d["date"],
						"id_item"=>$id,
						"name"=>$item->get_name(),
					
					);
					$r[$x]=$dd;	
				}
			}
		}
		if(sizeof($r)){
			return $r;	
		}
		
		
	}
	
	
	function get_debug_data($input){
		if(!$input->is_req_input_ok()){
			return false;
		}
		if(!$nd=$input->get_value_by_dot_cod_as_list("data")){
			return false;	
		}
		
		if(!$time=$input->get_value_by_dot_cod_as_time("data.start_date")){
			return false;	
		}
		$days=$nd["days"]+0;
		if($days<0){
			$days=0;	
		}
		$test=array();
		for($x=0;$x<=$days;$x++){
			$time_this=strtotime(date("Y-m-d",$time)." + $x days");
			$date=	date("Y-m-d",$time_this);
			$d=array(
				"date"=>$date,
			
			);
			if($q=$this->items_man->get_items_to_exec_on_time_query($time_this)){
				$d["sql"]=$q->get_sql();	
			}
			if($items=$this->items_man->get_items_to_exec_on_time($time_this)){
				$d["items"]=$items;	
			}
			$test[$x+1]=$d;
			
			
			
		}
		if(sizeof($test)){
			return $test;	
		}
			
	}
	function prepare_js_and_css_mans(){
		
		
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem/devextreme_datagrid.js");
		
		
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);

		
		$jsman=$util->get_js_man();
		
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("inputs/date.js");
		
		
		
		
			
	}
	
	function is_allowed(){
		return $this->allow("debug");	
	}

}
?>