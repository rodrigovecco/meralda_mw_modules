<?php
class  mwmod_mw_facebook_helper_response_tokeninfo extends mwmod_mw_facebook_helper_response {
	public $inputtoken;
	function __construct($inputtoken=false,$fbHelper=false){
		$this->init(false,$fbHelper);
		$this->inputtoken=$inputtoken;
		$this->endPoint="debug_token";
	}
	function getParams(){
		if(!$token=$this->getInputtoken()){
			return false;	
		}
		return array("input_token"=>$token);	
	}
	
	
	function getInputtoken(){
		if($this->inputtoken){
			return $this->inputtoken;	
		}
		if($this->fbHelper){
			return 	$this->getDefaultAccesstoken();
		}
	}
	
	function tokenProfileID(){
		return $this->getResponseData("data.profile_id");	
	}
	
	function tokenIsValid(){
		if($this->getResponseData("data.is_valid")){
			return true;
		}
		return false;
	}
	function tokenExpireDate(){
		if(!$t=$this->getResponseData("data.expires_at")){
			return "";
		}
		return date("Y-m-d H:i:s",$t);
	}
	
	function tokenExpires(){
		if($this->getResponseData("data.expires_at")){
			return true;
		}
		return false;
	}

}
?>