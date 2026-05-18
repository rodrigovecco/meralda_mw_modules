<?php
class mwmod_mw_service_whoami_check extends mwmod_mw_service_user_child {

	function __construct(){
		$this->initAsChild("check");
		$this->isfinal=true;
	}

	function doExecOk($path=false){
		$raw = $this->JsonRequestBodyData->get_data("permissions");

		if(!$raw){
			$this->outputJSON(array(
				"ok"    => false,
				"error" => "missing permissions",
			));
			return;
		}

		// Accept both a comma-separated string and an array.
		if(is_array($raw)){
			$codes = $raw;
		}else{
			$codes = explode(",", $raw."");
		}

		$result = array();
		foreach($codes as $code){
			$code = trim($code);
			if(!$code) continue;
			$result[$code] = (bool)$this->allow($code);
		}

		$this->outputJSON(array(
			"ok"          => true,
			"permissions" => $result,
		));
	}

}
?>
