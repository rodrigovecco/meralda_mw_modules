<?php
class mwmod_mw_bootstrap_html_specialelem_nav_li extends mwmod_mw_bootstrap_html_elem{
	public $contElem;
	public $lblElem;
	public $caretContainer;
	public $caret;
	public $nav;
	function __construct(){
		$this->set_tagname("li");
		//$this->nloncont=true;

	}
	function addNavElemByLbl($lbl,$href=false){
		//must do makeDropDown
		if($this->nav){
			return $this->nav->addNavElemByLbl($lbl,$href);	
		}
	}
	function addNavElem($subElem=false){
		//must do makeDropDown
		$li=new mwmod_mw_bootstrap_html_specialelem_nav_li();
		if($this->nav){
			return $this->nav->addNavElem($subElem);	
		}
	}

	
	function makeDropDown(){
		$this->add_class("dropdown");
		$this->getLblElem();
		if($e=$this->getContElem()){
			$e->setAtts('href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"');
			if(!$this->caretContainer){
				$this->caretContainer=new mwmod_mw_html_cont_varcont();
				//$this->caretContainer->add_cont("&nbsp;");
				$e->add_cont($this->caretContainer);
				$this->caret=new mwmod_mw_bootstrap_html_elem("span");
				$this->caret->setAtts('class="caret"');
				$this->caretContainer->add_cont($this->caret);
			}
			
		}
		$this->getNav();
		
	}
	function getNav(){
		if(!$this->nav){
			$this->getContElem();
			$this->nav=new mwmod_mw_bootstrap_html_specialelem_nav_uldd();
			$this->add_cont($this->nav);
				
		}
		return $this->nav;
		
	}
	
	
	
	function makeDivider(){
		$this->add_class("divider");	
		$this->set_att("role","separator");	
	}
	function setLable($str){
		if($e=$this->getLblElem()){
			$e->set_cont($str);
			return true;	
		}
	}
	
	function addLable($str){
		if($e=$this->getLblElem()){
			$e->add_cont($str);
			return true;	
		}
	}
	function setHref($str){
		if($e=$this->getContElem()){
			$e->set_att("href",$str);
			return true;	
		}
	}
	function setLableAndHref($lbl,$href){
		if($lbl){
			$this->addLable($lbl);	
		}
		if($href){
			$this->setHref($href);	
		}
		
	}
	
	
	function createContElem(){
		$elem=new mwmod_mw_bootstrap_html_elem("a");
		return $elem;
	}
	function setContElem($elem){
		$this->contElem=$elem;
		$this->set_cont($elem);
		return $elem;
	}
	function getContElem(){
		if(!$this->contElem){
			if($e=$this->createContElem()){
				$this->setContElem($e);	
			}else{
				return false;
			}
		}
		return $this->contElem;
	}
	function getLblElem(){
		if(!$this->lblElem){
			if($e=$this->getContElem()){
				$this->lblElem=new mwmod_mw_html_cont_varcont();
				$e->add_cont($this->lblElem);
			}else{
				return false;
			}
		}
		return $this->lblElem;
	}
}

?>