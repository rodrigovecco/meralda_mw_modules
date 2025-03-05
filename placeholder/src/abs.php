<?php
abstract class mwmod_mw_placeholder_src_abs extends mw_apsubbaseobj{
	private $cod;
	private $_items=array();
	var $text="";
	final function init($cod){
		$this->cod=$cod;
	}
	function getDebugData(){
		$r=array();
		if($t=$this->get_text()){
			$r["value"]=$t;	
		}
		if($items=$this->get_items()){
			$r["items"]=array();
			foreach($items as $c=>$i){
				$r["items"][$c]=$i->getDebugData();	
			}
		}
		return $r;
	}
	function get_text($strparams=false){
		return $this->text;
	}
	function setSubValue($cod,$value){
		return $this->setValue($value,$cod);
	}
	
	function setValue($value,$cod=false){
		if(!$item=$this->getItem($cod)){
			return false;	
		}
		$item->doSetValue($value);
		return $item;
	}
	function doSetValue($value){
		if(is_array($value)){
			foreach($value as $cod=>$v){
				if($cod){
					if($item=$this->getItem($cod)){
						$item->setValue($v);
					}
				}
			}
		}else{
			$this->set_txt($value);	
		}
		
	}
	
	function getItem($cod=false){
		if(!$cod){
			return $this;	
		}
		
		$list=explode(".",$cod,2);
		if(!$item=$this->get_or_create_item($list[0])){
			return false;	
		}

		if(!$rest=$list[1]??null){
			return $item;	
		}
		return $item->getItem($list[1]);
		
	}
	function get_item_by_dot_cod($cod){
		if(!$cod){
			return $this;	
		}
		
		$list=explode(".",$cod,2);
		if(!$item=$this->get_item($list[0]??null)){
			return false;	
		}
		return $item->get_item_by_dot_cod($list[1]??null);
		
	}
	
	function set_value($value){
		if(is_array($value)){
			foreach($value as $cod=>$v){
				$this->add_item_by_cod($v,$cod);	
			}
		}else{
			$this->set_txt($value);	
		}
	}
	function set_txt($value){
		if($value){
			if(is_object($value)){
				if(method_exists($value,"__toString")){
					$value=$value."";	
				}else{
					$value="";	
				}
			}
		}
		$this->text=$value."";
	}
	function add_item_by_cod($itemortxt,$cod){
		if(!$cod){
			if($itemortxt){
				if(is_object($itemortxt)){
					$cod=$itemortxt->cod;	
				}
			}
		}
		
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		if(is_object($itemortxt)){
			if(!$itemortxt){
				return false;	
			}
			if(!is_a($itemortxt,"mwmod_mw_placeholder_src_abs")){
				return false;	
			}
			return $this->add_item($itemortxt);
		}else{
			$item=new mwmod_mw_placeholder_src_item($cod,$itemortxt);
			return $this->add_item($item);
				
		}
		
	}
	function get_or_create_item($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		if($item=$this->get_item($cod)){
			return $item;
		}
		$item=new mwmod_mw_placeholder_src_item($cod,"");
		return $this->add_item($item);
		
		
		
	}
	function get_items(){
		return $this->_get_items();	
	}
	final function _get_items(){
		if($this->_items){
			if(sizeof($this->_items)){
				return $this->_items;	
			}
		}
	}
	final function get_item($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		return $this->_items[$cod]??null;
	}
	
	final function add_item($item){
		if(!$item){
			return false;	
		}
		if(!is_object($item)){
			return false;		
		}
		if(!is_a($item,"mwmod_mw_placeholder_src_abs")){
			return false;	
		}
		
		$cod=$item->cod;
		$this->_items[$cod]=$item;
		return $item;
	}
	
	

	final function __get_priv_cod(){
		return $this->cod; 	
	}
	
}
?>