<?php

abstract class  mwmod_mw_service_user_root extends mwmod_mw_service_base{
	private $userMan;

	/**
	 * Enable pwdBaseToken authentication (JWT tied to user password).
	 * Full user permissions, no per-token scope restriction.
	 * @var bool
	 */
	public $authPwdBaseToken = false;

	/**
	 * Enable API token authentication (independent token with optional permission scope).
	 * allow() will intersect user permissions with the token's declared scope.
	 * @var bool
	 */
	public $authApiToken = false;

	function validateAllowedAsRoot(){
		$this->loginUserByToken();
		return $this->isAllowed();
	}

	/** Set the auth-failure HTTP status code. Alias of setAuthFailCode(). */
	function setAuthFailResponse($code){
		$this->authFailCode = (int) $code;
	}

	/** 401 Unauthorized */
	function authFailUnauthenticated(){
		$this->setAuthFailResponse(401);
	}

	/** 403 Forbidden */
	function authFailForbidden(){
		$this->setAuthFailResponse(403);
	}

	/** 404 Not Found — stealth against scanners/fail2ban. */
	function authFailNotFound(){
		$this->setAuthFailResponse(404);
	}

	function loginUserByToken(){
		if(!$token=$this->getRequestTokenBearer()){
			return false;
		}

		if(!$uman=$this->get_user_manager()){
			return false;
		}

		// API token auth: JWT with type=apitoken — single DB lookup, cross-validates
		// hash and permissions between JWT payload and DB (no password dependency).
		if($this->authApiToken && ($jwtMan=$uman->getJwtMan()) && ($apitokenMan=$uman->getApitokenMan())){
			if($tokenItem=$apitokenMan->findActiveByJwt($token)){
				if($user=$uman->get_user($tokenItem->getUserId())){
					return $uman->login_user_api_token_mode($user, $tokenItem);
				}
			}
		}

		// pwdBaseToken auth: JWT tied to user password (full permissions, no scope restriction).
		if($this->authPwdBaseToken && ($jwtMan=$uman->getJwtMan())){
			if($user=$jwtMan->validateAndRetrieveUser($token)){
				$result=$uman->login_user_service_mode($user);
				if($result){
					$uman->setPswBaseTokenSession();
				}
				return $result;
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