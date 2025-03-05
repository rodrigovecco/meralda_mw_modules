<?php
//
class mwmod_mw_jsobj_function extends mwmod_mw_jsobj_code{
	private $_fnc_args;
	function __construct($jscode){
		$this->set_js_code_in($jscode);
	}
	final function add_fnc_arg($varname){
		if(!is_array($this->_fnc_args)){
			$this->_fnc_args=array();
		}
		$this->_fnc_args[]=$varname;
	}
	final function get_arg_by_index($i=0){
		$i=$i+0;
		if(is_array($this->_fnc_args)){
			return $this->_fnc_args[$i];
		}
			
	}
	
	function get_fnc_args_str(){
		if(!$args=$this->get_fnc_args()){
			return "";
		}
		return implode(",",$args);
	}
	final function get_fnc_args(){
		if(is_array($this->_fnc_args)){
			return $this->_fnc_args;
		}
			
	}
	function get_props_as_array_or_str(){
		return $this->get_as_js_val();
	}
	
	function get_as_js_val(){
		return $this->get_as_js_fnc();
	}
	
	function get_as_js_fnc(){
		$r="function(".$this->get_fnc_args_str()."){".$this->get_js_fnc_code_in()."}";	
		return $r;
	}

}
?>