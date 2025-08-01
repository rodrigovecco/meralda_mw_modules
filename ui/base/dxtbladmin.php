<?php
/**
 * Abstract class for DevExtreme-based admin data tables in the UI.
 * Handles data loading, saving, item creation, deletion, user preferences, and JS initialization.
 */
abstract class mwmod_mw_ui_base_dxtbladmin extends mwmod_mw_ui_base_basesubui{
	public $queryHelper;//mwmod_mw_devextreme_data_queryhelper iniciado en getQuery
	public $defPageSize=20;
	public $editingMode="row";
	public $excelExportName;
	public $columnsChooserEnabled=false;
	public $addColCodsToExcel=false;


	//public $userColsFiltersRememberEnabled=false;
	public $userColsOrderRememberEnabled=false;



	public	$userColsSelectedRememberEnabled=false;
	
	public	$userColsSelectedRememberEnabledVisible=false;
	public	$userColsPrefResetBtnEnabled=false;
	public	$clearFiltersBtnEnabled=false;
	public	$rememberFiltersBtnEnabled=false;
	public $userFiltersRemember=false;

	public $toolbarItemsExportButtonAdded=false;
	function setUserFilterRememberEnabledMode(){
		
		$this->clearFiltersBtnEnabled=true;
		$this->userFiltersRemember=true;
		$this->rememberFiltersBtnEnabled=true;

	}
	function setUserColsSelectedRememberEnabledMode(){
		
		$this->columnsChooserEnabled=true;
		$this->userColsSelectedRememberEnabled=true;
		$this->userColsSelectedRememberEnabledVisible=true;
		$this->userColsPrefResetBtnEnabled=true;
		//$this->userColsFiltersRememberEnabled=true;
		$this->userColsOrderRememberEnabled=true;
	}
	
	function userColsSelectedRememberEnabledVisibleIndex(){
		return $this->userColsSelectedRememberEnabledVisible;
	}
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title("Some UI");
		$this->js_ui_class_name="mw_ui_grid_remote";
		$this->editingMode="row";
		
	}
	function allowSaveColsState(){
		if($this->userColsSelectedRememberEnabled){
			return $this->is_allowed();	
		}
	}
	function allowSaveFilters(){
		if($this->userFiltersRemember){
			return $this->is_allowed();	
		}
	}

	function execfrommain_getcmd_sxml_savefilters($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		if(!$this->allowSaveFilters()){
			$xml->root_do_all_output();
			return false;	
		}
		
		
		if(!$dataItem=$this->get_user_ui_data("filters")){
			$xml->root_do_all_output();
			return false;	
		}
		//$xml->set_prop("R",$_GET);
		$input=new mwmod_mw_helper_inputvalidator_request("filters");
		//todo: other filters
		if(!$input->is_req_input_ok()){
			$xml->set_prop("error","no input");
			$xml->root_do_all_output();
			return false;	
		}
		if(!$nd=$input->get_value_by_dot_cod_as_list("cols")){
			$xml->set_prop("error","no input cols");
			$xml->root_do_all_output();
			return false;	
		}
		$xml->set_prop("newdata",$nd);
		$chaged=false;
		foreach($nd as $cod=>$val){
			if(is_array($val)){
				if($this->check_str_key_alnum_underscore($cod)){
					
					$dataItem->set_data($val,"cols.".$cod."");
					$chaged=true;
					
					
					
					
				
				}
			}

			
		}
		if($chaged){
			$dataItem->set_data(date("Y-m-d H:i:s"),"updatedTime");

			$dataItem->save();
		}



		
		
		
		$xml->set_prop("ok",true);
		
		$xml->set_prop("filters",$dataItem->get_data());
		$xml->set_prop("notify.message",$this->lng_common_get_msg_txt("MSGfilteresSaved","Estado de filtros guardado correctamente."));
		$xml->set_prop("notify.type","success");
	
		
		$xml->root_do_all_output();

	}
	function execfrommain_getcmd_sxml_resetcolsstate($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		if(!$this->allowSaveColsState()){
			$xml->root_do_all_output();
			return false;	
		}
		
		
		if(!$dataItem=$this->get_user_ui_data("colsstate")){
			$xml->root_do_all_output();
			return false;	
		}
		$dataItem->set_data(array());
		$dataItem->save();

		
		$js=new mwmod_mw_jsobj_obj();
		$xml_js=new mwmod_mw_data_xml_js("js",$js);		
		$xml->add_sub_item($xml_js);



	
		
		$xml->set_prop("notify.message",$this->lng_common_get_msg_txt("MSGcolumnsPrefReseted","Se restablecieron las preferencias de columnas. Por favor, actualizar la página."));
		$xml->set_prop("notify.type","warning");
		
		$xml->set_prop("ok",true);
		
		$xml->set_prop("colsstate",$dataItem->get_data());
	
		
		$xml->root_do_all_output();

	}
	function execfrommain_getcmd_sxml_savecolsstate($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		if(!$this->allowSaveColsState()){
			$xml->root_do_all_output();
			return false;	
		}
		
		
		if(!$dataItem=$this->get_user_ui_data("colsstate")){
			$xml->root_do_all_output();
			return false;	
		}
		$input=new mwmod_mw_helper_inputvalidator_request("cols");
		if(!$input->is_req_input_ok()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$nd=$input->get_value_as_list()){
			$xml->root_do_all_output();
			return false;	
		}
		$xml->set_prop("newdata",$nd);
		$chaged=false;
		foreach($nd as $cod=>$val){
			if(is_array($val)){
				if($this->check_str_key_alnum_underscore($cod)){
					unset($val["filterValue"]);
					unset($val["selectedFilterOperation"]);
					$dataItem->set_data($val,"cols.".$cod."");
					$chaged=true;
					
					
					
					
				
				}
			}

			
		}
		if($chaged){
			$dataItem->set_data(date("Y-m-d H:i:s"),"updatedTime");

			$dataItem->save();
		}



		
		
		
		$xml->set_prop("ok",true);
		
		$xml->set_prop("colsstate",$dataItem->get_data());
	
		
		$xml->root_do_all_output();

	}
	function getDefPageSize(){
		return $this->defPageSize;	
	}
	/**
	 * @return mwmod_mw_manager_man
	 */
	function getItemsMan(){
		return $this->items_man;
	}
	function allowDelete(){
		return $this->allow_admin();
	}
	function allowInsert(){
		return $this->allow_admin();
	}
	function allowUpdate(){
		return $this->allow_admin();
	}
	function allowDeleteItem($item){
		return $this->allowDelete();
	}
	function allowUpdateItem($item){
		return $this->allowUpdate();
	}
	/**
     * @return false|mwmod_mw_db_sql_query
     */
	function getQuery(){
		
		$this->queryHelper=new mwmod_mw_devextreme_data_queryhelper();
		if(!$man=$this->getItemsMan()){
			return false;
		}
		if(!$tblman=$man->get_tblman()){
			return false;	
		}
		if(!$query=$tblman->new_query()){
			return false;	
		}
		$this->queryHelper->addAllTblFields($tblman);
		$this->afterGetQuery($query);
		return $query;
	}
	/**
     * Hook to customize the query.
     * @param mwmod_mw_db_sql_query $query
     */
	function afterGetQuery($query){
		//extender	
	}
	function getQueryFromReq(){
		if(!$query=$this->getQuery()){
			return false;	
		}
		return $query;
			
	}
	function get_item_data($item){
		$r=$item->getDataForDXtbl();
		return $r;
	}
	

	function execfrommain_getcmd_sxml_loaddata($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		//$xml->set_prop("htmlcont",$this->lng_get_msg_txt("not_allowed","No permitido"));
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;
	
		}
		if(!$man=$this->getItemsMan()){
			$xml->root_do_all_output();
			return false;
		}
		if(!$query=$this->getQueryFromReq()){
			$xml->root_do_all_output();
			return false;
		}
		
		$xml->set_prop("ok",true);
		$js=new mwmod_mw_jsobj_obj();
		//$xml->set_prop("debug.sqlbefore",$query->get_sql());
		//$xml->set_prop("debug.loadoptions",$_REQUEST["lopts"]);
		$dataqueryhelper=$this->queryHelper;
		$dataqueryhelper->setLoadOptions($_REQUEST["lopts"]??null);
		//$xml->set_prop("debug.dataqueryhelper",$dataqueryhelper->getDebugData());
		$dataqueryhelper->aplay2Query($query);
		if(!$dataqueryhelper->sorted){
			$this->setDefaultQuerySort($query);
		}
		if($this->debugOutputEnabled()){
			$xml->set_prop("debug.sql",$query->get_sql());
		}

		$totalCount=0;
		if($totaldata=$query->get_total_data()){
			if(isset($totaldata[$query->sql_count_name])){
				$totalCount=intval($totaldata[$query->sql_count_name]);
				unset($totaldata[$query->sql_count_name]);
				if(sizeof($totaldata)>0){
					$summary=$js->get_array_prop("summary");
					foreach($totaldata as $k=>$v){
						$summary->add_data($v);
					}
				}

			}
		}

		
		$js->set_prop("totalCount",$totalCount);
		
		
		//$js->set_prop("totalCount",$query->get_total_regs_num());
		
		
		
		$dataoptim=new mwmod_mw_jsobj_dataoptim();
		$dataoptim->set_key("id");
		$js->set_prop("dsoptim",$dataoptim);
		if($items=$man->get_items_by_query($query)){
			foreach($items as $id=>$item){
				//$xml->set_prop("debug.item.".$id,"d");
				$data=$this->get_item_data($item);
				$dataoptim->add_data($data);	
			}
			
		}
		$xml_js=new mwmod_mw_data_xml_js("js",$js);
		
		
		$xml->add_sub_item($xml_js);
		$xml->root_do_all_output();
		
		
		//
		
			
	}
	function setDefaultQuerySort($query){
		//extender
	}
	function getBotHtml($container){
		
	}
	function getTopHtml($container){
		
	}
	
	
	function do_exec_page_in(){
		$MainContainer=$this->get_ui_dom_elem_container();
		$container=$MainContainer;
		if($this->mainPanelEnabled){
			if($mainpanel=$this->createMainPanel()){
				$MainContainer->add_cont($mainpanel);
				$container=$mainpanel->panel_body->add_cont_elem();
			}
		}





		//$container=$this->get_ui_dom_elem_container();

		$gridcontainer=$this->set_ui_dom_elem_id("datagrid_container");
		$body=$this->set_ui_dom_elem_id("datagrid_body");
		$loading=new mwcus_cus_templates_html_loading_placeholder();
		$body->add_cont($loading);
		$gridcontainer->add_cont($body);
		$this->getTopHtml($container);
		$container->add_cont($gridcontainer);
		
		$this->getBotHtml($container);

		echo $MainContainer->get_as_html();
		
		//
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		
		

		//$this->setDefaultJSinitParams();
		
		//$jsui=$this->new_ui_js();
		$var=$this->get_js_ui_man_name();
		//$js->add_cont("var {$var}=".$jsui->get_as_js_val().";\n");
		
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		
		echo $js->get_js_script_html();
		return;
		
		

		
	}
	function set_ui_js_params(){
		$this->setDefaultJSinitParams();
	}
	function setDefaultJSinitParams(){
		if($this->userColsSelectedRememberEnabled){
			$this->ui_js_init_params->set_prop("userColsSelectedRememberEnabled",true);
		}
		if($this->userFiltersAndOrderRememberEnabled){
			$this->ui_js_init_params->set_prop("userFiltersAndOrderRememberEnabled",true);
		}
		if($this->excelExportName){
			$this->ui_js_init_params->set_prop("excelExportName",$this->excelExportName);
		}
		///$this->ui_js_init_params->set_prop("excelExportName",$this->excelExportName);

	}
	function before_exec(){
		$util=new mwmod_mw_devextreme_util();
		if($this->excelExportName){
			$util->preapare_ui_exportExcel($this);
		}
		$util->preapare_ui_webappjs($this);
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_adv.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_cols.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_rdata.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_data.js");

		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem/devextreme_datagrid.js");
		
		$this->add_req_js_scripts();	
		$this->add_req_css();
		$item=$this->create_js_man_ui_header_declaration_item();
		$jsman->add_item_by_item($item);

	}
	function execfrommain_getcmd_sxml_newitem($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$this->allowInsert()){
			$xml->root_do_all_output();
			return false;	
		}
		
		if(!$man=$this->items_man){
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
		//$xml->set_prop("nd",$nd);
		unset($nd["id"]);
		if(!$this->check_before_create_item($nd,$xml)){
			$xml->root_do_all_output();
			return false;
		}
		if(!$item=$this->create_new_item($nd)){
			$xml->set_prop("notify.message",$this->lng_get_msg_txt("unableToCreateElement","No se pudo crear el elemento."));
			$xml->set_prop("notify.type","error");
			$xml->root_do_all_output();
			return false;	
		}
		$xml->set_prop("ok",true);
		$xml->set_prop("itemid",$item->get_id());
		$xml->set_prop("itemdata",$this->get_item_data($item));
		$xml->set_prop("notify.message",$item->get_name()." ".$this->lng_get_msg_txt("LCcreated","creado"));
		$xml->set_prop("notify.type","success");
		
		$xml->root_do_all_output();

	}
	function check_before_create_item($nd,$xml){
		//false if data is missing or there is a duplicate key
		return true;
	}
	function create_new_item($nd){
		if($man=$this->items_man){
			
			return $man->create_new_item($nd);	
		}
	}
	function execfrommain_getcmd_sxml_deleteitem($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$this->allowDelete()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$man=$this->__get_priv_items_man()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$item=$this->getOwnItem($_REQUEST["itemid"]??null)){
			$xml->root_do_all_output();
			return false;	
				
		}
		if(!$this->allowDeleteItem($item)){
			$xml->root_do_all_output();
			return false;	
		}

		
		$this->delete_item($item,$xml);
		if($d=$this->getUniqItemsIds()){
			$xml->set_prop("uniqItemsIds",$d);
		}
		
		$xml->root_do_all_output();
		

	}
	function delete_item($item,$xmlresponse){
		$xmlresponse->set_prop("itemid",$item->get_id());
		if($relman=$item->get_related_objects_man()){
			if($relman->get_rel_objects_num()){
				if($msg=$relman->get_relations_msg_plain()){
					$msg.="\n".$this->lng_get_msg_txt("cant_eliminate","No se pudo eliminar")." ".$item->get_name();
					$xmlresponse->set_prop("notify.message",$msg);
					$xmlresponse->set_prop("notify.type","error");
					$xmlresponse->set_prop("notify.multiline",true);
					
					
						
				}else{
					$xmlresponse->set_prop("notify.message",$this->lng_get_msg_txt("cant_eliminate","No se pudo eliminar")." ".$item->get_name());
					$xmlresponse->set_prop("notify.type","error");
						
				}
				return false;	
			}
				
		}
		$item->do_delete();
		$xmlresponse->set_prop("ok",true);
		$xmlresponse->set_prop("notify.message",$item->get_name()." ".$this->lng_get_msg_txt("LCdeleted","eliminado"));
		$xmlresponse->set_prop("notify.type","success");
		return true;

			
	}
	function execfrommain_getcmd_sxml_saveitem($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$this->allow_admin()){
			$xml->root_do_all_output();
			return false;	
		}
		
		if(!$man=$this->__get_priv_items_man()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$item=$this->getOwnItem($_REQUEST["itemid"]??null)){
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
		if(!$this->allowUpdateItem($item)){
			$xml->root_do_all_output();
			return false;	
		}
		
		//$item->do_save_data($nd);
		$this->saveItem($item,$nd);
		$xml->set_prop("ok",true);
		$xml->set_prop("itemid",$item->get_id());
		$xml->set_prop("itemdata",$this->get_item_data($item));
		
		if($d=$this->getUniqItemsIds()){
			$xml->set_prop("uniqItemsIds",$d);
		}
		
		$xml->root_do_all_output();
		
		//$item->tem

	}
	function saveItem($item,$nd){
		unset($nd["id"]);
		if(isset($nd["name"])and(!$nd["name"])){
			unset($nd["name"]);	
		}
		$item->do_save_data($nd);
		
	}
	function getOwnItem($id){
		if($man=$this->__get_priv_items_man()){
			if($item=$man->get_item($id)){
				if($this->isItemAllowed($item)){
					return $item;	
				}
			}
			
			
		}
	

	}
	function isItemAllowed($item){
		return $item;
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
		
		$xml->set_prop("ok",true);
		$xml->set_prop("htmlcont","");
		
		$var=$this->get_js_ui_man_name();

		$datagrid=new mwmod_mw_devextreme_widget_datagrid(false);
		$datagrid->setFilerVisible();
		if($this->excelExportName){
			$datagrid->js_props->set_prop("export.enabled",true);	
			$datagrid->js_props->set_prop("export.fileName",$this->excelExportName);
			
			
			
			
		}
		
		$datagrid->js_props->set_prop("columnAutoWidth",true);	
		$datagrid->js_props->set_prop("allowColumnResizing",true);
		if($this->allowUpdate()){
			$datagrid->js_props->set_prop("editing.allowUpdating",true);
			$datagrid->js_props->set_prop("editing.mode",$this->editingMode);
		}
		if($this->allowInsert()){
			$datagrid->js_props->set_prop("editing.allowAdding",true);
		}
		if($this->allowDelete()){
			$datagrid->js_props->set_prop("editing.allowDeleting",true);
		}
		$datagrid->js_props->set_prop("editing.useIcons",true);
		
		//$datagrid->js_props->set_prop("editing.mode","row");
		$datagrid->js_props->set_prop("paging.pageSize",$this->getDefPageSize());
		$datagrid->js_props->set_prop("remoteOperations.paging",true);
		$datagrid->js_props->set_prop("remoteOperations.filtering",true);
		$datagrid->js_props->set_prop("remoteOperations.sorting",true);
		
		
		$gridhelper=$datagrid->new_mw_helper_js();
		
		//$datagrid->mw_helper_js_set_editrow_mode_from_ui($this,$gridhelper,true,true,true);
		$datagrid->mw_helper_js_set_rdata_mode_from_ui($this,$gridhelper);
		$gridhelper->set_prop("dataSourceMan.dataKey","id");//new!!
		$gridhelper->set_fnc_name("mw_devextreme_datagrid_man_rdataedit");

		if($this->addColCodsToExcel){
			$gridhelper->set_prop("addColCodsToExcel",true);	
		}
		

		$this->add_cols($datagrid);
		$this->setColsUserPrefs($datagrid);
		
		//$dataoptim=$datagrid->new_dataoptim_data_man();
		//$dataoptim->set_key("id");
		/*
		if($items=$this->items_man->get_all_items()){
			foreach($items as $id=>$item){
				$data=$this->get_item_data($item);
				$dataoptim->add_data($data);	
			}
		}
			*/
		

		$columns=$datagrid->columns->get_items();

		$list=$gridhelper->get_array_prop("columns");
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$list->add_data($coljs);
			
		}
		
		$list->add_data($coljs);
		
		
		if($d=$this->getUniqItemsIds()){
			$gridhelper->set_prop("uniqItemsIds",$d);
			
		}
		
		
		$this->afterDatagridCreated($datagrid,$gridhelper);
		
		$js=new mwmod_mw_jsobj_obj();
		$js->set_prop("datagridman",$gridhelper);
		$xml_js=new mwmod_mw_data_xml_js("jsresponse",$js);
		
		
		$xml->add_sub_item($xml_js);
		$xml->root_do_all_output();
		
		
		
		
			
	}
	function setColsUserPrefs($datagrid){
		$this->setColsUserPrefsColsOptions($datagrid);
		$this->setColsUserPrefsFilters($datagrid);

		

	}
	function setColsUserPrefsFilters($datagrid){
		if(!$this->userFiltersRemember){
			return false;	
		}
		if(!$dataItem=$this->get_user_ui_data("filters")){
			
			return false;	
		}
		$cols=$datagrid->columns->get_items();
		//var_dump($dataItem->get_data());
		//die("setColsUserPrefs");
		foreach($cols as $col){
			if($col->userColsFiltersRememberEnabled){

				$cod=$col->cod;
				$datacod="cols.".$cod;
				
				
				if($dataItem->is_data_defined($datacod.".filterValue")){
						
					if($col->isDate()){
						if($filterValue=$dataItem->get_data($datacod.".filterValue")){
							if(is_string($filterValue)){
								if($filterValueTime=strtotime($filterValue)){
									$filterValue=date("Y-m-d",$filterValueTime);
									$filterValueObj=new mwmod_mw_jsobj_date($filterValue);
									$col->js_data->set_prop("filterValue",$filterValueObj);
								}
							}
						}

					}else{
						$col->js_data->set_prop("filterValue",$dataItem->get_data($datacod.".filterValue"));
					}
						

					if($dataItem->is_data_defined($datacod.".selectedFilterOperation")){
						$col->js_data->set_prop("selectedFilterOperation",$dataItem->get_data($datacod.".selectedFilterOperation"));

					}
				}
					


					

				
			}
			
			
		}
		

	}
	function setColsUserPrefsColsOptions($datagrid){
		if(!$this->userColsSelectedRememberEnabled){
			return false;	
		}
		if(!$dataItem=$this->get_user_ui_data("colsstate")){
			
			return false;	
		}
		$cols=$datagrid->columns->get_items();
		//var_dump($dataItem->get_data());
		//die("setColsUserPrefs");
		foreach($cols as $col){
			if($col->userColsSelectedRememberEnabled){

				$cod=$col->cod;
				$datacod="cols.".$cod;
				
				if($this->userColsOrderRememberEnabled){
					if($dataItem->is_data_defined($datacod.".sortIndex")){
						if($dataItem->is_data_defined($datacod.".sortOrder")){
							$col->js_data->set_prop("sortIndex",$dataItem->get_data($datacod.".sortIndex"));
							$col->js_data->set_prop("sortOrder",$dataItem->get_data($datacod.".sortOrder"));

						}

					}
					if($dataItem->is_data_defined($datacod.".width")){
						$col->js_data->set_prop("width",$dataItem->get_data($datacod.".width"));

					}
					
				}


				if($this->userColsSelectedRememberEnabledVisible){
					
					
					if($dataItem->is_data_defined($datacod.".visible")){
						//die($datacod);
						if($dataItem->get_data($datacod.".visible")){
							$col->js_data->set_prop("visible",true);
						}else{
							$col->js_data->set_prop("visible",false);
						}
						
					}
					
				}

				if($this->userColsSelectedRememberEnabledVisibleIndex()){
					if($dataItem->is_data_defined($datacod.".visibleIndex")){
						$vv=$dataItem->get_data($datacod.".visibleIndex");
						if(is_numeric($vv)){
							$col->js_data->set_prop("visibleIndex",$vv+0);
						}
						
						
					}

				}
			}
			
			
		}
		

	}
	//20210217
	 /**
     * @param mwmod_mw_devextreme_widget_datagrid $datagrid
     * @param mwmod_mw_devextreme_widget_datagrid_helper_dgman $gridhelper
     */
	function afterDatagridCreatedAddDefaultToolbarItems($datagrid,$gridhelper){
		$var=$this->get_js_ui_man_name();
		
		if($this->columnsChooserEnabled){
			//$datagrid->set_prop("columnsChooserEnabled",true);
			$datagrid->js_props->set_prop("columnChooser.enabled",true);
			$datagrid->js_props->set_prop("columnChooser.mode","select");
			$datagrid->js_props->set_prop("allowColumnReordering",true);
			
		}

		$cusBtnsEnabled=false;
		if($this->userColsSelectedRememberEnabled){
			$cusBtnsEnabled=true;
		}
		if($this->clearFiltersBtnEnabled){
			$cusBtnsEnabled=true;
		}
		
		if($this->rememberFiltersBtnEnabled){
			$cusBtnsEnabled=true;
		}
		if($this->excelExportName){
			$cusBtnsEnabled=true;
		}
			


		
		if($cusBtnsEnabled){
			$tollbarItems=$datagrid->js_props->get_array_prop("toolbar.items");
			if($this->clearFiltersBtnEnabled){
				$this->addBtnClearFilters($datagrid,$gridhelper);
			}
			if($this->rememberFiltersBtnEnabled){
				$this->addBtnSaveFilters($datagrid,$gridhelper);
			}

			if($this->allowInsert()){
				$tollbarItems->add_data("addRowButton");
			}			
			if($this->columnsChooserEnabled){
				$tollbarItems->add_data("columnChooserButton");
			}
			if($this->userColsPrefResetBtnEnabled){
				$this->addBtnColsPrefReset($datagrid,$gridhelper);
			}			
			if($this->excelExportName){

				if(!$this->toolbarItemsExportButtonAdded){
					$tollbarItems->add_data("exportButton");
					$this->toolbarItemsExportButtonAdded=true;
					
				}
			}
			

			

		}

	}
	/**
	 * @param mwmod_mw_devextreme_widget_datagrid $datagrid
	 * @param mwmod_mw_devextreme_widget_datagrid_helper_dgman $gridhelper
	 */
	function afterDatagridCreated($datagrid,$gridhelper){
		$this->afterDatagridCreatedAddDefaultToolbarItems($datagrid,$gridhelper);
			
			
	}
	function addBtnClearFilters($datagrid,$gridhelper){
		$var=$this->get_js_ui_man_name();
		$tollbarItems=$datagrid->js_props->get_array_prop("toolbar.items");
		
		$btn=$tollbarItems->add_data_obj();
		
		$btn->set_prop("locateInMenu","auto");
		$btn->set_prop("widget","dxButton");
		$btn->set_prop("options.text",$this->lng_get_msg_txt("clearFilters","Quitar filtros"));
		$btn->set_prop("options.icon","meralda-icon meralda-icon-filter-clear");
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_cont("{$var}.clearFilters();");
		$btn->set_prop("options.onClick",$fnc);

	}
	function addBtnSaveFilters($datagrid,$gridhelper){
		$var=$this->get_js_ui_man_name();
		$tollbarItems=$datagrid->js_props->get_array_prop("toolbar.items");
		
		$btn=$tollbarItems->add_data_obj();
		
		$btn->set_prop("locateInMenu","auto");
		$btn->set_prop("widget","dxButton");
		$btn->set_prop("options.text",$this->lng_get_msg_txt("rememberFilters","Recordar filtros"));
		$btn->set_prop("options.icon","meralda-icon meralda-icon-filter-save");
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_cont("{$var}.saveFilters();");
		$btn->set_prop("options.onClick",$fnc);

	}
	function addBtnColsPrefReset($datagrid,$gridhelper){
		$var=$this->get_js_ui_man_name();
		$tollbarItems=$datagrid->js_props->get_array_prop("toolbar.items");
		
		$btn=$tollbarItems->add_data_obj();
		$btn->set_prop("locateInMenu","auto");
		$btn->set_prop("widget","dxButton");
		$btn->set_prop("options.text",$this->lng_get_msg_txt("resetColumns","Restablecer columnas"));
		$btn->set_prop("options.icon","undo");
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_cont("{$var}.resetUserPrefCols();");
		$btn->set_prop("options.onClick",$fnc);

	}
	 /**
     * @param mwmod_mw_devextreme_widget_datagrid $datagrid
     */
	function add_cols($datagrid){
		$col=$datagrid->add_column_number("id","ID");
		$col->js_data->set_prop("width",60);
		$col->js_data->set_prop("allowEditing",false);
		$col->js_data->set_prop("visible",false);
		$col=$datagrid->add_column_string("name",$this->lng_common_get_msg_txt("name","Nombre"));
		
		
			
	}
	function is_responsable_for_sub_interface_mnu(){
		return false;	
	}
	

	function do_exec_no_sub_interface(){
		

	}
	

}
?>