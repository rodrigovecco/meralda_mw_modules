<?php
abstract class  mwmod_mw_addon_schev_type extends mwmod_mw_manager_itemtype{
	
	final function init_type($cod,$man){
		$this->init($cod,$man);
		$this->set_lngmsgsmancod($man->lngmsgsmancod);			
		
	}
	function new_ui_title(){
		return $this->lng_get_msg_txt("new","Nuevo")." ".$this->name;	
	}
	function set_new_js_inputs($inputsgrmain){
		$this->set_edit_js_inputs($inputsgrmain);	
	}
	function set_edit_js_inputs($inputsgrmain,$item=false){
		$inputsgrdata=$inputsgrmain->add_data_gr("data");
		$inputsgrdata->set_js_class("mw_datainput_item_groupwithtitle");
		$inputsgrdata->set_prop("lbl",$this->lng_get_msg_txt("program_data","Datos de la programación"));

		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("name"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("name","Nombre"));
		$inputjs->set_prop("state.required",true);
		
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("start_date","mw_datainput_item_date"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("date_from_to_start","Fecha desde que se ejecutará"));
		$inputjs->set_prop("nohour",true);
		
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("end_date","mw_datainput_item_date"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("date_to_finish","Fecha hasta la que se ejecutará"));
		$inputjs->set_prop("nohour",true);
		
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("active","mw_datainput_item_checkbox"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("active","Activo"));
		
		
		if($item){
			if($item->can_not_activate_msg){
				$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("can_not_activate_msg","mw_datainput_item_html"));
				//$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("type","Tipo"));
				$inputjs->set_prop("cont",$item->can_not_activate_msg);
					
			}
			$inputsgrdata->set_prop("value",$item->get_data());
			
		}
		
		
		
		$this->set_edit_js_inputs_after($inputsgrmain,$item);
		
		$this->set_edit_js_inputs_cfg($inputsgrmain,$item);
		$this->set_edit_js_inputs_scheduled($inputsgrmain,$item);
		$this->set_edit_js_inputs_summary($inputsgrmain,$item);
	
	}
	function set_edit_js_inputs_summary($inputsgrmain,$item=false){
		if(!$item){
			return false;
		}
		$inputsgrdata=$inputsgrmain->add_data_gr("summary");
		$inputsgrdata->set_js_class("mw_datainput_item_groupwithtitle");
		$inputsgrdata->set_prop("lbl",$this->lng_common_get_msg_txt("summary","Resumen"));
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("can_not_activate_msg","mw_datainput_item_html"));
		$inputjs->set_prop("cont",$item->get_exec_sumary_html());
		
		
	}
	
	function set_edit_js_inputs_after($inputsgrmain,$item=false){
			
	}
	function set_edit_js_inputs_scheduled($inputsgrmain,$item=false){
		$helper= new mwmod_mw_ap_helper();
		$inputsgrdata=$inputsgrmain->add_data_gr("scheduled");
		$inputsgrdata->set_js_class("mw_datainput_item_datagrid");
		$inputsgrdata->set_prop("lbl",$this->lng_common_get_msg_txt("scheduled","Programación"));
		$inputsgrdata->set_prop("newrowbtn",true);
		
		$datagrid=new mwmod_mw_devextreme_widget_datagrid();
		/*
		$datagrid->js_props->set_prop("editing.editEnabled",true);
		$datagrid->js_props->set_prop("editing.insertEnabled",true);
		$datagrid->js_props->set_prop("editing.removeEnabled",true);
		$datagrid->js_props->set_prop("editing.editMode","cell");
		*/
		
		$datagrid->js_props->set_prop("editing.allowUpdating",true);
		$datagrid->js_props->set_prop("editing.allowAdding",true);
		$datagrid->js_props->set_prop("editing.allowDeleting",true);
		$datagrid->js_props->set_prop("editing.mode","cell");
		
		
		$col=$datagrid->add_column_number("index","Index");
		$col->js_data->set_prop("width",60);
		$col->js_data->set_prop("visible",false);
		$col->js_data->set_prop("allowEditing",false);
		
		
		$col=$datagrid->add_column_string("id","ID");
		$col->js_data->set_prop("width",60);
		$col->js_data->set_prop("allowEditing",false);
		$col->js_data->set_prop("visible",false);

		$col=$datagrid->add_column_number("year",$this->lng_common_get_msg_txt("year","Año"));
		$fnccustomizeText=new mwmod_mw_jsobj_functionext();
		$fnccustomizeText->add_fnc_arg("cellInfo");
		$fnccustomizeText->add_cont("if(cellInfo.value){return cellInfo.value+''}\n");
		$fnccustomizeText->add_cont("return '".$fnccustomizeText->get_txt($this->lng_common_get_msg_txt("all","Todos"))."'\n");
		$col->js_data->set_prop("customizeText",$fnccustomizeText);
		
		//$col=$datagrid->add_column_string("month",$this->lng_common_get_msg_txt("month","Mes"));
		$col=$datagrid->add_column_number("month",$this->lng_common_get_msg_txt("month","Mes"));
		
		$lu=$col->set_lookup("id","name");
		$lu->add_data(array("id"=>0,"name"=>$this->lng_common_get_msg_txt("all","Todos")));
		$months=$helper->dateman->get_months();
		foreach($months as $id_m=>$m){
			$lu->add_data(array("id"=>$id_m."","name"=>$m->name));
		}
		
		
		
		
		
		
		$col=$datagrid->add_column_number("day",$this->lng_common_get_msg_txt("day","Día"));
		$fnccustomizeText=new mwmod_mw_jsobj_functionext();
		$fnccustomizeText->add_fnc_arg("cellInfo");
		$fnccustomizeText->add_cont("if(cellInfo.value){return cellInfo.value+''}\n");
		$fnccustomizeText->add_cont("return '".$fnccustomizeText->get_txt($this->lng_common_get_msg_txt("all","Todos"))."'\n");
		$col->js_data->set_prop("customizeText",$fnccustomizeText);
		
		//$col=$datagrid->add_column_string("weekday",$this->lng_common_get_msg_txt("weekday","Día de la semana"));
		
		$col=$datagrid->add_column_number("weekday",$this->lng_common_get_msg_txt("weekday","Día de la semana"));
		
		$lu=$col->set_lookup("id","name");
		$lu->add_data(array("id"=>0,"name"=>$this->lng_common_get_msg_txt("all","Todos")));
		$months=$helper->dateman->get_weekdays();
		foreach($months as $id_m=>$m){
			$lu->add_data(array("id"=>$id_m,"name"=>$m->name));
		}
		
		
		
		
		
		$columns=$datagrid->columns->get_items();
		$gridhelper=$datagrid->new_mw_helper_js();
		$gridhelper->set_prop("hideHeaderPanel",true);
		
		$list=$gridhelper->get_array_prop("columns");
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$list->add_data($coljs);
			
		}
		$inputsgrdata->set_prop("dataGridMan",$gridhelper);
		
		
		
		$x=0;
		$data=array();
		if($item){
			if($tblitems=$item->get_scheduled_tbl_items()){
				
				foreach($tblitems as $id=>$tblitem){
					$x++;
					$d=$tblitem->get_data();
					$d["index"]=$x;
					$data[$x]=$d;
					
				}
			}
		}
		$inputsgrdata->set_value($data);
			
	}
	function set_edit_js_inputs_cfg_sub($inputsgrdata,$item=false){
			
	}
	
	function set_edit_js_inputs_cfg($inputsgrmain,$item=false){
		$inputsgrdata=$inputsgrmain->add_data_gr("cfg");
		$inputsgrdata->set_js_class("mw_datainput_item_groupwithtitle");
		$inputsgrdata->set_prop("lbl",$this->lng_common_get_msg_txt("configuration","Configuración"));
		$this->set_edit_js_inputs_cfg_sub($inputsgrdata);
		
		if($item){
			if($td=$item->get_treedata_item("cfg")){
				$inputsgrdata->set_prop("value",$td->get_data());
			
			}
		}
		$fncvalid=new mwmod_mw_jsobj_functionext();
		$fncvalid->add_fnc_arg("elem");
		$fncvalid->add_cont("var active=elem.getParentChildByDotCod(1,'data.active');\n");
		$fncvalid->add_cont("if(active){if(!active.get_input_value()){ return true}} return false");
		$inputsgrdata->set_prop("omitChildrenValidationFnc",$fncvalid);
		
			
	}
	

}
?>