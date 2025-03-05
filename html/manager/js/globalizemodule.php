<?php
//rvh 2015-01-10 v 1
class mwmod_mw_html_manager_js_globalizemodule extends mwmod_mw_html_manager_item_js{
	var $globalize_module_cod;
	function __construct($globalize_module_cod){
		$this->globalize_module_cod=$globalize_module_cod;
		$cod="globalize_".$globalize_module_cod;
		$this->init_item($cod);
		
	}
	function get_src(){
		return "/res/globalize/modules/".$this->globalize_module_cod.".js";
	}

	
}
?>