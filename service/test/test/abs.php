<?php
abstract class  mwmod_mw_service_test_test_abs extends mwmod_mw_service_base{
	function execNotAllowed($path=false){
		$this->outputJSON(array(
			"error"=>array(
				"msg"=>"not allowed",
				"path"=>$path,
			)
		));	
	}

	
}
?>