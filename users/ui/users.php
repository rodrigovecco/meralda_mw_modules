<?php
class mwmod_mw_users_ui_users extends mwmod_mw_ui_sub_uiabs{
	public $queryHelper;//mwmod_mw_devextreme_data_queryhelper iniciado en getQuery
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_common_get_msg_txt("users","Usuarios"));
		$this->js_ui_class_name="mw_ui_grid_remote";
		$this->mnuIconClass="fa fa-users";
		
		
	}
	function getQuery(){
		
		$this->queryHelper=new mwmod_mw_devextreme_data_queryhelper();
		if(!$man=$this->getUman()){
			return false;
		}
		if(!$tblman=$man->get_tblman()){
			return false;	
		}
		if(!$query=$tblman->new_query()){
			return false;	
		}
		//$this->queryHelper->addAllTblFields($tblman);
		if($tblfields=$tblman->getFields()){
			foreach($tblfields as $c=>$f){
				if($c!="pass"){
					if($item=$this->queryHelper->addFieldBySqlExp($c,$tblman->tbl.".".$c)){
						$item->setOptionsByField($f);	
						
					}
				}
			}
		}
		
		
		$this->afterGetQuery($query);
		return $query;
	}
	
	function execfrommain_getcmd_sxml_loaddata($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		if($man=$this->get_admin_user_manager()){
			$xml->set_prop("uman",get_class($man));	
		}
		//$xml->set_prop("htmlcont",$this->lng_get_msg_txt("not_allowed","No permitido"));
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;
	
		}
		if(!$man=$this->getUman()){
			$xml->root_do_all_output();
			return false;
		}
		if(!$query=$this->getQuery()){
			$xml->root_do_all_output();
			return false;
		}
		
		$xml->set_prop("ok",true);
		$js=new mwmod_mw_jsobj_obj();
		//$xml->set_prop("debug.sqlbefore",$query->get_sql());
		//$xml->set_prop("debug.loadoptions",$_REQUEST["lopts"]);
		$dataqueryhelper=$this->queryHelper;
		$dataqueryhelper->setLoadOptions($_REQUEST["lopts"]);
		//$xml->set_prop("debug.dataqueryhelper",$dataqueryhelper->getDebugData());
		$dataqueryhelper->aplay2Query($query);
		if(!$dataqueryhelper->sorted){
			$this->setDefaultQuerySort($query);
		}
		$xml->set_prop("debug.sql",$query->get_sql());
		
		$js->set_prop("totalCount",$query->get_total_regs_num());
		
		
		
		$dataoptim=new mwmod_mw_jsobj_dataoptim();
		$dataoptim->set_key("id");
		$js->set_prop("dsoptim",$dataoptim);
		if($items=$man->get_users_by_query($query)){
			foreach($items as $id=>$item){
				$data=$this->get_item_data($item);
				$dataoptim->add_data($data);	
			}
			
		}
		$xml_js=new mwmod_mw_data_xml_js("js",$js);
		
		
		$xml->add_sub_item($xml_js);
		$xml->root_do_all_output();
		
		
		//new_users_query
		
			
	}
	function setDefaultQuerySort($query){
		$query->order->add_order("name");
	}
	
	
	
	function do_exec_page_in(){
		$container=$this->get_ui_dom_elem_container();

		$gridcontainer=$this->set_ui_dom_elem_id("datagrid_container");
		$body=$this->set_ui_dom_elem_id("datagrid_body");
		$loading=new mwcus_cus_templates_html_loading_placeholder();
		$body->add_cont($loading);
		$gridcontainer->add_cont($body);
		
		$container->add_cont($gridcontainer);
		
		$this->getBotHtml($container);

		echo $container->get_as_html();
		
		//
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$jsui=$this->new_ui_js();
		$var=$this->get_js_ui_man_name();
		$js->add_cont("var {$var}=".$jsui->get_as_js_val().";\n");
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		
		echo $js->get_js_script_html();
		return;
		
		

		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		$this->add_2_mnu($mnu);
		if($subs=$this->get_subinterfaces_by_code("new,admingroups",true)){
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		return $mnu;
	}
	
	
	function add_mnu_items($mnu){
		$this->add_2_mnu($mnu);
		$this->add_sub_interface_to_mnu_by_code($mnu,"new");
		
		
	}
	function load_all_subinterfases(){
		
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_newuser("new",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_user("user",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_user_fulldata("userfulldata",$this));
		if($uman=$this->getUman()){
			if($grman=$uman->get_groups_man()){
				$grman->add_admin_interface($this);	
			}
		}
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
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_adv.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_cols.js");
		
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem.js");
		
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_rdata.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_data.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem/devextreme_datagrid.js");
		
		
		$this->add_req_js_scripts();	
		$this->add_req_css();

	}
	function getUman(){
		return 	$this->mainap->get_user_manager();
	}
	function execfrommain_getcmd_sxml_loaddatagrid($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		$xml->set_prop("htmlcont",$this->lng_get_msg_txt("not_allowed","No permitido"));
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;
	
		}
		if(!$uman=$this->getUman()){
			$xml->root_do_all_output();
			return false;	
		}
		
		$xml->set_prop("ok",true);
		$xml->set_prop("htmlcont","");
		
		$var=$this->get_js_ui_man_name();

		$datagrid=new mwmod_mw_devextreme_widget_datagrid(false);
		$datagrid->setFilerVisible();
		$datagrid->js_props->set_prop("columnAutoWidth",true);	
		$datagrid->js_props->set_prop("allowColumnResizing",true);
		$datagrid->js_props->set_prop("paging.pageSize",20);
		$datagrid->js_props->set_prop("remoteOperations.paging",true);
		$datagrid->js_props->set_prop("remoteOperations.filtering",true);
		$datagrid->js_props->set_prop("remoteOperations.sorting",true);
		//$datagrid->js_props->set_prop("editing.allowUpdating",true);
		//$datagrid->js_props->set_prop("editing.allowAdding",true);
		//$datagrid->js_props->set_prop("editing.allowDeleting",true);
		//$datagrid->js_props->set_prop("editing.mode","row");
		//$datagrid->js_props->set_prop("editing.mode","cell");
		
		
		$gridhelper=$datagrid->new_mw_helper_js();
		$datagrid->mw_helper_js_set_rdata_mode_from_ui($this,$gridhelper);
		
		//$datagrid->mw_helper_js_set_editrow_mode_from_ui($this,$gridhelper,false,false,false);


		

		$this->add_cols($datagrid);
		

		$columns=$datagrid->columns->get_items();

		$list=$gridhelper->get_array_prop("columns");
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$list->add_data($coljs);
			
		}
		
		$list->add_data($coljs);
		
		
		if($d=$this->getUniqItemsIds()){
			$gridhelper->set_prop("uniqItemsIds",$d);
			//$xml->set_prop("uniqItemsIds",$d);
		}
		
		//$gridhelper->set_prop("dsoptim",$dataoptim);
		$js=new mwmod_mw_jsobj_obj();
		$js->set_prop("datagridman",$gridhelper);
		$xml_js=new mwmod_mw_data_xml_js("jsresponse",$js);
		
		
		$xml->add_sub_item($xml_js);
		$xml->root_do_all_output();
		
		
		
		
			
	}
	function get_item_data($user){
		$data=$user->get_public_tbl_data();
		if($user_rols=$user->get_rols()){
			$data["rols"]=implode(",",array_keys($user_rols));
		}
		$data["groups"]=$user->get_groups_str_list();
		return $data;
	}

	function add_cols($datagrid){
		if(!$uman=$this->getUman()){
			return false;	
		}
		
		$col=$datagrid->add_column_number("id","ID");
		$col->js_data->set_prop("width",60);
		$col->js_data->set_prop("visible",false);
		$col=$datagrid->add_column_string("name",$this->lng_common_get_msg_txt("user","Usuario"));
		$col->set_link_mode($this->get_url_subinterface("userfulldata"),array("iditem"=>"id"));
		$col=$datagrid->add_column_string("complete_name",$this->lng_common_get_msg_txt("name","Nombre"));
		$col=$datagrid->add_column_string("rols",$this->lng_common_get_msg_txt("rols","Roles"));
		$col->js_data->set_prop("visible",true);
		$col->set_mw_js_colum_class("mw_devextreme_datagrid_column_concatdata");
		$rolsdoptiom=new mwmod_mw_jsobj_dataoptim();
		$rolsdoptiom->set_key();
		if($rolsman=$uman->get_rols_man()){
			if($rols=$rolsman->get_items()){
				foreach($rols as $cod=>$rol){
					$rolsdoptiom->add_data(array("id"=>$cod,"name"=>$rol->get_name()));
					//$rolsgr->add_item(new mwmod_mw_datafield_checkbox($cod,$rol->get_name()));
				}
			}
		}
		$col->get_mw_js_colum_obj();
		$col->mw_js_colum_obj_params->set_prop("dataitems",$rolsdoptiom);
		

		
		/*
		if($groupsman){
			$col=$datagrid->add_column_string("groups",$this->lng_common_get_msg_txt("groups","Grupos"));
			$col->js_data->set_prop("visible",false);	
		}
		*/
		//$col=$datagrid->add_column_string("groups",$this->lng_common_get_msg_txt("email","Email"));
		$col=$datagrid->add_column_boolean("active",$this->lng_common_get_msg_txt("active","Activo"));
		$col->js_data->set_prop("filterValue",true);
		
		
			
	}
	
	function is_allowed(){
		return $this->allow("adminusers");	
	}
	
}
?>