<?php
abstract class mwmod_mw_ui_base_dxtbladminquery extends mwmod_mw_ui_base_dxtbladmin{
	public $queryHelper;//mwmod_mw_devextreme_data_queryhelper iniciado en getQuery
	public $defPageSize=20;
	public $editingMode="row";
	public $excelExportName;
	public $reg_key="id";
	public $dateColsCods;
	
	
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
	function updateRegById($id,$data){
		//extender
		return false;
		
		
	}
	function getRegDataById($id){
		if(!$query=$this->getQuery()){
			return false;
		}
		$query->where->add_where_crit("id",$id);

		return $query->get_one_row_result();
		
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
		/*
		if(!$item=$this->getOwnItem($_REQUEST["itemid"]??null)){
			$xml->root_do_all_output();
			return false;	
				
		}
			*/
		if(!$itemID=$_REQUEST["itemid"]??null){
			$xml->root_do_all_output();
			return false;	
				
		}
		if(!(is_string($itemID)or(is_numeric($itemID)))){
			
			$xml->set_prop("error","itemid not valid");
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
		


		if(!$this->updateRegById($itemID,$nd)){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$regData=$this->getRegDataById($itemID)){
			$xml->root_do_all_output();
			return false;	
		}
		
		//$item->do_save_data($nd);
		
		$xml->set_prop("ok",true);
		$xml->set_prop("itemid",$itemID);
		$xml->set_prop("itemdata",$this->get_reg_data($regData));
		
		
		
		$xml->root_do_all_output();
		
		//$item->tem

	}
	function get_reg_data($data){
		if($this->dateColsCods){
			$cods=explode(",",$this->dateColsCods);
			foreach($cods as $cod){
				if(isset($data[$cod])){
					$data[$cod]=$this->fixDatetimeFromDB($data[$cod]);

				}
			}
		}

		//$r=$item->getDataForDXtbl();
		//todo dates
		return $data;
		
	}
	function fixDatetimeFromDB($value,$format="Y/m/d H:i:s"){
		if(!$value){
			return null;
		}
		if(!is_string($value)){
			return null;
		}
		if($value=="0000-00-00"){
			return null;
		}
		if($value=="0000-00-00 00:00:00"){
			return null;	
		}
		if(!$time=strtotime($value)){
			return null;
		}
		return date($format,$time);
	}
	function allowInsert(){
		return false;
	}
	
	function allowDelete(){
		return false;
	}
	function allowUpdate(){
		return false;
	}


	function execfrommain_getcmd_sxml_loaddata($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		//$xml->set_prop("htmlcont",$this->lng_get_msg_txt("not_allowed","No permitido"));
		if(!$this->is_allowed()){
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
		$dataqueryhelper->setLoadOptions($_REQUEST["lopts"]);
		//$xml->set_prop("debug.dataqueryhelper",$dataqueryhelper->getDebugData());
		$dataqueryhelper->aplay2Query($query);
		if(!$dataqueryhelper->sorted){
			$this->setDefaultQuerySort($query);
		}
		if($this->debugOutputEnabled()){
			$xml->set_prop("debug.sql",$query->get_sql());
			$xml->set_prop("debug.sqlcount",$query->get_count_sql());
			$xml->set_prop("debug.isParameterizedMode",$query->isParameterizedMode());
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
						$js->set_prop("summaryMap.{$k}",$v);
					}
				}

			}
		}

		
		$js->set_prop("totalCount",$totalCount);
		
		
		
		$dataoptim=new mwmod_mw_jsobj_dataoptim();
		$dataoptim->set_key($this->reg_key);
		$js->set_prop("dsoptim",$dataoptim);
		if($alldata=$query->get_array_data_from_sql_by_index()){
			foreach($alldata as $d){
				
				$data=$this->get_reg_data($d);
				$dataoptim->add_data($data);	
			}
			
		}
		if($this->debugOutputEnabled()){
			if($query->isParameterizedMode()){
				$xml->set_prop("debug.parammode",true);
				if($query->currentParameterizedQuery){
					$xml->set_prop("debug.paramquery",$query->currentParameterizedQuery->getDebugData());
				}
			
				
			}
		}
		$xml_js=new mwmod_mw_data_xml_js("js",$js);
		
		
		$xml->add_sub_item($xml_js);
		$xml->root_do_all_output();
		
		
		//
		
			
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
		if($this->excelExportName){
			$this->ui_js_init_params->set_prop("excelExportName",$this->excelExportName);
		}
		
		$jsui=$this->new_ui_js();
		$var=$this->get_js_ui_man_name();
		//$js->add_cont("var {$var}=".$jsui->get_as_js_val().";\n");
		
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		
		echo $js->get_js_script_html();
		return;
		
		

		
	}
	
	
	
	

	function execfrommain_getcmd_sxml_loaddatagrid($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		$xml->set_prop("htmlcont",$this->lng_get_msg_txt("not_allowed","No permitido"));
		if(!$this->is_allowed()){
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
		$gridhelper->set_fnc_name("mw_devextreme_datagrid_man_rdataedit");
		if($this->addColCodsToExcel){
			$gridhelper->set_prop("addColCodsToExcel",true);	
		}

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
			
		}
		
		
		$this->afterDatagridCreated($datagrid,$gridhelper);
		
		$js=new mwmod_mw_jsobj_obj();
		$js->set_prop("datagridman",$gridhelper);
		$xml_js=new mwmod_mw_data_xml_js("jsresponse",$js);
		
		
		$xml->add_sub_item($xml_js);
		$xml->root_do_all_output();
		
		
		
		
			
	}
	

}
?>