<?php
class mwmod_mw_data_xml_singleroot extends mwmod_mw_data_xml{
	
	function __construct($id="root"){
		$this->id=$id;
	}
	/*
	function get_margin_tabs(){
		return "";	
	}
	*/
	function get_xml_open(){
		$t=$this->get_margin_tabs();
		$r=$t;
		$r.="<".$this->id.">\n$t<data>\n$t<data  dataType='Object'  >\n";	
		return $r;
	}
	function get_xml_close(){
		$t=$this->get_margin_tabs();
		$r="";
		$r.="$t</data>\n";	
		$r.="$t</data>\n";	
		$r.="$t</".$this->id.">";	
		return $r;
	}

}
?>