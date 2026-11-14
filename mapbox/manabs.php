<?php
abstract class  mwmod_mw_mapbox_manabs extends mwmod_mw_manager_baseman{
	function initMan($code,$ap){
		$this->init($code,$ap);
		$this->enable_treedata();
	}
	function prepareMainUI($ui){
		
		
		//$ui->jsmanager->add_item_by_cod_def_path("mapbox/mw_mapbox.js");
		//$ui->ui_js_init_params->set_prop("managers.mapbox",$this->getJsManObj());
	}
	/*
	function getJsManObj($js=false){
		if(!$js){
			$js=new mwmod_mw_jsobj_newobject("mw_mapbox_man");	
		}
		$js->set_prop("clientID",$this->getAppID());
		$js->set_prop("src",$this->get_js_src());
		$js->set_prop("enabled",$this->isEnabled());
		return $js;
			
	}
	*/
	function get_js_src(){
		return "https://api.mapbox.com/mapbox-gl-js/v2.4.1/mapbox-gl.js";	
	}

	
	function getJSInitItem(){
		return new mwmod_mw_mapbox_jsinit($this);	
	}
	function isEnabled(){
		if(!$td=$this->get_treedata_item("cfg")){
			return false;
		}
		if(!$td->get_data("enabled")){
			return false;	
		}
		if($this->getAppID()){
			return true;	
		}
		return false;
			
	}
	function getAppID(){
		if($td=$this->get_treedata_item("keys")){
			return $td->get_data("appId")."";	
		}
	}
	function createCfgUI($cod,$parent){
		$ui=new mwmod_mw_mapbox_ui_cfg($cod,$parent,$this);
		return $ui;
	}
}
?>