<?php
class mwmod_mw_mnu_items_dropdown_side extends mwmod_mw_mnu_mnuitem{
	var $side_css_sub_class="nav-second-level";
	function __construct($cod,$etq,$parent,$url=false){
		$this->init($cod,$etq,$parent,$url);
	}
	
	function get_li_class_name(){
		if($this->is_active()){
			return "active";	
		}
		return "";
	}
	function get_html_children_nav(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		$container=new mwmod_mw_html_elem("div");
		$container->set_att("id",$this->getElemID("ch"));
		$container->addClass("collapse");
		
		$subcont=$container->add_cont_elem();
		$subcont->addClass("bg-white py-2 collapse-inner rounded");
		foreach ($items as $item){
			$subcont->add_cont($item->get_html_as_collapse_item());	
		}
		return $container->get_as_html();
	
	}
	
	function get_navlist_link_elem(){
		$c=new mwmod_mw_html_elem("a");
		if($str=$this->get_url()){
			$c->set_att("href",$str);
		}else{
			$c->set_att("href","#");
		}
		$c->addClass("nav-link");
		if($str=$this->get_param("aid")){
			$c->set_att("id",$str);
		}elseif($this->is_dropdown()){
			$c->set_att("id",$this->getElemID("ddctr"));
		}
		$c->addClass("collapsed");
		$c->set_att("data-toggle","collapse");
		$c->set_att("data-target","#".$this->getElemID("ch")."");
		$c->set_att("aria-controls",$this->getElemID("ch"));
		$c->set_att("aria-expanded","false");
		
		/*
		if($this->is_dropdown()){
			//$c->addClass("dropdown");
			$c->addClass("dropdown-toggle");
			$c->set_att("data-toggle","dropdown");
			$c->set_att("aria-expanded","false");
		}
		*/
		
		if($str=$this->get_target()){
			$c->set_att("target",$str);
		}
		$e=$c->add_cont_elem("","span");
		$e->add_cont($this->get_a_inner_html());
		return $c;

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
	
	
	function get_alink(){
		$r.="<a href='#' class='nav-link collapsed' data-toggle='collapse' ";
		/*
		if($this->tooltip){
			$r.="data-toggle='tooltip'  data-placement='top'  title='".mw_text_nl_js($this->tooltip)."' ";	
		}
		*/
		$r.=">";
		$r.=$this->get_a_inner_html();
		//$r.=" <span class='fa arrow'></span>";
		$r.="</a>";
		return 	$r;
	}
	function get_a_inner_html(){
		$r="";
		$r=$this->innerHTMLbefore;
		if($c=$this->get_param("icon_class")){
			$r.="<i class='$c'></i>";		
		}else{
			$r.=$this->get_etq();
		}
		$r.=$this->innerHTMLafter;
		return $r;	
	}
	
	function get_html_as_nav_child_inner(){
		$r.=$this->get_alink();
		$r.="<ul class='nav ".$this->side_css_sub_class." collapse' aria-expanded='false'>";	
		$r.=$this->get_html_as_nav_child_inner_children();
		$r.="</ul>";
		return $r;
			
	}
	function get_html_children(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		$r.="<ul class='nav ".$this->side_css_sub_class." collapse' aria-expanded='false'>x";	
		foreach ($items as $item){
			//$r.="<li>";	
			$r.=$item->get_html_as_list_item();	
			//$r.="</li>";	
		}
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
	
}

?>