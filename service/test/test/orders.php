<?php
class  mwmod_mw_service_test_test_orders extends mwmod_mw_service_test_test_abs{
	function __construct(){
		$this->initAsChild("orders");
		$this->isfinal=true;
	}
	function doExecOk($path=false){
		$info=array(
			"class"=>get_class($this),
			"path"=>$path,
		);
		$this->outputJSON($info);
	}


	
}
?>