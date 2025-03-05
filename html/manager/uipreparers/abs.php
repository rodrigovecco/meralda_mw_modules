<?php
abstract class mwmod_mw_html_manager_uipreparers_abs extends mwmod_mw_html_manager_util{
	
	function addCKEditor($ui=false){
		$item=new mwmod_mw_html_manager_item_jsexternal("ckeditor","/res/ckeditor/ckeditor.js");
		return $this->add_js_item($item,$ui);
	
	}
	function add_js($ui=false){
		/*
		if(!$jsman=$this->get_js_man($manorui)){
			return false;	
		}
		if($jsman->item_exists("dxwebappjs")){
			return true;
		}
		$jsman->add_jquery();
		$jsman->add_globalize();
		$item=new mwmod_mw_html_manager_item_jsexternal("dxwebappjs","/res/devextreme/js/dx.webappjs.js");
		$jsman->add_item_by_item($item);
		*/
	}
	function add_css($ui=false){
		/*
		example
		if($cssmanager=$this->get_css_man($ui)){
			return false;	
		}
		if(!$cssmanager->item_exists("dxcommon")){
			$item= new mwmod_mw_html_manager_item_css("dxcommon","/res/devextreme/css/dx.common.css");
			$cssmanager->add_item_by_item($item);
		}
		*/
	}
	function preapare_ui($ui=false){
		
		$this->add_js($ui);	
		$this->add_css($ui);	
	}
	
	
	function add_js_item($item,$ui=false){
		if(!$man=$this->get_js_man($ui)){
			return false;	
		}
		return $man->add_item_by_item($item);
		
	}
	function add_css_item($item,$ui=false){
		if(!$cssmanager=$this->get_css_man($ui)){
			return false;	
		}
		return $cssmanager->add_item_by_item($item);
		
	}

}
?>