<?php
abstract class  mwmod_mw_paymentapi_abs_api extends mw_apsubbaseobj{
	private $man;
	final function init($man){
		$this->man=$man;
	}
	function debugTestApiClassesLoaded(){
			
	}
	function get_cod(){
		return $this->man->cod;	
	}
	final function __get_priv_man(){
		return $this->man; 	
	}

}
?>