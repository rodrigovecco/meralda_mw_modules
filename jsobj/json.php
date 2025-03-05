<?php
class mwmod_mw_jsobj_json extends mwmod_mw_jsobj_obj{
	var $jsonStr="{}";
	function __construct($jsonStr="{}"){
		$this->jsonStr=$jsonStr;	
	}
	function set_value($jsonStr="{}"){
		$this->jsonStr=$jsonStr;	
	}
	function get_as_js_val(){
		$r=$this->jsonStr;
		return $r;
	}

}
?>