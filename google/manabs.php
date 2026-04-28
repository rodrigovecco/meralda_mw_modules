<?php
abstract class  mwmod_mw_google_manabs extends mwmod_mw_manager_baseman{
	
	function initMan($code,$ap){
		$this->init($code,$ap);
		$this->enable_treedata();
		$this->enable_jsondata();
		
	}

	/**
	 * Returns the data item used by the manager to store its config blocks
	 * (cfg, keys, maps). Override in subclasses to change the storage format.
	 * Default: JSON files (`*.json`) under `man/google/data/`.
	 * Pastipan override returns treedata (`*.mwtreedata`) for legacy data.
	 *
	 * @param string $code Sub-item code (e.g. "cfg", "keys", "maps").
	 * @return mwmod_mw_data_tree_item|false
	 */
	function getCfgDataItem($code){
		return $this->getJsonDataItem($code);
	}

	function getMapID(){
		if($td=$this->getCfgDataItem("maps")){
			if($v=$td->get_data("mapid")){
				return $v;	
			};	
		}
		return	"mwmapid";
	}

	function prepareMainUIForMaps($ui,$libraries=false){
		
    	$ui->jsmanager->add_item_by_cod_def_path("google/mw_google_maps.js");
    	$cod="googlemaps";
    	if(!$item=$ui->jsmanager->get_item($cod)){
			$src="https://maps.google.com/maps/api/js?callback=mw_google_maps_loaded&loading=async";
			if($lngman=$this->mainap->get_current_lng_man()){
   				if($lng=$lngman->get_locale_cod()){
   					$src.="&language=$lng";
   				}
   			}
			if($td=$this->getCfgDataItem("maps")){
				if($v=$td->get_data("apikey")){
					$src.="&key=".$v;
				}
			}
			$item= new mwmod_mw_google_jsinitmaps($cod,$src);
			$ui->jsmanager->add_item_by_item($item);
		}
		if($libraries){
			if(method_exists($item,"addLibraries")){
				$item->addLibraries($libraries);
			}
		}


		
		
		
	}

	function prepareMainUI($ui){
		$ui->jsmanager->add_item_by_cod_def_path("google/mw_google.js");
		$ui->ui_js_init_params->set_prop("managers.google",$this->getJsManObj());
	}
	function getJsManObj($js=false){
		if(!$js){
			$js=new mwmod_mw_jsobj_newobject("mw_google_man");	
		}
		$js->set_prop("clientID",$this->getAppID());
		$js->set_prop("src",$this->get_js_src());
		$js->set_prop("enabled",$this->isEnabled());
		return $js;
			
	}
	function get_js_src(){
		return "https://accounts.google.com/gsi/client";
	}

	
	function getJSInitItem(){
		return new mwmod_mw_google_jsinit($this);	
	}
	function isEnabled(){
		if(!$td=$this->getCfgDataItem("cfg")){
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
		if($td=$this->getCfgDataItem("keys")){
			return $td->get_data("appId")."";	
		}
	}
	
	function createCfgUI($cod,$parent){
		$ui=new mwmod_mw_google_ui_cfg($cod,$parent,$this);
		return $ui;
	}
}
?>