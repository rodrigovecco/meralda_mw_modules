<?php
//rvh 2015-01-10 v 1
class mwmod_mw_html_manager_item_js extends mwmod_mw_html_manager_item_abs{
	function __construct($cod){
		$this->init_item($cod);
	}
	function get_html_declaration(){
		return "<script type='text/javascript'  language='javascript' src='".$this->get_src()."'></script>\n";	
	}

	
}
?>