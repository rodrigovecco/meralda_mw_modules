<?php
class mwmod_mw_addon_schev_ui_main extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent,$man){
		$this->set_items_man($man);
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("scheduled_tasks","Tareas programadas"));
		$this->js_ui_class_name="mw_ui_grid";
		$this->debug_mode=true;
		
	}
	
	
	function do_exec_page_in(){
		/*
		$helper= new mwmod_mw_ap_helper();
		
		mw_array2list_echo($helper->dateman->get_months());
		mw_array2list_echo($helper->dateman->get_weekdays());
		*/
		
		$container=$this->get_ui_dom_elem_container();
		
		$gridcontainer=$this->set_ui_dom_elem_id("datagrid_container");
		$body=$this->set_ui_dom_elem_id("datagrid_body");
		$loading=new mwcus_cus_templates_html_loading_placeholder();
		$body->add_cont($loading);
		$gridcontainer->add_cont($body);
		
		$container->add_cont($gridcontainer);
		echo $container->get_as_html();
		
		//
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		$deletefnc=new mwmod_mw_jsobj_functionext();
		$deletefnc->add_fnc_arg("mnuitem");
		$deletefnc->add_cont("var id_item=mnuitem.get_data('id');\n");
		$deletefnc->add_cont("if(!id_item){return false};\n");
		$jsdialog=new mwmod_mw_jsobj_newobject("mw_dx_dialog");
		
		$dialogdec=new mwmod_mw_jsobj_vardeclaration("dialog",$jsdialog);
		$jsdialog->set_prop("message",$this->lng_get_msg_txt("delete_scheduled_task","Eliminar tareas programada"));
		
		
		$deletefncdo=new mwmod_mw_jsobj_functionext();
		
		//$deletefncdo->add_cont("alert(id_item);\n");
		$deletefncdo->add_cont("if({$var}.gridLoader){\n");
		$deletefncdo->add_cont("{$var}.gridLoader.set_cont_xml_url({$var}.get_xmlcmd_url('loaddatagrid',{del_item:id_item}));\n");
		$deletefncdo->add_cont("{$var}.gridLoader.loadCont();\n");
		$deletefncdo->add_cont("}\n");
		
		
		
		$jsdialog->set_prop("onYes",$deletefncdo);
		$deletefnc->add_cont($dialogdec);
		
		$deletefnc->add_cont("dialog.params.set_param(mnuitem.get_data('name'),'title');\n");
		$deletefnc->add_cont("dialog.confirm();\n");
		
		
		$js->add_cont($var.".onDeleteCl=".$deletefnc->get_as_js_val().";\n");
		
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$js->add_cont($var.".loadGridManager();\n");
		echo $js->get_js_script_html();

	}
	function do_exec_no_sub_interface(){
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
		

	}
	function execfrommain_getcmd_sxml_loaddatagrid($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		$xml->set_prop("htmlcont",$this->lng_get_msg_txt("not_allowed","No permitido"));
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;
	
		}
		if(!$this->items_man){
			$xml->root_do_all_output();
			return false;
				
		}
		if($id_del=$_REQUEST["del_item"]){
			if($item=$this->items_man->get_item($id_del)){
				$item->delete();	
			}
		}
		
		$xml->set_prop("ok",true);
		$xml->set_prop("htmlcont","");
		
		$var=$this->get_js_ui_man_name();

		$datagrid=new mwmod_mw_devextreme_widget_datagrid(false);
		$datagrid->setFilerVisible();
		$datagrid->js_props->set_prop("columnAutoWidth",true);	
		$datagrid->js_props->set_prop("allowColumnResizing",true);	

		

		
		$col=$datagrid->add_column_number("id","ID");
		$col->js_data->set_prop("width",60);
		$col->js_data->set_prop("visible",false);
		
		$col=$datagrid->add_column_string("name",$this->lng_common_get_msg_txt("name","Nombre"));
		$urlbase=$this->get_url_subinterface("selitem");
		$col->set_link_mode($urlbase,array("iditem"=>"id"));
		

		$colowner=$datagrid->add_column_number("owner",$this->lng_common_get_msg_txt("user","Usuario"));		
		
		
		
		$col=$datagrid->add_column_boolean("active",$this->lng_common_get_msg_txt("active","Activo"));
		
		

		$usersid=array();
		$dataoptim=$datagrid->new_dataoptim_data_man();
		if($items=$this->items_man->get_all_items()){
			foreach($items as $cod=>$item){
				if($uid=$item->get_data("owner")){
					$usersid[$uid]=$uid;	
				}
				
				$dataoptim->add_data($item->get_data());	
				
			}
		}
		if($uman=$this->mainap->get_user_manager()){
			$udslookup=$colowner->set_lookup("id","name");
			if($users=$uman->get_users_by_ids($usersid)){
				foreach($users as $idu=>$u){
					$udslookup->add_data(array("id"=>$idu,"name"=>$u->get_name()));	
				}
			}
		}
		$columns=$datagrid->columns->get_items();
		$gridhelper=$datagrid->new_mw_helper_js();
		$list=$gridhelper->get_array_prop("columns");
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$list->add_data($coljs);
			
		}
		
		$coljs=new mwmod_mw_devextreme_widget_datagrid_helper_mnu_column();
		$coljs->set_option("width",100);
		$colmnuitem=$coljs->new_mnu_item_edit($this->get_url_subinterface("selitem"));
		$colmnuitem->set_prop("iconClass","glyphicon glyphicon-share-alt");	
		$colmnuitem->set_prop("lbl",$this->lng_common_get_msg_txt("view","Ver"));	
		$coljs->add_mnu_item($colmnuitem);
		$colmnuitem=$coljs->new_mnu_item_def("del","glyphicon glyphicon-remove",$this->lng_common_get_msg_txt("delete","Eliminar"));
		//$colmnuitem->set_prop("iconClass","glyphicon glyphicon-share-alt");	
		//$colmnuitem->set_prop("lbl",$this->lng_common_get_msg_txt("view","Ver"));	
		
		$deletefnc=new mwmod_mw_jsobj_functionext();
		$deletefnc->add_fnc_arg("mnuitem");
		$deletefnc->add_cont("{$var}.onDeleteCl(mnuitem);\n");
		$deletefnc->add_cont("return false;\n");
		$colmnuitem->set_prop("onclick",$deletefnc);

		
		$coljs->add_mnu_item($colmnuitem);
		
		$list->add_data($coljs);
		
		
		
		$gridhelper->set_prop("dsoptim",$dataoptim);
		$js=new mwmod_mw_jsobj_obj();
		$js->set_prop("datagridman",$gridhelper);
		$xml_js=new mwmod_mw_data_xml_js("jsresponse",$js);
		$xml->add_sub_item($xml_js);
		$xml->root_do_all_output();
		
		
		
		
			
	}
	
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function is_allowed(){
		if($this->items_man){
			return $this->items_man->is_allowed_ui($this);	
		}
		return false;
	}
	
	function load_all_subinterfases(){
		
		$si=$this->add_new_subinterface(new mwmod_mw_addon_schev_ui_new("new",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_addon_schev_ui_selitem("selitem",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_addon_schev_ui_debug("debug",$this));
		
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		$this->add_2_mnu($mnu);
		if($subs=$this->get_subinterfaces_by_code("new,debug",true)){
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);		
			}
		}
		
		return $mnu;
	}
	
	

}
?>