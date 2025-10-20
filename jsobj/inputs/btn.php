<?php
class mwmod_mw_jsobj_inputs_btn extends mwmod_mw_jsobj_inputs_def{
	function __construct($cod,$type=false,$def_js_class_pref=false){
		$this->def_js_class="mw_datainput_item_btn";
		$this->def_js_class_type="btn";
		if($def_js_class_pref){
			$this->setJSClassPref($def_js_class_pref);
		}
		$this->init_js_input_type_mode($cod,$type);
	}
	/**
	 * @param mixed $fnc 
	 * @return mwmod_mw_jsobj_functionext 
	 */
	function setOnclick($fnc=null){
		$addarg=false;
		if(!$fnc){
			$addarg=true;
		}
		$f=$this->addFunction("onclick",$fnc);
		if($addarg){
			$f->add_fnc_arg("btn");
		}
		return $f;
	}
	
}
?>