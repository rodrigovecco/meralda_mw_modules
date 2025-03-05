<?php

abstract class mwmod_mw_ui_base_dxtbladminforsubitems extends mwmod_mw_ui_base_dxtbladmin{
	public $subItemRelFieldName;
	public $mainItemReqParam="mainitem";
	public $mainItem;
	public $mainItemManCode;

	private $mainItemMan;
	/*
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("detail","Detalle"));
		$this->subItemRelFieldName="pricelist_id";
		$this->mainItemManCode="products_pricelists";
		$this->set_items_man_cod("products_pricelists_det");
		$this->js_ui_class_name="mw_ui_grid_remote";
		$this->editingMode="cell";
		
	}
	*/
	function loadMainItemMan(){
		if($this->mainItemManCode){
			return $this->mainap->get_submanager($this->mainItemManCode);
		}
	}
	final function __get_priv_mainItemMan(){
		if(!isset($this->mainItemMan)){
			$this->mainItemMan=$this->loadMainItemMan();
		}
		return $this->mainItemMan;
	}
	function setMainItem($item){
		if(!$item){
			return false;
		}
		$this->finalSetMainItem($item);
		//$this->mainItem=$item;
		$this->set_url_param($this->mainItemReqParam,$item->get_id());
	}
	final function finalSetMainItem($item){
		$this->mainItem=$item;
	}
	function do_exec_page_in(){
		if(!$this->mainItem){
			
			return false;
		}
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
	
	function loadMainItemFromReq(){
		if($item=$this->loadMainItemFromID($_REQUEST[$this->mainItemReqParam]??null)){
			return $this->setMainItem($item);
		}
	}
	function loadMainItemFromID($id){
		if(!$man=$this->__get_priv_mainItemMan()){
			return false;
		}
		if($id){
			if(!is_array($id)){
				return $man->get_item($id);

			}
		}
	}
	function checkSubItem($item){
		if(!$item){
			return false;
		}
		if(!$this->subItemRelFieldName){
			return false;	
		}
		if(!$this->mainItem){
			return false;	
		}
		if($this->mainItem->get_id()==$item->get_data($this->subItemRelFieldName)){
			return true;	
		}
	}
	function loadMainItemCMDMode($params){
		if(!is_array($params)){
			return false;
		}
		
		if(!$item=$this->loadMainItemFromID($params[$this->mainItemReqParam]??null)){
			return false;	
		}
		$this->setMainItem($item);
		return $item;

	}
	function get_exec_cmd_sxml_url($xmlcmd="debug",$params=array()){
		if($this->maininterface){
			if($this->mainItem){
				if(!is_array($params)){
					$params=array();
				}
				$params[$this->mainItemReqParam]=$this->mainItem->get_id();
			}
			
			//$this->maininterface must by mwmod_mw_ui_main_uimainabsajax
			return $this->maininterface->get_exec_cmd_sxml_url($xmlcmd,$this,$params);	
		}
		
		
	}

	function execfrommain_getcmd_sxml($cmdcod,$params=array(),$filename=false){
		$this->loadMainItemCMDMode($params);
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			$xml=$this->new_getcmd_sxml_answer(false,"Invalid command");
			$xml->root_do_all_output();
			return false;	
		}
		$method="execfrommain_getcmd_sxml_$cmdcod";
		if(!method_exists($this,$method)){
			$xml=$this->new_getcmd_sxml_answer(false,"Method $method does not exist on ".get_class($this));
			$xml->root_do_all_output();
			return false;
				
		}
		return $this->$method($params,$filename);
	}
	function execfrommain_getcmd_dl($cmdcod,$params=array(),$filename=false){
		$this->loadMainItemCMDMode($params);
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			$errmsg="invalid command";
			return $this->execfrommain_getcmd_dl_error($params,$filename,$errmsg);	
		}
		$method="execfrommain_getcmd_dl_$cmdcod";
		if(!method_exists($this,$method)){
			$errmsg=get_class($this)." has no method $method";
			return $this->execfrommain_getcmd_dl_error($params,$filename,$errmsg);	
				
		}
		return $this->$method($params,$filename);
	}
	
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
		if(!$this->mainItem){
			return false;	
		}
		if(!$this->subItemRelFieldName){
			return false;	
		}
		$query->where->add_where_crit($tblman->tbl.".".$this->subItemRelFieldName,$this->mainItem->get_id());


		$this->afterGetQuery($query);
		return $query;
	}
	
	
	
	
	
	function create_new_item($nd){
		if(!$this->mainItem){
			return false;
		}
		if(!is_array($nd)){
			return false;
		}
		if(!$this->subItemRelFieldName){
			return false;
		}
		$nd[$this->subItemRelFieldName]=$this->mainItem->get_id();
		if($man=$this->items_man){
			
			return $man->create_new_item($nd);	
		}
	}
	
	function saveItem($item,$nd){
		unset($nd["id"]);
		if(!$this->subItemRelFieldName){
			return false;
		}
		unset($nd[$this->subItemRelFieldName]);
		if(!$this->checkSubItem($item)){
			return false;
		}

		if(isset($nd["name"])and(!$nd["name"])){
			unset($nd["name"]);	
		}
		$item->do_save_data($nd);
		
	}
	function delete_item($item,$xmlresponse){

		if(!$this->checkSubItem($item)){
			return false;
		}
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

	

	function do_exec_no_sub_interface(){
		$this->loadMainItemFromReq();
		

	}
	

}
?>