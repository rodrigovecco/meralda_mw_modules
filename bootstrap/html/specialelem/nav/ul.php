<?php
class mwmod_mw_bootstrap_html_specialelem_nav_ul extends mwmod_mw_bootstrap_html_elem{
	function __construct(){
		$this->set_tagname("ul");
		$this->add_class("nav navbar-nav");
		$this->only_visible_when_has_cont=true;
	}
	function addNavElemByLbl($lbl,$href=false){
		$li=$this->addNavElem();
		$li->setLableAndHref($lbl,$href);
		return $li;
	}
	function addNavElem($subElem=false){
		$li=new mwmod_mw_bootstrap_html_specialelem_nav_li();
		$this->add_cont($li);
		if($subElem){
			$li->setContElem($subElem);	
		}
		return $li;
	}
}

?>