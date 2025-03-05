<?php

abstract class mwmod_mw_data_cfg_src_abs extends mw_apsubbaseobj{
	private $cod;
	private $_values;
	function load_values(){
		//extender	
	}
	function get_debug_data(){
		$r=array(
			"cod"=>$this->cod,
			"values"=>$this->get_values(),
		);
		$this->get_debug_data_add($r);
		return $r;	
	}
	function get_debug_data_add(&$r){
		
	}
	
	final function get_values(){
		if(isset($this->_values)){
			return $this->_values;	
		}
		$this->_values=array();
		if($v=$this->load_values()){
			if(is_array($v)){
				foreach($v as $cod=>$d){
					if($cod=trim($cod)){
						$this->_values[$cod]=$d;	
					}
				}
			}
		}
		return 	$this->_values;
	}
	
	final function set_cod($cod){
		$this->cod=$cod;
	}
	final function __get_priv_cod(){
		return $this->cod;	
	}
	

}


?>