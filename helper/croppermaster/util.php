<?php

class mwmod_mw_helper_croppermaster_util extends mwmod_mw_html_manager_util{
	function __construct(){
		
	}
	function preapare_ui($ui){
		$this->add_js($ui);
		$this->add_css($ui);
		
	}
	function preapare_ui_avatar($ui){
		if(!$jsman=$this->get_js_man($ui)){
			return false;	
		}
		$item=new mwmod_mw_html_manager_item_jsexternal("cropperavatar","/res/cropper/avatar.js");
		$jsman->add_item_by_item($item);
		
	}
	
	function add_css($manorui){
		if(!$cssmanager=$this->get_css_man($manorui)){
			return false;	
		}
		if(!$cssmanager->item_exists("cropper")){
			$item= new mwmod_mw_html_manager_item_css("cropper","/res/cropper/cropper.min.css");
			$cssmanager->add_item_by_item($item);
		}
	}
	
	function add_js($manorui){
		if(!$jsman=$this->get_js_man($manorui)){
			return false;	
		}
		if($jsman->item_exists("cropper")){
			return true;
		}
		$item=new mwmod_mw_html_manager_item_jsexternal("cropper","/res/cropper/cropper.min.js");
		$jsman->add_item_by_item($item);
	}
	
}
?>