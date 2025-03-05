<?php
//rvh 2015-01-10 v 1
class mwmod_mw_html_manager_css extends mwmod_mw_html_manager_abs{
	function __construct(){
		$this->init_man();
		$this->set_def_path("/res/css/");
	}

	
	function create_item($cod){
		if(!$cod=$this->check_str_key($cod)){
			return false;	
		}
		$item= new mwmod_mw_html_manager_item_css($cod);

		return $item;	
	}

	
}
?>