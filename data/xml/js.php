<?php
class mwmod_mw_data_xml_js extends mwmod_mw_data_xml{
	var $js;
	function __construct($id,$js=false){
		$this->id=$id;
		$this->set_js($js);
	}
	function get_js(){
		if($this->js){
			return $this->js;	
		}
		return $this->set_js(false);
	}
	function set_js($js){
		if($js){
			if(is_object($js)){
				$this->js=$js;
				$this->js->xml_parent=$this;
				return $js;
			
			}
		}
		$js= new mwmod_mw_jsobj_obj($js);
		$this->js=$js;
		$this->js->xml_parent=$this;
		return $js;
	}
	function get_value(){
		
		if($this->js){
			return $this->js->get_as_js_val();	
		}
		return "{}";	
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
		//$val=$this->get_value();
		//$dtype=mw_array2xml_str_data_type($val);
		$r.="<item id='".$this->id."' dataType='JSObject'>";
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