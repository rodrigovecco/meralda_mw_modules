<?php
class mwmod_mw_jsobj_vardeclaration extends mwmod_mw_jsobj_obj{
	var $varname;
	var $value;
	function __construct($varname,$value=false){
		$this->varname=$varname;
		$this->value=$value;
	}
	function get_value_as_js_val(){
		if($this->value){
			if(is_object($this->value)){
				if(method_exists($this->value,"get_as_js_val")){
					return $this->value->get_as_js_val();
				}
			}
		}
		return $this->get_prop_as_js_val($this->value);
	}
	function get_as_js_val(){
		$r="var ".$this->varname."=";
		$r.=$this->get_value_as_js_val();
		//$this->get_as_js_obj_args_in();
		$r.=";\n";
		return $r;
	}
}
?>