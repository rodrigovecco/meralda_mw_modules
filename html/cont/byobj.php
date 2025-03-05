<?php

class mwmod_mw_html_cont_byobj extends mw_baseobj implements mwmod_mw_html_cont_interface{
	private $outputobj;
	private $outputobj_method_get;
	private $outputobj_method_do;
	private $outputobj_arg=NULL;
	
	function __construct($outputobj,$outputobj_method_get=false,$outputobj_method_do=false,$outputobj_arg=NULL){
		$this->set_output_obj($outputobj,$outputobj_method_get,$outputobj_method_do,$outputobj_arg);
	}
	final function set_output_obj($outputobj,$outputobj_method_get=false,$outputobj_method_do=false,$outputobj_arg=NULL){
		$this->unset_output_obj();
		if(!$outputobj){
			return false;	
		}
		if(!is_object($outputobj)){
			return false;	
		}
		$r=0;
		$this->outputobj=$outputobj;
		if($this->set_method_get($outputobj_method_get)){
			$r++;	
		}
		if($this->set_method_do($outputobj_method_do)){
			$r++;	
		}
		$this->set_arg($outputobj_arg);
		return $r;
		
	}
	final function set_arg($outputobj_arg=NULL){
		$this->outputobj_arg=$outputobj_arg;
	}
	
	final function set_method_get($outputobj_method_get){
		unset($this->outputobj_method_get);
		if(!$outputobj_method_get){
			return false;	
		}
		if(!is_string($outputobj_method_get)){
			return false;	
		}
		if(!$this->outputobj){
			return false;	
		}
		if(!is_object($this->outputobj)){
			return false;	
		}
		if(method_exists($this->outputobj,$outputobj_method_get)){
			$this->outputobj_method_get=$outputobj_method_get;
			return true;	
		}
		return false;
		
			
	}
	final function set_method_do($outputobj_method_do){
		unset($this->outputobj_method_do);
		if(!$outputobj_method_do){
			return false;	
		}
		if(!is_string($outputobj_method_do)){
			return false;	
		}
		if(!$this->outputobj){
			return false;	
		}
		if(!is_object($this->outputobj)){
			return false;	
		}
		if(method_exists($this->outputobj,$outputobj_method_do)){
			$this->outputobj_method_do=$outputobj_method_do;
			return true;	
		}
		return false;
		
			
	}
	
	final function unset_output_obj(){
		unset($this->outputobj);	
		unset($this->outputobj_method_get);	
		unset($this->outputobj_method_do);	
		unset($this->outputobj_arg);	
	}
	function get_as_html(){
		return $this->get_as_html_from_obj();
	}
	function get_as_html_from_obj(){
		if($this->check_get_as_html_from_get_method()){
			return $this->get_as_html_from_get_method();
		}else{
			return $this->get_as_html_from_output_method();
		}
		
	}
	function get_as_html_from_output_method(){
		ob_start();
		
		$this->do_output_from_obj_by_output_method();
		$html=ob_get_clean();
		return $html;
	}
	function get_as_html_from_get_method(){
		if($this->outputobj){
			if(is_object($this->outputobj)){
				if($this->outputobj_method_get){
					if(method_exists($this->outputobj,$this->outputobj_method_get)){
						if(is_null($this->outputobj_arg)){
							return $this->outputobj->$this->outputobj_method_get();
						}else{
							return $this->outputobj->$this->outputobj_method_get($this->outputobj_arg);	
						}
					}
				}
			}
		}
		return "";
		
	}
	function check_get_as_html_from_get_method(){
		if($this->outputobj){
			if(is_object($this->outputobj)){
				if($this->outputobj_method_get){
					if(method_exists($this->outputobj,$this->outputobj_method_get)){
						return true;
					}
				}
			}
		}
		return false;
		
	}
	
	function do_output(){
		$this->do_output_from_obj();
	}
	
	function do_output_from_obj(){
		if($this->do_output_from_obj_by_output_method()){
			return true;	
		}
		if($this->check_get_as_html_from_get_method()){
			echo $this->get_as_html_from_get_method();
			return true;	
		}
		
		return false;
		
	}
	function do_output_from_obj_by_output_method(){
		if($this->outputobj){
			if(is_object($this->outputobj)){
				if($this->outputobj_method_do){
					if(method_exists($this->outputobj,$this->outputobj_method_do)){
						if(is_null($this->outputobj_arg)){
							$this->outputobj->$this->outputobj_method_do();
							return true;
						}else{
							$this->outputobj->$this->outputobj_method_do($this->outputobj_arg);	
							return true;
						}
					}
				}
			}
		}
		
	}
	
	
	function get_debug_info(){
		$r["this"]=get_class($this);
		if($this->outputobj){
			if(is_object($this->outputobj)){
				$r["outputobj"]=get_class($this->outputobj);
			}
		}
		$r["get"]=$this->explain_get();
		$r["output"]=$this->explain_output();
		
		return $r;
	}
	
	final function __get_priv_outputobj(){
		return $this->outputobj;	
	}
	final function __get_priv_outputobj_method_get(){
		return $this->outputobj_method_get;	
	}
	final function __get_priv_outputobj_method_do(){
		return $this->outputobj_method_do;	
	}
	final function __get_priv_outputobj_arg(){
		return $this->outputobj_arg;	
	}
	function explain_get(){
		if($this->outputobj){
			if(is_object($this->outputobj)){
				if($this->outputobj_method_get){
					if(is_null($this->outputobj_arg)){
						return get_class($this->outputobj)."->".$this->outputobj_method_get."()";	
					}else{
						return get_class($this->outputobj)."->".$this->outputobj_method_get."(arg)";	
					}
				}
				return get_class($this->outputobj)." NO GET METHOD";	
			}
		}
		return " NO OUTPUT OBJ";	
	}
	function explain_output(){
		if($this->outputobj){
			if(is_object($this->outputobj)){
				if($this->outputobj_method_do){
					if(is_null($this->outputobj_arg)){
						return get_class($this->outputobj)."->".$this->outputobj_method_do."()";	
					}else{
						return get_class($this->outputobj)."->".$this->outputobj_method_do."(arg)";	
					}
				}elseif($this->outputobj_method_get){
					if(is_null($this->outputobj_arg)){
						return "echo ".get_class($this->outputobj)."->".$this->outputobj_method_get."()";	
					}else{
						return "echo ".get_class($this->outputobj)."->".$this->outputobj_method_get."(arg)";	
					}
						
				}
				return get_class($this->outputobj)." NO OUTPUT METHOD";	
			}
		}
		return " NO OUTPUT OBJ";	
	}
	
	function __toString(){
		return $this->explain_get();	
	}
	
}

?>