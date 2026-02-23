<?php
//not used or tested
class mwmod_mw_jsobj_helper_modal extends mwmod_mw_jsobj_newobject{
	
	public $def_js_class;
	function __construct(){
	
		$this->set_fnc_name("mwuihelper_modal_populator");
	}

	function setInputs($jsInputs){
		$this->set_prop("inputs",$jsInputs);
	}
	function setTitle($title){
		$this->set_prop("title",$title);
	}

	
}
?>