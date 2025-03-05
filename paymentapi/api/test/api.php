<?php
class  mwmod_mw_paymentapi_api_test_api extends mwmod_mw_paymentapi_abs_api{
	function __construct($man){
		$this->init($man);
	}
	function debugTestApiClassesLoaded(){
		$obj = new mwsometestpaymentapi();
		echo $obj->hello();	
	}

}
?>