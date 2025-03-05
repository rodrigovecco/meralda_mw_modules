<?php
class mwmod_mw_html_manager_uipreparers_default extends mwmod_mw_html_manager_uipreparers_abs{
	function __construct($ui=false){
		$this->set_ui($ui);
	}
	function preapare_ui($ui=false){
		if(!$ui){
			$ui=$this->ui;	
		}
		if(!$ui){
			return false;	
		}
		$helper=new mwmod_mw_html_manager_uipreparers_htmlfrm($ui);
		$helper->preapare_ui();
		
		
		$this->add_js($ui);	
		$this->add_css($ui);	
	}
	
	/*
	function add_js($ui=false){
		if(!$jsman=$this->get_js_man($ui)){
			return false;	
		}
		$jsman->add_item_by_cod_def_path("inputsman.js");
		$jsman->add_item_by_cod_def_path("validator.js");

	}
	function add_css($ui=false){
	}
	*/
}
?>