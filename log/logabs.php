<?php
abstract class mwmod_mw_log_logabs extends mw_apsubbaseobj{
	private $cod;
	private $_path_man;
	final function init($cod){
		$this->cod=$cod;	
	}
	
	function do_log(){
			
	}
	
	function do_log_on_shutdown(){
			
	}
	function create_path_man(){
		if(!$cod=$this->check_str_key_alnum_underscore($this->cod)){
			return false;
		}
		return $this->mainap->get_sub_path_man("logs/".$cod,"system");
		
			
	}
	
	final function get_path_man(){
		if(isset($this->_path_man)){
			return 	$this->_path_man;
		}
		$this->_path_man=false;
		if($pm=$this->create_path_man()){
			$this->_path_man=$pm;	
		}
		return 	$this->_path_man;
	}
	function __call($a,$b){
		return false;	
	}
	
}
?>