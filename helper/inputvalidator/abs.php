<?php

abstract class mwmod_mw_helper_inputvalidator_abs extends mw_baseobj{
	var $dont_strip_tags=false;
	var $val=false;
	var $list_mode=false;
	var $cod;
	var $parent;
	var $included_on_parent_list=false;
	
	private $_items=array();

	//20231222
	function getValueStrTrim($cod=false){
		$v=$this->getValueStr($cod);

		if(is_string($v)){
			return trim($v);

		}
		if(is_numeric($v)){
			return $v."";
		}
	}
	function getValueStr($cod=false){
		$v=$this->get_value_by_dot_cod($cod);
		if(is_numeric($v)){
			return $v."";
		}
		if(is_string($v)){
			return $v."";
		}

	}
	function getValuesFromDoptim($cod=false){
		if(!$cod){
			$item=$this;	
		}else{
			$item=$this->get_item_by_dot_cod($cod,false);	
		}
		if(!$item){
			return false;	
		}
		return $item->_getValuesFromDoptim();
	}
	function _getValuesFromDoptim(){
		if(!is_array($this->val)){
			return false;
		}
		if(!is_array($this->val["keys"])){
			return false;
		}
		if(!is_array($this->val["data"])){
			return false;
		}
		$keys=array();
		reset($this->val["keys"]);
		foreach($this->val["keys"] as $key){
			if($key=$this->check_str_key_alnum_underscore($key)){
				$keys[$key]=$key;
			}else{
				return false;
			}
		}
		reset($this->val["data"]);
		$result=array();
		foreach($this->val["data"] as $d){
			if(is_array($d)){
				reset($keys);
				$r=array();
				$x=0;
				foreach($keys as $key){
					$r[$key]=$d[$x]??null;
					$x++;
				}
				$result[]=$r;
			}
			

		}
		return $result;



	}


	function reset_included_on_parent_list(){
		$this->included_on_parent_list=false;	
	}
	function set_included_on_parent_list($val=true){
		$this->included_on_parent_list=$val;	
	}
	function reset_included_list_items(){
		if($items =$this->get_items()){
			foreach($items as $cod=>$item){
				$item->reset_included_on_parent_list();
			}
		}
			
	}
	function is_included_on_parent_list(){
		return $this->included_on_parent_list;	
	}
	function set_value($val){
		$this->val=$val;
		$this->list_mode=false;
		if(is_array($val)){
			$this->reset_included_list_items();
			$this->list_mode=true;
			foreach($val as $c=>$v){
				if($item=$this->get_item($c,true)){
					$item->set_included_on_parent_list(true);
					$item->set_value($v);	
				}
			}
		}else{
			$this->val=$val;	
		}
	}
	function get_validated_value(){
		$val=$this->get_orig_value();
		if($this->dont_strip_tags){
			return $val;
		}
		$val=strip_tags($val."");
		return $val;
		
	}
	function get_text_value(){
		$v=$this->get_orig_value();
		if(is_array($v)){
			return "";
			
		}
		return $v."";
	}
	function get_orig_value(){
		return $this->val;
	}
	function get_value(){
		if($this->list_mode){
			return $this->get_value_as_list();	
		}
		return $this->get_validated_value();
		
			
	}
	function get_value_as_list(){
		if(!$this->list_mode){
			return false;	
		}
		$r=array();
		if($items =$this->get_items()){
			foreach($items as $cod=>$item){
				if($item->is_included_on_parent_list()){
					$r[$cod]=$item->get_value();
				}
			}
		}
		return $r;
		
	}
	function get_value_by_dot_cod($cod=false){
		if(!$cod){
			$item=$this;	
		}else{
			$item=$this->get_item_by_dot_cod($cod,false);	
		}
		if(!$item){
			return false;	
		}
		return $item->get_value();
	}
	function get_value_as_time_if_date_ok(){
		if($this->list_mode){
			return false;	
		}
		if(!$date=$this->get_validated_value()){
			//return time();
			return false;	
		}
		
		$parts=explode(" ",$date."");
		if(!$parts[0]){
			return false;	
		}
		$dd=str_replace("/","-",$parts[0]);
		$d=explode("-",$dd);
		if(!checkdate($d[1],$d[2],$d[0])){
			return false;	
		}
		return strtotime($date);
		
		
		
		
			
	}
	function get_value_as_time($format=false){
		if(!$time=$this->get_value_as_time_if_date_ok()){
			
			return false;	
		}
		if($format){
			return date($format,$time);
		}
		return $time;
	}
	function get_value_by_dot_cod_as_datetime($cod=false){
		return $this->get_value_by_dot_cod_as_time($cod,"Y-m-d H:i:s");	
	}
	function get_value_by_dot_cod_as_date($cod=false){
		return $this->get_value_by_dot_cod_as_time($cod,"Y-m-d");	
	}
	
	function get_value_by_dot_cod_as_time($cod=false,$format=false){
		if(!$item=$this->get_item_by_dot_cod_or_this($cod)){
			return false;	
		}
		return $item->get_value_as_time($format);
	}

	function get_item_by_dot_cod_or_this($cod=false){
		if(!$cod){
			return $this;	
		}else{
			return $this->get_item_by_dot_cod($cod,false);	
		}
			
	}
	function get_value_as_list_restricted($allowed,$cod=false){
		if(!$list=$this->get_value_by_dot_cod_as_list($cod)){
			return false;
		}
		$r=array();
		if(!is_array($allowed)){
			$allowed=explode(",",$allowed."");	
		}
		foreach($list as $c=>$v){
			if(in_array($c,$allowed)){
				$r[$c]=$v;
			}
		}
		if(sizeof($r)){
			return $r;	
		}
	}

	function get_value_by_dot_cod_as_list($cod=false){
		if(!$cod){
			$item=$this;	
		}else{
			$item=$this->get_item_by_dot_cod($cod,false);	
		}
		if(!$item){
			return false;	
		}
		return $item->get_value_as_list();
	}
	function value_exists($cod){
		return $this->get_item_by_dot_cod($cod,false);
	}
	function get_item_by_dot_cod($cod,$create=true){
		if(!strlen($cod)){
			return false;
		}
		$cods=explode(".",$cod,2);
		$sub=false;
		$c=$cods[0];
		if(!strlen($c)){
			return false;
		}
		if(isset($cods[1])and(strlen($cods[1]))){
			$sub=$cods[1];	
		}
		if(!$item=$this->get_item($c,$create)){
			return false;
		}
		if($sub){
			return $item->get_item_by_dot_cod($sub,$create);	
		}else{
			return $item;	
		}
		
		
	}
	final function add_item($item){
		$cod=$item->cod;
		$this->_items[$cod]=$item;
		return $item;	
	}
	final function get_items(){
		return $this->_items;
	}

	final function get_item($cod,$create=true){
		if(!strlen($cod)){
			return false;
		}
		if(isset($this->_items[$cod]) and $this->_items[$cod]){
			return 	$this->_items[$cod];
		}
		if(!$create){
			return false;	
		}
		if(!$item=$this->create_item($cod)){
			return false;
		}
		return $this->add_item($item);
		
	}
	function create_item($cod){
		if(!strlen($cod)){
			return false;
		}
		if(!$cod=$this->checkItemCode($cod)){
			return false;
		}
		$item =new mwmod_mw_helper_inputvalidator_item($cod,$this);
		return $item;
		
	}
	function checkItemCode($cod){
		if($this->parent){
			return $this->parent->checkItemCode($cod);
		}
		return $this->checkItemCodeAsMainParent($cod);
	}
	function checkItemCodeAsMainParent($cod){

		if(!strlen($cod)){
			return false;
		}
		if($cod=$this->check_str_key_alnum_underscore($cod)){
			return $cod;
		}


	}
}
?>