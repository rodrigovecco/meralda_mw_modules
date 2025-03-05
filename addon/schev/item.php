<?php
abstract class  mwmod_mw_addon_schev_item extends mwmod_mw_manager_itemwithtype{
	var $can_not_activate_msg;
	private $_check_ok;
	private $_scheduled_tbl_items;
	var $save_result_notification_js;
	final function init_item($tblitem,$type){
		$this->set_lngmsgsmancod($type->man->lngmsgsmancod);	
		$this->init_from_type($tblitem,$type);	
		$this->enable_treedata();
		$this->enable_strdata();
	}
	function get_exec_sumary_html_head(){
		$r="<div>";
		if($n=$this->get_data("name")){
			$r.=$this->lng_get_msg_txt("the_task","La tarea")." <strong>$n</strong> ".$this->lng_get_msg_txt("will_be_executed","se ejecutará");
		}else{
			$r.=$this->lng_get_msg_txt("the_task","La tarea")." ".$this->lng_get_msg_txt("will_be_executed","se ejecutará");
		}
		$r.="</div>";
		return $r;
	}
	function get_exec_sumary_dates_str(){
		$data=array();
		if($items=$this->get_scheduled_tbl_items()){
			foreach($items as $id=>$item){
				$data[]=$item->get_data();	
			}
		}
		return $this->man->get_exec_sumary_dates_str($data);
	}
	function get_exec_sumary_period_str(){
		return $this->man->get_exec_sumary_period_str($this->get_data());	
	}
	function get_exec_sumary_html(){
		$html=$this->get_exec_sumary_html_head();
		$html.="<div>".$this->get_exec_sumary_dates_str()."</div>";
		$html.="<div>".$this->get_exec_sumary_period_str()."</div>";
		return $html;
		
		
	}
	function pre_delete_depending_objects(){
		if($tblitems=$this->get_scheduled_tbl_items()){
			
			foreach($tblitems as $id=>$tblitem){
				$tblitem->delete();
				
			}
		}
		return true;
	}
	
	
	function exec_from_cron(){
		return false;	
	}
	final function init_scheduled_tbl_items(){
		if(!isset($this->_scheduled_tbl_items)){
			$this->_scheduled_tbl_items=array();
			if($this->man->schedule_tbl_man){
				$q=	$this->man->schedule_tbl_man->new_query();
				$q->where->add_where_crit("event_id",$this->get_id());
				$sql=$q->get_sql();
			
				if($items=$this->man->schedule_tbl_man->get_items_by_sql($sql)){
					$this->_scheduled_tbl_items=$items;	
				}
			}
		}
	}

	final function get_scheduled_tbl_items(){
		$this->init_scheduled_tbl_items();
		return $this->_scheduled_tbl_items;
		
	}
	final function get_scheduled_tbl_item($id){
		if(!$id=$id+0){
			return false;	
		}
		$this->init_scheduled_tbl_items();
		return $this->_scheduled_tbl_items[$id];
		
	}
	final function unset_scheduled_tbl_items(){
		unset($this->_scheduled_tbl_items);
	}
	
	final function unset_check(){
		unset($this->_check_ok);
	}
	function load_check(){
		return false;	
	}
	final function check(){
		if(!isset($this->_check_ok)){
			if($this->load_check()){
				$this->_check_ok=true;	
			}else{
				$this->_check_ok=false;		
			}
		}
		return $this->_check_ok;
	}
	function save_scheduled_item($input){
		if(!is_array($input)){
			return false;	
		}
		$input["month"]=$input["month"]+0;
		$input["year"]=$input["year"]+0;
		$input["day"]=$input["day"]+0;
		$input["weekday"]=$input["weekday"]+0;
		if(!$tblman=$this->man->schedule_tbl_man){
			return false;	
		}
		if($input["id"]=="new"){
			if($input["delete"]){
				return false;	
			}
			$input["event_id"]=$this->get_id();
			return $tblman->insert_item($input);
			
		}
		if(!$tblitem=$this->get_scheduled_tbl_item($input["id"])){
			return false;	
		}
		if($input["delete"]){
			$tblitem->delete();
			
			return true;	
		}
		unset($input["id"]);
		unset($input["event_id"]);
		return $tblitem->update($input);
		
		
		
		
			
	}
	function save_scheduled($input){
		if(!is_array($input)){
			return false;	
		}
		
		foreach($input as $nd){
			$this->save_scheduled_item($nd);	
		}
		$this->unset_scheduled_tbl_items();
			
	}
	
	function save_cfg($input){
		$this->unset_check();
		if(!is_array($input)){
			return false;	
		}
		if($td=$this->get_treedata_item("cfg")){
			$td->set_data($input);
			$td->save();
			return true;	
		}
	}
	function save_from_input_man($input){
		if(!$input->is_req_input_ok()){
			return false;
		}
		if(!$nd=$input->get_value_by_dot_cod_as_list("data")){
			return false;	
		}
		//$nd["active"]=$nd["active"]+0;
		unset($nd["type"]);
		unset($nd["exec_num"]);
		unset($nd["owner"]);
		unset($nd["last_exec"]);
		$this->save_cfg($input->get_value_by_dot_cod_as_list("cfg"));
		$this->save_scheduled($input->get_value_by_dot_cod_as_list("scheduled"));
		
		if(!$this->check()){
			$nd["active"]=0;	
		}
		if($this->save_result_notification_js){
			if($nd["active"]){
				$msg=$this->lng_get_msg_txt("task_saved","Tarea guardada.");
				$this->save_result_notification_js->set_msg($msg);	
				$this->save_result_notification_js->set_type_success();
			}else{
				$msg=$this->lng_get_msg_txt("task_saved_but_not_active","Tarea guardada, pero inactiva.");
				$this->save_result_notification_js->set_msg($msg);	
				$this->save_result_notification_js->set_type_warning();
					
			}
		}
		$this->tblitem->update($nd);
		return true;
		

	}
	/*
	function new_js_scheduled(){
		return;
		$gr=new mwmod_mw_jsobj_inputs_input("n","mw_datainput_item_groupwithtitle");
		$helper= new mwmod_mw_ap_helper();
		$inputjs=$gr->add_child(new mwmod_mw_jsobj_inputs_input("year"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("year","Año"));
		$inputjs->set_prop("leftBtn.lbl",$this->lng_common_get_msg_txt("all","Todos"));	
		$inputjs->set_prop("leftBtn.enabled",true);	
		$leftBtnCl=new mwmod_mw_jsobj_functionext();
		$leftBtnCl->add_fnc_arg("e");
		$leftBtnCl->add_cont("e.set_input_value('')");
		$inputjs->set_prop("leftBtn.onclick",$leftBtnCl);	
		
		
		
		$inputjs=$gr->add_child(new mwmod_mw_jsobj_inputs_select("month"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("month","Mes"));
		$inputjs->set_prop("noemtyoption",true);
		$inputjs->add_select_option("all",$this->lng_common_get_msg_txt("all","Todos"));		
		$months=$helper->dateman->get_months();
		foreach($months as $id_m=>$m){
			$inputjs->add_select_option($id_m,$m->name);			
		}
		$inputjs=$gr->add_child(new mwmod_mw_jsobj_inputs_input("day"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("day","Día"));
		$inputjs->set_prop("leftBtn.lbl",$this->lng_common_get_msg_txt("all","Todos"));	
		$inputjs->set_prop("leftBtn.enabled",true);	
		$leftBtnCl=new mwmod_mw_jsobj_functionext();
		$leftBtnCl->add_fnc_arg("e");
		$leftBtnCl->add_cont("e.set_input_value('')");
		$inputjs->set_prop("leftBtn.onclick",$leftBtnCl);	
		
		$inputjs=$gr->add_child(new mwmod_mw_jsobj_inputs_select("weekday"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("weekday","Día de la semana"));
		$inputjs->set_prop("noemtyoption",true);
		$inputjs->add_select_option("all",$this->lng_common_get_msg_txt("all","Todos"));		
		$months=$helper->dateman->get_weekdays();
		foreach($months as $id_m=>$m){
			$inputjs->add_select_option($id_m,$m->name);			
		}
		
		$inputjs=$gr->add_child(new mwmod_mw_jsobj_inputs_input("delete","mw_datainput_item_checkbox"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("delete","Eliminar"));
		
		
		
		
		return $gr;
	}
	*/
	/*
	function set_edit_js_inputs_scheduled($inputsgrmain){
		return ;
		$helper= new mwmod_mw_ap_helper();
		$inputsgrdata=$inputsgrmain->add_data_gr("scheduled");
		$inputsgrdata->set_js_class("mw_datainput_item_datagrid");
		$inputsgrdata->set_prop("lbl",$this->lng_common_get_msg_txt("scheduled","Programación"));
		$inputsgrdata->set_prop("newrowbtn",true);
		
		$datagrid=new mwmod_mw_devextreme_widget_datagrid();
		$datagrid->js_props->set_prop("editing.editEnabled",true);
		$datagrid->js_props->set_prop("editing.insertEnabled",true);
		$datagrid->js_props->set_prop("editing.removeEnabled",true);
		
		
		
		$datagrid->js_props->set_prop("editing.editMode","cell");
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
		$fnccustomizeText->add_cont("if(cellInfo.value){return cellInfo.value}\n");
		$fnccustomizeText->add_cont("return '".$fnccustomizeText->get_txt($this->lng_common_get_msg_txt("all","Todos"))."'\n");
		$col->js_data->set_prop("customizeText",$fnccustomizeText);
		
		$col=$datagrid->add_column_string("month",$this->lng_common_get_msg_txt("month","Mes"));
		$lu=$col->set_lookup("id","name");
		$lu->add_data(array("id"=>0,"name"=>$this->lng_common_get_msg_txt("all","Todos")));
		$months=$helper->dateman->get_months();
		foreach($months as $id_m=>$m){
			$lu->add_data(array("id"=>$id_m,"name"=>$m->name));
		}
		
		
		
		
		
		$col=$datagrid->add_column_number("day",$this->lng_common_get_msg_txt("day","Día"));
		$fnccustomizeText=new mwmod_mw_jsobj_functionext();
		$fnccustomizeText->add_fnc_arg("cellInfo");
		$fnccustomizeText->add_cont("if(cellInfo.value){return cellInfo.value}\n");
		$fnccustomizeText->add_cont("return '".$fnccustomizeText->get_txt($this->lng_common_get_msg_txt("all","Todos"))."'\n");
		$col->js_data->set_prop("customizeText",$fnccustomizeText);
		
		$col=$datagrid->add_column_string("weekday",$this->lng_common_get_msg_txt("weekday","Día de la semana"));
		
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
		if($tblitems=$this->get_scheduled_tbl_items()){
			
			foreach($tblitems as $id=>$tblitem){
				$x++;
				$d=$tblitem->get_data();
				$d["index"]=$x;
				$data[$x]=$d;
				
			}
		}
		$inputsgrdata->set_value($data);
		
		
		
		
		
			
	}
	*/
	/*
	function set_edit_js_inputs_cfg($inputsgrmain){
		$inputsgrdata=$inputsgrmain->add_data_gr("cfg");
		$inputsgrdata->set_js_class("mw_datainput_item_groupwithtitle");
		$inputsgrdata->set_prop("lbl",$this->lng_common_get_msg_txt("configuration","Configuración"));
		$this->set_edit_js_inputs_cfg_sub($inputsgrdata);
		if($td=$this->get_treedata_item("cfg")){
			$inputsgrdata->set_prop("value",$td->get_data());
			
		}
		$fncvalid=new mwmod_mw_jsobj_functionext();
		$fncvalid->add_fnc_arg("elem");
		$fncvalid->add_cont("var active=elem.getParentChildByDotCod(1,'data.active');\n");
		$fncvalid->add_cont("if(active){if(!active.get_input_value()){ return true}} return false");
		$inputsgrdata->set_prop("omitChildrenValidationFnc",$fncvalid);
		
			
	}
	function set_edit_js_inputs_cfg_sub($inputsgrmain){
			
	}
	*/

	
	function set_edit_js_inputs($inputsgrmain){
		$this->type->set_edit_js_inputs($inputsgrmain,$this);
		
		/*
		return;
		$inputsgrdata=$inputsgrmain->add_data_gr("data");
		$inputsgrdata->set_js_class("mw_datainput_item_groupwithtitle");
		$inputsgrdata->set_prop("lbl",$this->lng_common_get_msg_txt("data","Datos"));

		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("name"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("name","Nombre"));
		$inputjs->set_prop("state.required",true);
		
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("start_date","mw_datainput_item_date"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("start_date","Fecha inicio"));
		$inputjs->set_prop("nohour",true);
		
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("end_date","mw_datainput_item_date"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("end_date","Fecha fin"));
		$inputjs->set_prop("nohour",true);
		
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("active","mw_datainput_item_checkbox"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("active","Activo"));
		
		if($this->can_not_activate_msg){
			$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("can_not_activate_msg","mw_datainput_item_html"));
			//$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("type","Tipo"));
			$inputjs->set_prop("cont",$this->can_not_activate_msg);
				
		}
		
		
		
		$inputsgrdata->set_prop("value",$this->get_data());
		$this->set_edit_js_inputs_cfg($inputsgrmain);
		*/
		/*
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_select("type"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("type","Tipo"));
		$inputjs->set_prop("state.required",true);
		*/
		/*
		$inputsgrdata=$inputsgrmain->add_data_gr("info");
		$inputsgrdata->set_js_class("mw_datainput_item_groupwithtitle");
		$inputsgrdata->set_prop("lbl",$this->lng_common_get_msg_txt("information","Información"));
		$inputsgrdata->set_prop("collapsed",true);
		
		
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("type","mw_datainput_item_html"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("type","Tipo"));
		$inputjs->set_prop("cont",$this->type->get_name());
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("last_exec","mw_datainput_item_html"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("last_exec","Última ejecución"));
		$inputjs->set_prop("cont",$this->tblitem->get_data_as_date("last_exec"));
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("exec_num","mw_datainput_item_html"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("exec_num","Número de ejecuciones"));
		$inputjs->set_prop("cont",$this->get_data("exec_num"));
		*/
		
		//$this->set_edit_js_inputs_scheduled($inputsgrmain);

	}
	
	function allow_delete(){
		return true;
	}

	
	//$this->set_lngmsgsmancod($man->lngmsgsmancod);	

}
?>