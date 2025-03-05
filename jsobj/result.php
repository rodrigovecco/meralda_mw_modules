<?php
class mwmod_mw_jsobj_result extends mwmod_mw_jsobj_obj{
	function __construct($fncname,$args=NULL,$argsaslist=false){
		$this->set_fnc_name($fncname);
		$this->args_as_list=$argsaslist;
		if(!is_null($args)){
			$this->set_props($args);	
		}
	}
	function get_as_js_val(){
		$r=$this->get_fnc_name()."(";
		$r.=$this->get_as_js_obj_args_in();
		$r.=")";
		return $r;
	}
}
?>