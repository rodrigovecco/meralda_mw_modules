<?php
//rvh 2015-01-10 v 1

class mwmod_mw_html_manager_item_jsexternal extends mwmod_mw_html_manager_item_js{
	
	function __construct($cod,$src){
		$this->init_item($cod);
		$this->extenal_src=$src;
	}
	function get_src(){
		return $this->extenal_src;	
	}

	
}
?>