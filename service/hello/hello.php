<?php
class  mwmod_mw_service_hello_hello extends mwmod_mw_service_test_test_abs{
	function __construct($baseurl=false){
		$this->initAsRoot($baseurl);
	}
	function doExecOk($path=false){
		$info=array(
			"msg"=>"Hello! Is site is powerd by Meralda",

			"class"=>get_class($this),

			"path"=>$path);
		

		$this->outputJSON($info);
		
	}
		
	function isAllowed(){
		return true;	
	}
	

}
?>