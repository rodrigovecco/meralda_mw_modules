<?php
class  mwmod_mw_paymentapi_api_niubiz_testman extends mwmod_mw_paymentapi_api_niubiz_man{
	function __construct(){
		$this->init("niubiztest");
		$this->testMode=true;
		$this->name="Niubiz pruebas";
	}

}
?>