<?php
class mwmod_mw_helper_text extends mw_baseobj{
	private $value=NULL;
	function __construct($v=NULL){
		if($v){
			$this->set_value($v);	
		}
	}

	function fillZeros($digitsnum=3,$val=NULL){
		$digitsnum=abs(round($digitsnum+0));
		$str=($this->get_str($val)+0)."";
		while(strlen($str)<$digitsnum){
			$str="0".$str;	
		}
		return $str;
		
		
		
	}
	function remove_double($needle,$v=NULL){
		if(!$str=$this->get_str($v)){
			return "";	
		}
		if(!strlen($needle)){
			return $str;	
		}
		$nn=$needle.$needle."";
		
		while(strpos($str,$nn)!=false){
			$str=str_replace($nn,$needle,$str);	
			
		}
		return $str;
		
	}
	function fix_quotes_slashes($v=NULL){
		if(!$str=$this->get_str($v)){
			return "";	
		}
		$str=$this->remove_double("\\",$str);
		$str=str_replace("\\'","'",$str);	
		$str=str_replace("\\\"","\"",$str);	
		return $str;
	}
	
	
	function get_str($v=NULL){
		if(is_null($v)){
			if($this->has_value()){
				$v=$this->value;	
			}
		}
		return $v."";
	}
	function has_value(){
		if(is_null($this->value)){
			return false;	
		}
		return true;
	}
	final function set_value($v=NULL){
		$this->value=$v;
	}
	final function __get_priv_value(){
		return $this->value;	
	}
}
?>