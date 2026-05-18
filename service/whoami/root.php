<?php
class mwmod_mw_service_whoami_root extends mwmod_mw_service_user_root {

	/**
	 * Accepts pwdBaseToken authentication (JWT tied to user password).
	 * Set $authApiToken = true as well to also accept independent API tokens.
	 */
	protected $authPwdBaseToken = true;

	function __construct($baseurl=false){
		$this->initAsRoot($baseurl);
	}

	function isAllowed(){
		return (bool)$this->get_current_user();
	}

	function doExecOk($path=false){
		$user=$this->get_current_user();
		$uman=$this->get_user_manager();

		$authType="unknown";
		if($uman){
			if($uman->isPswBaseTokenSession())    $authType="pwdBaseToken";
			elseif($uman->isPasswordSession())    $authType="password";
			elseif($uman->isTokenBasedSession())  $authType="apiToken";
		}

		$this->outputJSON(array(
			"ok"   => true,
			"user" => array(
				"id"       => $user->get_id(),
				"username" => $user->get_idname(),
				"name"     => $user->get_real_name(),
			),
			"auth" => $authType,
		));
	}

	function createChildByMethod_check($cod){
		return new mwmod_mw_service_whoami_check();
	}

}
?>
