<?php
class mwmod_mw_mnu_items_cus extends mwmod_mw_mnu_mnuitem{
	var $html_elem;
	function __construct($cod,$etq,$parent,$url=false){
		$this->init($cod,$etq,$parent,$url);
		$this->create_html_elem();
	}
	function get_html_elem(){
		if($this->html_elem){
			return $this->html_elem;
		}
		$this->create_html_elem();
		return $this->html_elem;
	}
	function create_html_elem(){
		$this->html_elem=new mwmod_mw_html_elem("div");
		$this->html_elem->set_cont($this->get_alink());	
	}
	function get_html_as_list_inner(){
		return $this->get_html();	
			
	}
	
	function get_html_as_nav_child(){
		return $this->get_html();	
	}
	function get_html(){
		if($this->html_elem){
			return $this->html_elem->get_as_html();	
		}
		return "";
			
	}
	
}

?>