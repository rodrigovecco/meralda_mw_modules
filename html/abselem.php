<?php

abstract class mwmod_mw_html_abselem extends mw_apsubbaseobj{
	private $atts;
	private $nlonclose;
	private $nloncont;

	
	final function setNLonClose($val=true){
		$this->nlonclose=$val;
	}
	final function setNLonCont($val=true){
		$this->nloncont=$val;
	}
	
	final function init_atts($atts=false){
		if($atts){
			if(is_object($atts)){
				if(is_a($atts,"mwmod_mw_html_atts")){
					$this->atts=$atts;
					
					return $this->atts;
				}
			}
		}
		$this->atts=new mwmod_mw_html_atts($atts);
		return $this->atts;
	}
	function nloncloseDef(){
		return false;	
	}
	final function __get_priv_nlonclose(){
		if(!isset($this->nlonclose)){
			return $this->nloncloseDef();
		}
		return $this->nlonclose;	
	}
	function nloncontDef(){
		return false;	
	}
	final function __get_priv_nloncont(){
		if(!isset($this->nloncont)){
			return $this->nloncontDef();
		}
		return $this->nloncont;	
	}
	
	final function __get_priv_atts(){
		if(!isset($this->atts)){
			$this->atts=new mwmod_mw_html_atts();	
		}
		return $this->atts;	
	}
	
}

?>