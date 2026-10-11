<?php

class mwmod_mw_jsobj_str extends mwmod_mw_jsobj_obj{
	var $str="";
	function __construct($str=""){
		$this->set_value($str);
	}
	function set_value($str=""){
		$this->str=$str;
	}
	function get_as_js_val(){
		return "'".mwmod_mw_util_text::js_escape($this->str)."'";	
		
	}

}
?>