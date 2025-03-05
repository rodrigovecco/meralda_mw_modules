<?php
class mwmod_mw_jsobj_inputs_btnsgr extends mwmod_mw_jsobj_inputs_def{
	function __construct($cod,$type=false,$def_js_class_pref=false){
		$this->def_js_class="mw_datainput_item_btnsgroup";
		$this->def_js_class_type="btnsgroup";
		if($def_js_class_pref){
			$this->setJSClassPref($def_js_class_pref);
		}
		$this->init_js_input_type_mode($cod,$type);
	}
	
}
?>