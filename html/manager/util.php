<?php
abstract class mwmod_mw_html_manager_util {
	public $ui;
	
	
	function add_js_item_by_cod_def_path($jsrelpath,$manorui=false){
		if($jsman=$this->get_js_man($manorui)){
			return 	$jsman->add_item_by_cod_def_path($jsrelpath);
		}
	}
	function add_jquery_ui($manorui=false){
		$this->add_jquery($manorui);
		if($jsman=$this->get_js_man($manorui)){
			if(!$jsman->item_exists("jqueryui")){
				$item=new mwmod_mw_html_manager_item_jsexternal("jqueryui","/res/jquery/ui/jquery-ui.min.js");
				$jsman->add_item_by_item($item);
			}
			
		}
		if($cssman=$this->get_css_man($manorui)){
			if(!$cssman->item_exists("jqueryui")){
				$item=new mwmod_mw_html_manager_item_css("jqueryui","/res/jquery/ui/jquery-ui.min.css");
				$cssman->add_item_by_item($item);
			}
			
		}
		
	}
	function add_jquery($manorui=false){
		if($jsman=$this->get_js_man($manorui)){
			 $jsman->add_jquery();
			 return	true;
		}
	}
	
	function set_ui($ui){
		if(!$ui){
			return false;	
		}
		if(is_a($ui,"mwmod_mw_ui_sub_uiabs")){
			$this->ui=$ui;
			return true;
		}
		
	}
	function get_css_man($manorui=false){
		if(!$manorui){
			if(!$this->ui){
				return false;	
			}else{
				$manorui=$this->ui;	
			}
		}
		if(is_a($manorui,"mwmod_mw_ui_sub_uiabs")){
			$manorui=$manorui->maininterface;	
			if(!$manorui){
				return false;	
			}
		}
		if(is_a($manorui,"mwmod_mw_ui_main_uimainabs")){
			$manorui=$manorui->cssmanager;	
			if(!$manorui){
				return false;	
			}
		}
		if(is_a($manorui,"mwmod_mw_html_manager_css")){
			return $manorui;	
		}
	}
	function get_js_man($manorui=false){
		if(!$manorui){
			if(!$this->ui){
				return false;	
			}else{
				$manorui=$this->ui;	
			}
		}
		if(is_a($manorui,"mwmod_mw_ui_sub_uiabs")){
			$manorui=$manorui->maininterface;	
			if(!$manorui){
				return false;	
			}
		}
		if(is_a($manorui,"mwmod_mw_ui_main_uimainabs")){
			$manorui=$manorui->jsmanager;	
			if(!$manorui){
				return false;	
			}
		}
		if(is_a($manorui,"mwmod_mw_html_manager_js")){
			return $manorui;	
		}
	}

}
?>