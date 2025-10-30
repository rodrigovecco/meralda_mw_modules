<?php
class mwmod_mw_jsobj_inputs_number extends mwmod_mw_jsobj_inputs_def{
	function __construct($cod,$type=false,$def_js_class_pref=false){
		$this->def_js_class="mw_datainput_item_number";
		$this->def_js_class_type="number";
		if($def_js_class_pref){
			$this->setJSClassPref($def_js_class_pref);
		}
		$this->init_js_input_type_mode($cod,$type);
	}
	function setMax($max){
		$this->set_prop("max",$max);
	}
	function setMin($min){
		$this->set_prop("min",$min);
	}
	function setInt($isint=true){
		$this->set_prop("int",$isint);
	}
	
}
?>