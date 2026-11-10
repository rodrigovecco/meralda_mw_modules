<?php
abstract class mwmod_mw_html_manager_uipreparers_abs extends mwmod_mw_html_manager_util{
	
	function addCKEditor($ui=false){
		$item=new mwmod_mw_html_manager_item_jsexternal("ckeditor","/res/ckeditor/ckeditor.js");
		return $this->add_js_item($item,$ui);
	
	}
	/**
	 * Loads TinyMCE 6 (self hosted, MIT) plus the popup file picker helper that
	 * the TinyMCE input uses to browse files in a Meralda UI.
	 *
	 * TinyMCE builds its own asset URLs (skins, plugins, langs) from the script
	 * src, so the canonical path must be used: no rewrite/redirect in between.
	 *
	 * @param mwmod_mw_ui_sub_uiabs|false $ui
	 * @return mwmod_mw_html_manager_item_jsexternal|false
	 */
	function addTinyMCE($ui=false){
		$item=new mwmod_mw_html_manager_item_jsexternal("tinymce","/res/thirdparty/tinymce/tinymce.min.js");
		$this->add_js_item($item,$ui);
		$this->addMwFilePicker($ui);
		return $item;
	}
	/**
	 * Popup helper used by editors to ask for a file to a Meralda UI
	 * (typically the DevExtreme file manager subinterface).
	 *
	 * @param mwmod_mw_ui_sub_uiabs|false $ui
	 * @return mixed
	 */
	function addMwFilePicker($ui=false){
		if(!$jsman=$this->get_js_man($ui)){
			return false;
		}
		return $jsman->add_item_by_cod_def_path("mw_file_picker.js");
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