<?php
class mwmod_mw_facebook_sess_user extends mwmod_mw_facebook_sess_obj {
	
	
	function __construct($sessKey){
		$this->init($sessKey);
		$this->protectedKeys="token";
	}
	function getExtendedInfo(){
		$d=$this->getInfo();
		if($this->tokenIsValid()){
			$d["ok"]=true;	
		}else{
			$d["ok"]=false;	
		}
		return $d;
	}
	function tokenIsValid(){
		if(!$t=$this->gettoken()){
			return false;	
		}
		//falta ver si hay fecha de caducidad
		return true;
	}
	function setNewtoken($val){
		$this->unsetSessData();
		return $this->settoken($val);
	}
	function setUserId($val){
		return $this->setSessData($val,"id");
	}
	function setName($val){
		return $this->setSessData($val,"name");
	}
	function gettoken(){
		return $this->getSessData("token");
	}
	function settoken($val){
		return $this->setSessData($val,"token");
	}

}
?>