<?php
class  mwmod_mw_service_test_test_order_export extends mwmod_mw_service_test_test_abs{
	function __construct(){
		$this->initAsChild("export");
		$this->isfinal=true;
	}

	function doExecOk($path=false){
		$info=array("exported"=>true);
		$this->outputJSON($info);

		
	}


	
}
?>