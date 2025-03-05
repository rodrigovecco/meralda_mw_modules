<?php
//
class mwmod_mw_jsobj_jquery_action extends mwmod_mw_jsobj_codecontainer{
	var $selname;
	var $action;
	function __construct($selname,$action, $jscode=false){
		$this->action=$action;
		$this->selname=$selname;
		$this->add_cont($jscode);
	}
	function get_as_js_val_open(){
		return "$('".$this->selname."').".$this->action."(\n";	
	}
	function get_as_js_val_close(){
		return ");\n";	
	}

}
?>