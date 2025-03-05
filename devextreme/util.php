<?php
class mwmod_mw_devextreme_util extends mwmod_mw_html_manager_util{
	function __construct(){
	}
	function preapare_ui_charts($ui){
		$this->preapare_ui_webappjs($ui);
		$this->add_js_charts($ui);
		
	}
	function preapare_ui_exportExcel($manorui){
		if(!$jsman=$this->get_js_man($manorui)){
			return false;	
		}
		if($jsman->item_exists("dxjsexcel")){
			return true;
		}
		$item=new mwmod_mw_html_manager_item_jsexternal("dxjsexcel","/res/dx/js/exceljs.min.js");
		$jsman->add_item_by_item($item);
		$item=new mwmod_mw_html_manager_item_jsexternal("dxfilesaver","/res/dx/js/FileSaver.min.js");
		$jsman->add_item_by_item($item);
		
		$jsman->remove_item("dxwebappjs");
		$jsman->remove_item("dxinit");
		$jsman->remove_item("dxwebappjs_local");
		

	}


	function preapare_ui_zip($ui){
		$this->add_js_zip($ui);
		
	}
	function add_js_zip($manorui){
		//no probado en nueva versión 20201224
		if(!$jsman=$this->get_js_man($manorui)){
			return false;	
		}
		if($jsman->item_exists("jszip")){
			return true;
		}
		$jsman->remove_item("dxwebappjs");
		$jsman->remove_item("dxinit");
		$jsman->remove_item("dxwebappjs_local");
		
		
		
		//$jsman->add_jquery();
		//$jsman->add_globalize();
		$item=new mwmod_mw_html_manager_item_jsexternal("jszip","/res/dx/js/jszip.min.js");
		$jsman->add_item_by_item($item);
	}
	
	function preapare_ui_webappjs($ui){
		$this->add_js_webappjs($ui);
		$this->add_css_webappjs($ui);
		
	}
	function add_js_charts($manorui){
		return false;
		if(!$jsman=$this->get_js_man($manorui)){
			return false;	
		}
		if($jsman->item_exists("dxchartsjs")){
			return true;
		}
		//$jsman->add_jquery();
		//$jsman->add_globalize();
		$item=new mwmod_mw_html_manager_item_jsexternal("dxchartsjs","/res/dx/js/dx.chartjs.js");
		//$item=new mwmod_mw_html_manager_item_jsexternal("dxwebappjs","/res/dx/js/dx.webappjs.debug.js");
		$jsman->add_item_by_item($item);
	}
	function add_css_mw_compact($manorui){
		if(!$cssmanager=$this->get_css_man($manorui)){
			return false;	
		}
		if(!$cssmanager->item_exists("dxmwcompact")){
			$item= new mwmod_mw_html_manager_item_css("dxmwcompact","/res/dx/css/mw.compact.css");
			$cssmanager->add_item_by_item($item);
		}
	}
	
	function add_css_webappjs($manorui){
		if(!$cssmanager=$this->get_css_man($manorui)){
			return false;	
		}
		if(!$cssmanager->item_exists("dxcommon")){
			$item= new mwmod_mw_html_manager_item_css("dxcommon","/res/dx/css/dx.common.css");
			$cssmanager->add_item_by_item($item);
		}
		if(!$cssmanager->item_exists("dxlight")){
			$item= new mwmod_mw_html_manager_item_css("dxlight","/res/dx/css/dx.light.css");
			$item->set_att("rel","dx-theme");	
			$item->set_att("data-theme","generic.light");	
			$item->set_att("data-active","true");	
			$cssmanager->add_item_by_item($item);
		}
	}
	function add_dx_theme_android($manorui){
		return $this->add_dx_theme("dxandroid","android5.light","dx.android5.light",$manorui);
	}
	function add_dx_theme_ios($manorui){
		return $this->add_dx_theme("dxios","ios7.default","dx.ios7.default",$manorui);
	}
	function add_dx_theme($cod,$name,$cssfile,$manorui){
		if(!$cssmanager=$this->get_css_man($manorui)){
			return false;	
		}
		if(!$cssmanager->item_exists($cod)){
			$item= new mwmod_mw_html_manager_item_css($cod,"/res/dx/css/".$cssfile.".css");
			$item->set_att("rel","dx-theme");	
			$item->set_att("data-theme",$name);	
			$item->set_att("data-active","true");	
			$cssmanager->add_item_by_item($item);
			return $item;
		}
		return $cssmanager->get_item($cod);
		
	}
	
	function add_js_webappjs($manorui){
		if(!$jsman=$this->get_js_man($manorui)){
			return false;	
		}
		if($jsman->item_exists("dxwebappjs")){
			return true;
		}
		$jsman->add_jquery();
		$jsman->add_globalize();
		$item=new mwmod_mw_html_manager_item_jsexternal("dxwebappjs","/res/dx/js/dx.all.js");
		//$item=new mwmod_mw_html_manager_item_jsexternal("dxwebappjs","/res/devextreme/js/dx.webappjs.debug.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_dialog_helper.js");
		//$item=new mwmod_mw_html_manager_item_jsexternal("dxwebappjs","/res/devextreme/js/dx.webappjs.debug.js");
		$jsman->add_item_by_item($item);
		if($lng=$jsman->mainap->get_current_lng_man()){
			if($r=$lng->get_ini_cfg_value("dxwebappjs_locale_src")){
				$item=new mwmod_mw_html_manager_item_jsexternal("dxwebappjs_local","/res/dx/js/localization/$r");
				$jsman->add_item_by_item($item);
					
			}
			if($r=$lng->get_ini_cfg_value("dxwebappjs_locale_code")){
				$item=new mwmod_mw_html_manager_item_jscus("dxinit");
				$item->js_container->add_cont("DevExpress.localization.locale('$r');\n");
				$jsman->add_item_by_item($item);	
			}
		}
	}
	
}
?>