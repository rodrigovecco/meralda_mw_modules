<?php
class mwmod_mw_data_xml_array extends mwmod_mw_data_xml{
	function __construct($id){
		$this->id=$id;
	}
	function addData($data=NULL){
		$cod=$this->get_new_item_auto_id();
		$item=new mwmod_mw_data_xml($cod);
		$this->add_sub_item($item);
		if(!is_null($data)){
			$item->set_value($data);	
		}
		return $item;
	}
	function addHTML($htmlElem=false){
		$item=$this->addHTMLitem($htmlElem);
		return $item->get_html_elem();
	}
	function addHTMLitem($htmlElem=false){
		$cod=$this->get_new_item_auto_id();
		$item=new mwmod_mw_data_xml_html($cod,$htmlElem);
		$this->add_sub_item($item);
		return $item;
	}

	
	function get_xml_open(){
		$r=$this->get_margin_tabs();
		//$val=$this->get_value();
		//$dtype=mw_array2xml_str_data_type($val);
		$r.="<item id='".$this->id."' dataType='Array'>";
		return $r;
	}
	function get_xml_close(){
		$r="";
		$r.=$this->get_margin_tabs();	
		$r.="</item>\n";
		return $r;
	}

}
?>