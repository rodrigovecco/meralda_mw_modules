<?php
class mwmod_mw_service_whoami_check extends mwmod_mw_service_user_child {

	function __construct(){
		$this->initAsChild("check");
		$this->isfinal=true;
	}

	function doExecOk($path=false){
		$perm=trim($path."");
		if(!$perm){
			$this->outputJSON(array(
				"ok"    => false,
				"error" => "missing permission code",
			));
			return;
		}
		$allowed=(bool)$this->allow($perm);
		$this->outputJSON(array(
			"ok"         => true,
			"permission" => $perm,
			"allowed"    => $allowed,
		));
	}

}
?>
