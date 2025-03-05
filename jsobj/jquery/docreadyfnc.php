<?php
//
class mwmod_mw_jsobj_jquery_docreadyfnc extends mwmod_mw_jsobj_codecontainer{
	function __construct($jscode=false){
		$this->add_cont($jscode);
		$this->outputAsScriptOnHTML=true;
	}
	function get_as_js_val_open(){
		return "$(function (){\n";	
	}
	function get_as_js_val_close(){
		return "\n});\n";	
	}

}
?>