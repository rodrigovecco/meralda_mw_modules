<?php
class mwmod_mw_jsobj_code extends mwmod_mw_jsobj_obj{
	function __construct($jscode){
		$this->set_js_code_in($jscode);
	}
	function get_as_js_val(){
		return $this->get_js_fnc_code_in();
	}
}
?>