<?php
//por ahora sólo maneja archivos ini
class mwmod_mw_data_cfg_man extends mw_apsubbaseobj{
	private $_srcs=array();
	private $_items;
	function __construct($srcs=false,$ap=false){
		$this->init($srcs,$ap);
	}
	function get_value($cod,$def=false){
		if($item=$this->get_item($cod)){
			return $item->get_value($def);
		}
		return $def;
	}
	function get_value_boolean($cod,$def=false){
		if($item=$this->get_item($cod)){
			return $item->get_boolean();
		}
		return $def;	
	}
	function is_on_list($cod,$datacod){
		if($item=$this->get_item($cod)){
			return $item->is_on_list($datacod);
		}
		return false;
	}
	
	function get_value_as_list_no_blank($cod){
		if($item=$this->get_item($cod)){
			return $item->get_value_as_list_no_blank();
		}
		return false;
	}
	
	function get_debug_data(){
		$r=array(
			"class"=>get_class($this),
		);
		if($items=$this->get_srcs()){
			$rr=array();
			foreach($items as $cod=>$item){
				$rr[$cod]=$item->get_debug_data();
			}
			$r["srcs"]=$rr;
		}
		if($items=$this->get_items()){
			$rr=array();
			foreach($items as $cod=>$item){
				$rr[$cod]=$item->get_debug_data();
			}
			$r["items"]=$rr;
		}
		return $r;	
	}
	
	final function _get_or_create_item($cod){
		if(!$cod){
			return false;
		}
		if(!isset($this->_items)){
			return false;	
		}
		if(isset($this->_items[$cod])and($this->_items[$cod])){
			return $this->_items[$cod];
		}
		$item=new mwmod_mw_data_cfg_item($cod,$this);
		$this->_items[$cod]=$item;
		return $item;
		
			
	}
	final function get_item($cod){
		if(!$cod){
			return false;
		}
		$this->init_items();
		return $this->_items[$cod]??null;
	}
	final function init_items(){
		if(isset($this->_items)){
			return;	
		}
		$this->_items=array();
		if(!$srcs=$this->get_srcs()){
			return false;	
		}
		foreach($srcs as $src_cod=>$src){
			if($values=$src->get_values()){
				foreach($values as $cod=>$v){
					if($item=$this->_get_or_create_item($cod)){
						$item->add_value($v,$src_cod,$src);	
					}
				}
			}
		}
		
	}
	final function get_items(){
		$this->init_items();
		return $this->_items;
	}
	
	final function get_srcs(){
		return $this->_srcs;
	}
	final function get_src($cod){
		if(!$cod){
			return false;	
		}
		return $this->_srcs[$cod];
	}
	final function add_src($srcs){
		if(!$srcs){
			return false;	
		}
		$r=array();
		if(is_array($srcs)){
			foreach($srcs as $src){
				if($this->_add_src($src)){
					$r[]=$src;	
				}
			}
		}else{
			if($src=$this->_add_src($srcs)){
				$r[]=$src;	
			}
		}
		if(sizeof($r)){
			return $r;	
		}
	}
	final function _add_src($src){
		if(!$src){
			return false;	
		}
		if(!is_object($src)){
			return false;	
		}
		if(!is_a($src,"mwmod_mw_data_cfg_src_abs")){
			return false;	
		}
		if($this->is_initialized()){
			return false;	
		}
		if(!$cod=$src->cod){
			return false;	
		}
		$this->_srcs[$cod]=$src;
		return $src;
		
		
	}
	final function is_initialized(){
		return isset($this->_items);	
	}
	final function init($srcs=false,$ap=false){
		$this->set_mainap($ap);	
		if($srcs){
			$this->add_src($srcs);	
		}
	}
	

}


?>