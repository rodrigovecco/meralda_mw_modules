<?php
class mwmod_mw_jsobj_newobject extends mwmod_mw_jsobj_result{
	function __construct($objclass,$args=NULL,$argsaslist=false){
		$this->set_fnc_name($objclass);
		$this->args_as_list=$argsaslist;
		if(!is_null($args)){
			$this->set_props($args);	
		}
	}
	function get_as_js_val(){
		$r="new ".$this->get_fnc_name()."(";
		$r.=$this->get_as_js_obj_args_in();
		$r.=")";
		return $r;
	}
}
?>