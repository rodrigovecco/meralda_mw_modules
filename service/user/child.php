<?php
abstract class  mwmod_mw_service_user_child extends mwmod_mw_service_base{


	/** @return mwmod_mw_users_user  */
	function get_current_user(){
		if($this->parentService){
			return $this->parentService->get_current_user();
		}

		return null;
	}
	function get_user_manager(){
		if($this->parentService){
			return $this->parentService->get_user_manager();
		}
		return null;
	}

	function allow($action,$params=false){
		if($this->parentService){
			return $this->parentService->allow($action,$params);
		}
		return false;
	}
	

	

}
?>