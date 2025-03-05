<?php
class mwmod_mw_jsobj_inputs_def extends mwmod_mw_jsobj_inputs_input{
	function __construct($cod,$type=false,$def_js_class_pref=false){
		if($def_js_class_pref){
			$this->setJSClassPref($def_js_class_pref);
		}
		$this->init_js_input_type_mode($cod,$type);
	}
}
?>