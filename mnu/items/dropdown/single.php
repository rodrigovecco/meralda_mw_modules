<?php
class mwmod_mw_mnu_items_dropdown_single extends mwmod_mw_mnu_mnuitem{
	function __construct($cod,$etq,$parent,$url=false){
		$this->init($cod,$etq,$parent,$url);
	}
	function is_dropdown(){
		return true;	
	}
	/*
	function get_li_class_name(){
		if($this->is_active()){
			return "dropdown active";	
		}
		return "dropdown";
	}
	
	function is_active(){
		if($this->active){
			return true;	
		}
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		
		foreach ($items as $item){
			
			if($item->is_active()){
				return true;	
			}
				
		}
		
	}
	/*
	/*
	function get_alink(){
		//obsolete
	
		$r.="<a href='#' ";
		$r.=" class='dropdown-toggle' data-toggle='dropdown' role='button' aria-expanded='false' ";
		$r.=">";
		$r.=$this->get_a_inner_html();
		$r.="<span class='caret'></span>";
		$r.="</a>";
				

		return 	$r;
	}
	*/
	/*
	function get_html_as_nav_child_inner(){
		$r.=$this->get_alink();
		$r.="<ul class='dropdown-menu' role='menu'>";	
		$r.=$this->get_html_as_nav_child_inner_children();
		$r.="</ul>";
		return $r;
			
	}

	function get_html_as_nav_child_inner_children(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		
		foreach ($items as $item){
			
			$r.=$item->get_html_as_nav_child();	
				
		}
		
		return $r;
	}
	*/
}

?>