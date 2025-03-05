<?php
class mwmod_mw_data_xml_html extends mwmod_mw_data_xml{
	var $html;
	function __construct($id,$html=false){
		$this->id=$id;
		$this->set_html($html);
	}
	function get_html_elem(){
		if($this->html){
			return $this->html;	
		}
		return $this->set_html(false);
	}
	function set_html($html=false){
		if($html){
			if(is_object($html)){
				$this->html=$html;
				return $html;
			
			}
		}
		$html= new mwmod_mw_html_cont_varcont($html);
		$this->html=$html;
		return $html;
	}
	function get_value(){
		
		if($this->html){
			return $this->html->get_as_html();	
		}
		return "";	
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
		$r.="<item id='".$this->id."' dataType='String'>";
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