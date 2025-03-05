<?php
class  mwmod_mw_paymentapi_api_test_man extends mwmod_mw_paymentapi_abs_man{
	function __construct(){
		$this->init("test");
		$this->testMode=true;
		$this->name="Pruebas";
	}
	function doLoadApiClasses(){
		$file=dirname(__FILE__)."/apiclases/load.php";
		//echo $file;
		if(file_exists($file)){
			require_once($file);
		}
		//__FILE__;
		
	}
	function createNewApi(){
		return new mwmod_mw_paymentapi_api_test_api($this);
	}
	function checkForEnable(){
		return true;	
	}


}
?>