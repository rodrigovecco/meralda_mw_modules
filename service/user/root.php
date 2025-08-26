<?php

abstract class  mwmod_mw_service_user_root extends mwmod_mw_service_base{
	private $userMan;






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
		if($uman->jwtMan){
			if($user=$uman->jwtMan->validateAndRetrieveUser($tokenHeadder)){
				return $uman->login_user_service_mode($user);
				
			}
		}
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