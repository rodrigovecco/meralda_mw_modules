<?php
class  mwmod_mw_service_test_test_order extends mwmod_mw_service_test_test_abs{
	function __construct(){
		$this->initAsChild("orders");
	}
	function createChildByNum($num){
		if(!$id=abs(round($num+0))){
			return false;	
		}
		return new mwmod_mw_service_test_test_order_info($id);
	}

	function doExecOk($path=false){
		echo "Invalid order ID";
	}


	
}
?>