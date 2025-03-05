<?php
class mwmod_mw_jsobj_dataoptim_field extends mw_apsubbaseobj{
	private $cod;
	private $mode="default";
	var $dateFormat="Y/m/d H:i:s";
	var $nulldate="";
	function __construct($cod){
		$this->set_cod($cod);
	}
	function get_default_value(){
		return $this->get_value("");	
	}
	function get_val_as_date($val){
		if(!$val){
			return $this->nulldate;	
		}
		if(is_string($val)){
			if(!$time=strtotime($val)){
				return $this->nulldate;	
			}
			if($time<=0){
				return $this->nulldate;	
			}
			return date($this->dateFormat,$time);
		}elseif(is_numeric($val)){
			return date($this->dateFormat,$val);	
		}
		//ver si es otro tipo de fecha;
		return $this->nulldate;	
		
		
	}
	
	function get_value($val){
		if($this->is_mode("boolean")){
			if($val){
				return true;	
			}else{
				return false;	
			}
		}
		if($this->is_mode("numeric")){
			if(is_numeric($val)){
				return $val+0;	
			}
			return null;	
		}
		if($this->is_mode("date")){
			return $this->get_val_as_date($val);	
		}
		if($this->is_mode("string")){
			return $val."";	
		}
		return $val;
		
	}
	
	final function boolean_mode(){
		$this->mode="boolean";	
	}
	final function numeric_mode(){
		$this->mode="numeric";	
	}
	final function text_mode(){
		$this->mode="string";	
	}
	final function def_mode(){
		$this->mode="default";	
	}
	final function date_mode($nouhour=false){
		if($nouhour){
			$this->dateFormat="Y/m/d";	
		}else{
			$this->dateFormat="Y/m/d H:i:s";		
		}
		$this->mode="date";	
	}
	final function is_mode($cod){
		if($cod==$this->mode){
			return true;	
		}
		return false;
	}
	final function set_cod($cod){
		$this->cod=$cod;	
	}
	final function __get_priv_cod(){
		return $this->cod;	
	}
	final function __get_priv_mode(){
		return $this->mode;	
	}
	
}
?>