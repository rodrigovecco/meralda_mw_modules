<?php
abstract class  mwmod_mw_manager_itemtype extends mw_apsubbaseobj{
	public $name;
	private $cod;
	private $man;
	
	function create_item($tblitem){
		$item=new mwmod_mw_manager_itemwithtype($tblitem,$this);
		return $item;
	
	}
	
	final function init($cod,$man){
		$this->cod=$cod;
		$this->man=$man;
		$this->set_mainap();	
		
	}
	function get_name(){
		if($this->name){
			return $this->name;
		}
		return $this->cod;	
	}
	function get_id(){
		return $this->cod;	
	}
	final function __get_priv_cod(){
		return $this->cod; 	
	}
	final function __get_priv_man(){
		return $this->man; 	
	}
	function __call($a,$b){
		return false;	
	}

}
?>