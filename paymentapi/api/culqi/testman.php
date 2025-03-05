<?php
class  mwmod_mw_paymentapi_api_culqi_testman extends mwmod_mw_paymentapi_api_culqi_man{
	function __construct(){
		$this->init("culqitest");
		$this->testMode=true;
		$this->name="Culqi pruebas";
	}

}
?>