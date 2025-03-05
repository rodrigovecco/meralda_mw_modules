<?php
class mwmod_mw_datafield_js_validfnc extends mwmod_mw_jsobj_function{
	var $validational_fnc;
	function __construct($inner_code=false){
		$this->add_fnc_arg("inputman");
		$this->create_validational_fnc($inner_code);
	}
	function set_validation_js_code_in($js){
		$this->validational_fnc->set_js_code_in($js);
	}
	function get_js_fnc_code_in(){
		$r="inputman.set_validation_function(".$this->validational_fnc->get_as_js_val().");\n";
		return $r;
	}
	function create_validational_fnc($inner_code=false){
		$this->validational_fnc=new mwmod_mw_jsobj_function("");
		$this->validational_fnc->add_fnc_arg("inputman");
		if($inner_code){
			$this->validational_fnc->set_js_code_in($inner_code);
		}else{
			$this->validational_fnc->set_js_code_in("return true;");
				
		}
	}

}
?>