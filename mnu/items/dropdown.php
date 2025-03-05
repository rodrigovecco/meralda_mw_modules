<?php
//no probado
class mwmod_mw_mnu_items_dropdown extends mwmod_mw_mnu_mnuitem{
	function __construct($cod,$etq,$parent,$url=false){
		$this->init($cod,$etq,$parent,$url);
		$this->set_param("class","dropdown-menu");
	}
	function is_dropdown(){
		return true;	
	}
	function get_html_open(){
		return "<ul class='".$this->get_param("class")."'>";	
		
	}
	function get_html_close(){
		return "</ul>";	
	}
	function get_html(){
		$r.=$this->get_html_open();
		//$r.=$this->get_alink();
		$r.=$this->get_html_children();
		$r.=$this->get_html_close();
		return $r;
			
	}
	function get_html_children(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		//$r="<ul>";
		foreach ($items as $item){
			$r.="<li>";	
			$r.=$item->get_html_as_dropdown_child();	
			$r.="</li>";	
		}
		//$r.="</ul>";
		return $r;
	}
}

?>