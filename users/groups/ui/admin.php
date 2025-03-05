<?php
class mwmod_mw_users_groups_ui_admin extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_common_get_msg_txt("admin_groups","Administrar grupos"));
		$this->js_ui_class_name="mw_ui_users_groups_admin";
		//$this->debug_mode=true;
		
	}
	function load_all_subinterfases(){
		
		$si=$this->add_new_subinterface(new mwmod_mw_users_groups_ui_setusers("grusers",$this));
	}
	
	
	function do_exec_page_in(){
		
		
		
		if(!$uman=$this->mainap->get_user_manager()){
			return false;	
		}
		if(!$man=$uman->get_groups_man()){
			return false;	
		}
		
		//$ondomreadyjs=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$container=$this->get_ui_dom_elem_container();
		
		//$jsfull=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$jsui=$this->new_ui_js();
		//$jsui->set_fnc_name("mw_ui_grid");
		$datagrid=new mwmod_mw_devextreme_widget_datagrid("datagriditems");
		$jsbefore=new mwmod_mw_jsobj_codecontainer();
		//$jsbefore->add_cont("test();\n");
		$jsbefore->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		//$ondomreadyjs->add_cont("var ui=".$jsui->get_as_js_val().";\n");
		
		//$jsbefore->add_cont("ui.set_container();\n");
		
		
		echo $container->get_as_html();
		//echo "<div id='ui_groups_admin'></div>";
		
		$datagrid->setFilerVisible();
		$datagrid->js_props->set_prop("columnAutoWidth",false);	
		$datagrid->js_props->set_prop("allowColumnResizing",true);	

		//$datagrid->js_props->set_prop("paging.enabled",false);	
		$datagrid->js_props->set_prop("editing.editMode","row");	
		$datagrid->js_props->set_prop("editing.editEnabled",true);	
		$datagrid->js_props->set_prop("editing.removeEnabled",false);	
		$datagrid->js_props->set_prop("editing.insertEnabled",true);
		//$datagrid->js_props->set_prop("editing.texts.editRow","\2212");
		
		

		
		$col=$datagrid->add_column_number("id","ID");
		$col->js_data->set_prop("width",60);
		$col->js_data->set_prop("allowEditing",false);
		$col->js_data->set_prop("visible",false);
		
		$col=$datagrid->add_column_string("name",$this->lng_common_get_msg_txt("group","Grupo"));
		$col=$datagrid->add_column_boolean("active",$this->lng_common_get_msg_txt("active","Activo"));
		$col->js_data->set_prop("filterValue",true);
		$dataoptim=$datagrid->new_dataoptim_data_man();
		if($items=$man->get_all_items()){
			foreach($items as $cod=>$item){
				//$datagrid->add_data($item->get_data());	
				$dataoptim->add_data($item->get_data());	
				
			}
		}
		$datagrid->set_js_ui_events("rowRemoved,rowUpdating,rowInserting,rowInserted,initNewRow");
		
		//$datagrid->htmlelem->set_style("width","400px");
		//$datagrid->htmlelem->set_style("margin","auto");
		echo $datagrid->get_html_container();
		$gridhelper=$datagrid->new_mw_helper_js();
		$jsbefore->add_cont("\n ui.set_datagrid_man(".$gridhelper->get_as_js_val().");\n\n\n\n");	
		
		
		$coljs=new mwmod_mw_devextreme_widget_datagrid_helper_mnu_column();
		$colmnuitem=$coljs->new_mnu_item_edit($this->get_url_subinterface("grusers"));
		$colmnuitem->set_prop("iconClass","fa fa-group");	
		$colmnuitem->set_prop("lbl",$this->lng_common_get_msg_txt("users","Usuarios"));	
		$coljs->add_mnu_item($colmnuitem);
		$jsbefore->add_cont("ui.add_data_grid_colum(".$coljs->get_as_js_val().");\n");
		
		$columns=$datagrid->columns->get_items();
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$jsbefore->add_cont("ui.add_data_grid_colum(".$coljs->get_as_js_val().");\n");	
		}
		
		$jsbefore->add_cont("ui.datagrid_man.set_ds_from_optim(".$dataoptim->get_as_js_val().");\n");	
		//$coljs=$datagrid->new_helper_mnu_colum_js("_mnu");
		
		//new_helper_mnu_colum_js

		
		$jsbefore->add_cont("ui.init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$jsbefore->add_cont("ui.set_container();\n");
		//$js=$datagrid->new_js_doc_ready();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont($jsbefore,true);
		
		$jsbefore->add_cont("ui.create_data_grid();\n");	
		$js->add_cont("ui.set_grid('".$datagrid->container_id."')");

		//$datagrid->prepare_js_props();
		
		//echo nl2br($js->get_as_js_val());

		echo $js->get_js_script_html();

		
		
		return;
		//old mode
		
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		$this->proc_input($input);
		//mw_array2list_echo($man->get_all_active_items());
		
		$frm=$this->new_frm();
		$cr=$this->new_datafield_creator();
		$cr->items_pref="nd";
		if($items=$man->get_all_items()){
			$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("items"));
			foreach($items as $cod=>$item){
				$gritem=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle($cod,$item->get_name()));
				$item->set_admin_inputs($gritem);
				
			}
		}
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle("new",$this->lng_common_get_msg_txt("create_group","Crear grupo")));
		$man->set_new_item_inputs($gr);
		$cr->add_submit($this->lng_common_get_msg_txt("save","Guardar"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html();

		

		
		/*
		if(!$users=$uman->get_all_useres()){
			
			echo "<p>".$this->lng_common_get_msg_txt("no_users","No hay usuarios.")."</p>";	
			return false;	
		}
		$datagrid=new mwmod_mw_devextreme_widget_datagrid("datagridtest");
		$datagrid->setFilerVisible();
		$col=$datagrid->add_column_html("_mnu"," ");
		$col->js_data->set_prop("allowFiltering",false);
		$col->js_data->set_prop("allowSorting",false);
		$col->js_data->set_prop("width",30);
		
		$col=$datagrid->add_column_number("id","ID");
		$col->js_data->set_prop("width",60);
		$col=$datagrid->add_column_string("name",$this->lng_common_get_msg_txt("user","Usuario"));
		$col=$datagrid->add_column_string("complete_name",$this->lng_common_get_msg_txt("name","Nombre"));
		$col=$datagrid->add_column_string("email",$this->lng_common_get_msg_txt("email","Email"));
		$col=$datagrid->add_column_boolean("active",$this->lng_common_get_msg_txt("active","Activo"));
		$col->js_data->set_prop("filterValue",true);
		
		
		foreach($users as $id=>$user){
			$data=$user->get_public_tbl_data();
			
			//$url=$this->get_url_subinterface("edituser",array("iditem"=>$id));
			//$mnuelem->add_item_by_item(new mwmod_mw_mnu_items_bticon("edit",$this->lng_common_get_msg_txt("data","Datos"),$url,"pencil",$mnuelem));
			$url=$this->get_url_subinterface("userfulldata",array("iditem"=>$id));
			$mnuelem=new mwmod_mw_mnu_mnu("mnuitem");
			$mnuelem->add_item_by_item(new mwmod_mw_mnu_items_bticon("userfulldata",$this->lng_common_get_msg_txt("edit","Editar"),$url,"pencil",$mnuelem));
			
			$data["_mnu"]=$mnuelem->get_html_for_tbl();
			
			$datagrid->add_data($data);	
		}
		
		//$datagrid->add_data_from_list($dd);
		echo $datagrid->get_html_container();
		$js=$datagrid->new_js_doc_ready();
		echo $js->get_js_script_html();
		*/

		return;
		
		

		
	}
	

	function do_exec_no_sub_interface(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		
		$uipreparer=new mwmod_mw_html_manager_uipreparers_ajax($this);
		$uipreparer->preapare_ui();


		$jsman=$this->maininterface->jsmanager;
		/*
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		
		*/
		
		
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("ui/users/admin_groups.js");
		
		

	}
	function proc_input($input){
		if(!$input){
			return false;	
		}
		if(!$uman=$this->mainap->get_user_manager()){
			return false;	
		}
		if(!$man=$uman->get_groups_man()){
			return false;	
		}
		
		if(!$input->is_req_input_ok()){
			return false;	
		}
		if($input->get_value_by_dot_cod("new.data.name")){
			if($nd=$input->get_value_by_dot_cod_as_list("new")){
				//mw_array2list_echo($nd);
				$man->create_new_item_from_full_input($nd);
			}
		}
		if($newdata=$input->get_value_by_dot_cod_as_list("items")){
			foreach($newdata as $id=>$d){
				if($item=$man->get_item($id)){
					$item->save($d);	
				}
			}
		}
		
		//
		
		
	}
	function execfrommain_getcmd_sxml_newitem($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$uman=$this->mainap->get_user_manager()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$man=$uman->get_groups_man()){
			$xml->root_do_all_output();
			return false;	
		}
		
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		if(!$input->is_req_input_ok()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$nd=$input->get_value_as_list()){
			$xml->root_do_all_output();
			return false;	
		}
		unset($nd["id"]);
		if(!$nd["name"]){
			$xml->root_do_all_output();
			return false;	
		}
		$nd["active"]=$nd["active"]+0;
		if(!$item=$man->create_new_item($nd)){
			$xml->root_do_all_output();
			return false;	
		}
		$xml->set_prop("ok",true);
		$xml->set_prop("itemid",$item->get_id());
		$nd=$item->get_data();
		if($nd["active"]){
			$nd["active"]=true;	
		}else{
			$nd["active"]=false;	
		}
		$xml->set_prop("itemdata",$nd);
		$xml->root_do_all_output();

	}
	
	function execfrommain_getcmd_sxml_saveitem($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$uman=$this->mainap->get_user_manager()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$man=$uman->get_groups_man()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$item=$man->get_item($_REQUEST["itemid"])){
			$xml->root_do_all_output();
			return false;	
				
		}
		
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		if(!$input->is_req_input_ok()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$nd=$input->get_value_as_list()){
			$xml->root_do_all_output();
			return false;	
		}
		unset($nd["id"]);
		if(!$nd["name"]){
			unset($nd["name"]);	
		}
		$nd["active"]=$nd["active"]+0;
		$item->do_save_data($nd);
		$xml->set_prop("ok",true);
		$xml->set_prop("itemid",$item->get_id());
		$xml->set_prop("itemdata",$item->get_data());
		$xml->root_do_all_output();
		
		//$item->tem

	}
	function is_allowed(){
		return $this->allow("adminusers");	
	}
	
}
?>