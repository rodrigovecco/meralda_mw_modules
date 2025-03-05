<?php
class mwmod_mw_data_tree_subitem extends mw_baseobj{
	private $cod;
	private $mainitem;
	function __construct($cod,$mainitem){
		$this->init($cod,$mainitem);	
	}
	function isNew(){
		if($this->is_data_defined()){
			return false;	
		}else{
			return true;	
		}
	}
	function is_data_defined($key=false){
		if(!$realkey=$this->get_real_key($key)){
			return false;
		}
		return $this->mainitem->is_data_defined($realkey);
		
		
	}
	function get_sub_data_debug_info($key=false){
		$r=array();
		$r["key"]=$key;
		$r["data"]=$this->get_data($key);
		$r["defined"]=$this->is_data_defined($key);
		return $r;
		
		
		
	}
	function get_debug_info(){
		$r=array(
			"type"=>"subitem",
			"cod"=>$this->cod,
			"mainitem"=>$this->mainitem->get_debug_info()
		);
		return $r;	
	}

	
	function get_real_key($key=false){
		if($key===false){
			return $this->cod;	
		}
		if(!$key){
			return false;	
		}
		if(!is_string($key)){
			if(!is_numeric($key)){
				return false;
			}
		}
		return $this->cod.".".$key;	
	}
	function get_sub_item($key){
		if(!$key){
			return false;	
		}
		if(!is_string($key)){
			if(!is_numeric($key)){
				return false;
			}
		}
		if(!$realkey=$this->get_real_key($key)){
			return false;
		}
		return $this->mainitem->get_sub_item($realkey);
	
	}

	function set_data($data,$key=false){
		if(!$key){
			if(!is_array($data)){
				$data=array();	
			}
		}
		
		if(!$realkey=$this->get_real_key($key)){
			return false;
		}
		return $this->mainitem->set_data($data,$realkey);
	}
	function get_data_as_list($key=false,$falseonfail=false){
		if(!$realkey=$this->get_real_key($key)){
			if(!$falseonfail){
				$r=array();
				return $r;	
			}
			return false;
		}
		return $this->mainitem->get_data_as_list($realkey,$falseonfail);	
	}
	function get_data($key=false){
		if(!$realkey=$this->get_real_key($key)){
			return false;
		}
		return $this->mainitem->get_data($realkey);
		
			
	}
	function set_data_and_save($data,$key=false){
		$this->set_data($data,$key);
		$this->save();
	}
	function save(){
		//$this->set_data($data,$key);
		$this->mainitem->save();
	}

	final function init($cod,$mainitem){
		$this->mainitem=$mainitem;	
		$this->cod=$cod;	
	}
	final function __get_priv_mainitem(){
		return $this->mainitem;	
	}
	final function __get_priv_cod(){
		return $this->cod;	
	}
}

?>