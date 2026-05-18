<?php

abstract class  mwmod_mw_service_user_root extends mwmod_mw_service_base{
	private $userMan;

	/**
	 * Enable pwdBaseToken authentication (JWT tied to user password).
	 * Full user permissions, no per-token scope restriction.
	 * @var bool
	 */
	protected $authPwdBaseToken = false;

	/**
	 * Enable API token authentication (independent token with optional permission scope).
	 * allow() will intersect user permissions with the token's declared scope.
	 * @var bool
	 */
	protected $authApiToken = false;






	function validateAllowedAsRoot(){
		$this->loginUserByToken();
		return $this->isAllowed();
	}
	function loginUserByToken(){
		if(!$tokenHeadder=$this->getRequestTokenBearer()){
			return false;
		}

		if(!$uman=$this->get_user_manager()){
			return false;
		}

		// pwdBaseToken auth: JWT tied to user password (full permissions, no scope restriction)
		if($this->authPwdBaseToken && ($jwtMan=$uman->getJwtMan())){
			if($user=$jwtMan->validateAndRetrieveUser($tokenHeadder)){
				$result=$uman->login_user_service_mode($user);
				if($result){
					$uman->setPswBaseTokenSession();
				}
				return $result;
			}
		}

		// API token auth: independent token with optional per-token permission scope
		if($this->authApiToken && ($apitokenMan=$uman->getApitokenMan())){
			if($tokenItem=$apitokenMan->findActiveByRawToken($tokenHeadder)){
				$user=$uman->get_user($tokenItem->getUserId());
				if($user && $user->is_active()){
					$uman->set_currentuser_obj($user);
					$uman->setCurrentApiToken($tokenItem);
					return $user;
				}
			}
		}

		return false;
	}

	/** @return mwmod_mw_users_user  */
	function get_current_user(){
		return $this->__get_priv_userMan()->get_current_user();
	}

	/** @return mwmod_mw_users_usersman  */
	function get_user_manager(){
		return $this->__get_priv_userMan();
	}
	function loadUserMan(){
		return $this->mainap->get_user_manager();
	}
	final function __get_priv_userMan(){
		if(!isset($this->userMan)){
			$this->userMan=$this->loadUserMan();
		}
		return $this->userMan;
	}
	
	function allow($action,$params=false){
		if($man=$this->get_user_manager()){
			return $man->allow($action,$params);	
		}
	
	}
	

}
?>