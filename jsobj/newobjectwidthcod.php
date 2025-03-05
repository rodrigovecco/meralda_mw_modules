<?php
class mwmod_mw_jsobj_newobjectwidthcod extends mwmod_mw_jsobj_result{
	var $cod;
	function __construct($cod,$objclass,$args=NULL){
		$this->cod=$cod;
		$this->set_fnc_name($objclass);
		$this->args_as_list=false;
		if(!is_null($args)){
			$this->set_props($args);	
		}
	}
	function get_as_js_val(){
		$r="new ".$this->get_fnc_name()."('".$this->cod."',";
		$r.=$this->get_as_js_obj_args_in();
		$r.=")";
		return $r;
	}
}
?>