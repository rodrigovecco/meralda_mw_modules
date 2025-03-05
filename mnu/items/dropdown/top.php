<?php
class mwmod_mw_mnu_items_dropdown_top extends mwmod_mw_mnu_mnuitem{
	function __construct($cod,$etq,$parent,$url=false){
		$this->init($cod,$etq,$parent,$url);
		$this->set_dropdown();
	}

	function addToHtmlTopNavULSbadmin($navUL,$level=0){
		//20231022 extender si hay necesidad
		$li=new mwmod_mw_html_elem("li");
		$navUL->add_cont($li);
		$li->addClass("nav-item dropdown");
		$a=$li->add_cont_elem("","a");
		$a->add_class("nav-link dropdown-toggle");
		$id=$this->getElemID("dd");
		$a->set_att("id",$id);
		$a->set_att("href","#");
		$a->set_att("role","button");
		$a->set_att("data-bs-toggle","dropdown");
		$a->set_att("aria-expanded","false");
		$a->add_cont($this->get_a_inner_html());

		
		if($e=$this->getSubHTMLElem("beforelbl",false)){
			$a->add_cont($e);
		}

		if($items=$this->get_items_allowed()){
			$ul=new mwmod_mw_html_elem("ul");
			$ul->set_att("aria-labelledby",$id);
			$ul->addClass("dropdown-menu");
			if($this->isEnd){
				$ul->addClass("dropdown-menu-end");
			}
			$li->add_cont($ul);
			foreach($items as  $item){
				
				if($c=$item->addToHtmlAsDropDownChild($ul,$level+1)){
					$ul->add_cont($c);

				}
			}
		}





		
		




	}

	
	function get_alink(){
		//<a class="dropdown-toggle" data-toggle="dropdown" href="#">
		$r.="<a href='#' ";
		$r.=" class='dropdown-toggle' data-toggle='dropdown' ";
		$r.=">";
		$r.=$this->get_a_inner_html();
		if($e=$this->getSubHTMLElem("beforelbl")){
			$r.=$e->get_as_html();
		}
		$r.=" <i class='fa fa-caret-down'></i>";
		$r.="</a>";
				

		return 	$r;
	}
	
	function get_a_inner_html(){
		$r="";
		$r=$this->innerHTMLbefore;
		if($c=$this->get_param("icon_class")){
			$r.="<span >".$this->get_etq()." </span>";		
			$r.="<i class='$c'></i>";
		}else{
			$r.=$this->get_etq();
		}
		$r.=$this->innerHTMLafter;
		return $r;	
	}
	
	
	function get_html_as_nav_child_inner(){
		$r.=$this->get_alink();
		
		//$r.="<ul class='dropdown-menu' role='menu'>";	
		$r.="<ul class='".$this->getParam("ulclass","dropdown-menu")."' role='menu'>";	
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
	
}

?>