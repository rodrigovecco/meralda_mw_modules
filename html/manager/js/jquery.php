<?php
//rvh 2023-10-21 v 2
class mwmod_mw_html_manager_js_jquery extends mwmod_mw_html_manager_item_js{
	
	function __construct($cod="jquery"){
		$this->init_item($cod);
		
	}
	function get_src(){
		if($this->extenal_src){
			return $this->extenal_src;
		}
		return "/res/jquery/jquery.min.js";
	}
	
}
?>