<?php
class mwmod_mw_data_cfg_item extends mw_apsubbaseobj{
	var $default_value;
	var $value;
	var $values=array();
	var $value_as_list_no_blank;
	var $boolean_value;
	private $man;
	private $cod;
	function __construct($cod,$man){
		$this->init($cod,$man);	
	}
	function get_boolean(){
		if(isset($this->boolean_value)){
			return $this->boolean_value;	
		}
		$this->boolean_value=$this->load_boolean_value();
		return $this->boolean_value;
		
	}
	function load_boolean_value(){
		$val=$this->get_value(false);
		if(is_bool($val)){
			return $val;	
		}
		if(is_numeric($val)){
			if($val){
				return true;	
			}else{
				return false;	
			}
		}
		if(!$val){
			return false;	
		}
		$val=strtolower(trim($val));
		if(in_array($val,array("true","yes","enabled","ok","y"))){
			return true;
		}
		return false;
	
	}
	function get_value($def=false){
		if(isset($this->value)){
			return $this->value;	
		}
		if(isset($this->default_value)){
			return $this->default_value;	
		}
		return $def;	
	}
	function is_on_list($cod){
		if(!$cod){
			return false;	
		}
		$this->init_value_as_list_no_blank();
		return $this->value_as_list_no_blank[$cod]??null;
		
	}

	function get_value_as_list_no_blank(){
		$this->init_value_as_list_no_blank();
		return $this->value_as_list_no_blank;		
	}
	function init_value_as_list_no_blank(){
		if(is_array($this->value_as_list_no_blank)){
			return;	
		}
		$this->value_as_list_no_blank=array();
		if($str=$this->get_value().""){
			$list=explode(",",$str);
			foreach($list as $v){
				if($v=trim($v)){
					$this->value_as_list_no_blank[$v]=$v;	
				}
			}
		}
	
	}
	function get_debug_data(){
		$r=array(
			"value"=>$this->get_value(),
			"default_value"=>$this->default_value,
			"values"=>$this->values,
		);
		return $r;	
	}
	function add_value($value,$src_cod=false,$src=false){
		if(!isset($this->default_value)){
			$this->default_value=$value;	
		}
		$this->value=$value;
		if($src_cod){
			$this->values[$src_cod]=$value;	
		}
		return true;
	}
	
	final function init($cod,$man){
		$this->cod=$cod;
		$this->man=$man;
	}
	final function __get_priv_cod(){
		return $this->cod;	
	}
	final function __get_priv_man(){
		return $this->man;	
	}

}


?>