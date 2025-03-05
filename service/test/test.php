<?php
class  mwmod_mw_service_test_test extends mwmod_mw_service_test_test_abs{
	function __construct($baseurl=false){
		$this->initAsRoot($baseurl);
	}
	function doExecOk($path=false){
		$info=array("class"=>get_class($this),"path"=>$path);
		$this->outputJSON($info);
		
	}
	function createChildByMethod_orders($cod){
		return new mwmod_mw_service_test_test_orders();
	}
	function createChildByMethod_order($cod){
		return new mwmod_mw_service_test_test_order();
	}
	

	
	function isAllowed(){
		return true;	
	}
	

}
?>