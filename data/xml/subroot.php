<?php
class mwmod_mw_data_xml_subroot extends mwmod_mw_data_xml{
	
	function __construct($id="data"){
		$this->id=$id;
	}
	/*
	function get_margin_tabs(){
		return "";	
	}
	*/
	function is_value_mode(){
		return false;
	}
	
	function get_xml_open(){
		$t=$this->get_margin_tabs();
		$r="$t";
		$r.="<".$this->id.">\n$t<data  dataType='Object'  >\n";	
		return $r;
	}
	function get_xml_close(){
		$r="";
		$t=$this->get_margin_tabs();
		$r.="$t</data>\n";	
		$r.="$t</".$this->id.">";	
		return $r;
	}

}
?>