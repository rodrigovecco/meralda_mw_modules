<?php
class mwmod_mw_data_xml_root extends mwmod_mw_data_xml{
	
	function __construct($id="root"){
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
	function get_sub_root($cod="data"){
		return $this->get_item($cod,true);	
	}
	function create_sub_item($cod){
		$item=new mwmod_mw_data_xml_subroot($cod);
		return $item;	
	}
	
	function get_xml_open(){
		$r=$this->get_margin_tabs();
		$r.="<".$this->id.">\n";	
		return $r;
	}
	function get_xml_close(){
		$r="";
		$t=$this->get_margin_tabs();
		$r.="$t</".$this->id.">";	
		return $r;
	}

}
?>