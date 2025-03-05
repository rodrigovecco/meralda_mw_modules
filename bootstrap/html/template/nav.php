<?php
class mwmod_mw_bootstrap_html_template_nav extends mwmod_mw_bootstrap_html_template_abs{
	var $collapseElemId;
	var $navOutterContainer;
	var $navHeader;
	var $toggleBtn;
	var $navContainer;
	var $navLeft;
	var $navRight;
	var $navs=array();
	var $defNave="left";
	var $defNave1="right";
	
	
	

	
	function __construct($collapseElemId="bs-example-navbar-collapse-1",$display_mode="default"){
		$this->collapseElemId=$collapseElemId;
		$main=new mwmod_mw_bootstrap_html_specialelem_nav($display_mode);
		
		$this->create_cont($main);
		
	}
	
	function createToggleBtn(){
		$btn=new mwmod_mw_bootstrap_html_def("navbar-toggle collapsed");
		$btn->set_tagname("button");
		$btn->set_att("type","button");
		$btn->set_att("data-toggle","collapse");
		$btn->set_att("aria-expanded","false");
		$btn->set_att("data-target","#".$this->collapseElemId);
		$e=$btn->add_cont_elem("Toggle navigation","span");
		$e->add_class("sr-only");
		$e=$btn->add_cont_elem("","span");
		$e->add_class("icon-bar");
		$e=$btn->add_cont_elem("","span");
		$e->add_class("icon-bar");
		$e=$btn->add_cont_elem("","span");
		$e->add_class("icon-bar");
		return $btn;
		
			
	}
	function getNav($code=false){
		if(!$code){
			$code=$this->defNave;	
		}elseif($code===true){
			$code=$this->defNave1;	
		}
		return $this->navs[$code];
	}
	function addNavElemByLbl($lbl,$href=false,$right=false){
		if(!$nav=$this->getNav($right)){
			return false;	
		}
		return $nav->addNavElemByLbl($lbl,$href);
	}
	function addNavElem($right=false,$subElem=false){
		if(!$nav=$this->getNav($right)){
			return false;	
		}
		return $nav->addNavElem($subElem);
	}
	function addLeftNavElem($subElem=false){
		return $this->addNavElem("left",$subElem);
	}
	function addRightNavElem($subElem=false){
		return $this->addNavElem("right",$subElem);
	}
	
	
	function create_cont($main){
		$this->set_main_elem($main);
		$this->navOutterContainer=new mwmod_mw_bootstrap_html_def("container-fluid");
		$main->add_cont($this->navOutterContainer);
		$this->navHeader=new mwmod_mw_bootstrap_html_def("navbar-header");
		$this->navOutterContainer->add_cont($this->navHeader);
		if($this->toggleBtn=$this->createToggleBtn()){
			$this->navHeader->add_cont($this->toggleBtn);
		}
		$this->navContainer=new mwmod_mw_bootstrap_html_def("collapse navbar-collapse");
		$this->navContainer->set_att("id",$this->collapseElemId);
		$this->navOutterContainer->add_cont($this->navContainer);
		$this->navLeft=new mwmod_mw_bootstrap_html_specialelem_nav_ul();
		$this->navs["left"]=$this->navLeft;
		$this->navContainer->add_cont($this->navLeft);
		$this->navRight=new mwmod_mw_bootstrap_html_specialelem_nav_ul();
		$this->navRight->add_class("navbar-right");
		$this->navContainer->add_cont($this->navRight);
		$this->navs["right"]=$this->navRight;
		
	}
	

}

?>