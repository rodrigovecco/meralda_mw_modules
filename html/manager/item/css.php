<?php
//rvh 2015-01-10 v 1
class mwmod_mw_html_manager_item_css extends mwmod_mw_html_manager_item_abs{
	private $_atts=array();
	function __construct($cod,$src=false){
		$this->init_item($cod);
		if($src){
			$this->extenal_src=$src;	
		}
		$this->set_def_atts();
	}
	function set_def_atts(){
		$this->set_att("rel","stylesheet");	
		$this->set_att("type","text/css");	
	}
	final function set_att($cod,$val){
		$this->_atts[$cod]=$val;
	}
	function get_html_declaration(){
		$r="<link";
		if($a=$this->get_atts_for_dec()){
			foreach($a as $cod=>$v){
				$r.=" $cod='$v'";	
			}
		}
		$r.="/>\n";
		return $r;
		
		return "<link href='".$this->get_src()."' rel='stylesheet' type='text/css' />\n";
	}
	function get_atts_for_dec(){
		$r=array();
		$r["href"]=$this->get_src();
		if($a=$this->get_all_atts()){
			foreach($a as $cod=>$v){
				if($v){
					$r[$cod]=$v;	
				}
			}
		}
		return $r;
	}
	final function get_all_atts(){
		return $this->_atts;
	}

	
}
?>