<?php
class mwmod_mw_data_xml_json extends mwmod_mw_data_xml{
	private $data;
	function __construct($id,$data=null){
		$this->id=$id;
		$this->setAllData($data);
	}
	final function setAllData($data){
		if(!(is_array($data)or is_object($data))){
			$data=array();	
		}
		$this->data=$data;
	}
	function setData($data){
		$this->setAllData($data);
	}
	function get_value(){
		$data=$this->getData();
		if((is_array($data)or is_object($data))){
			return json_encode($data);
		}
		return "{}";	
	}
	final function getData(){
		return $this->data;
	}

	function output_xml_cont(){
		$val=$this->get_value();
		echo mw_array2xml_parse_node_string_value($val);
	}
	
	function get_xml_cont(){
		$val=$this->get_value();
		return mw_array2xml_parse_node_string_value($val);
			
	}
	

	
	
	function get_xml_open(){
		$r=$this->get_margin_tabs();
		$r.="<item id='".$this->id."' dataType='json'>";
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