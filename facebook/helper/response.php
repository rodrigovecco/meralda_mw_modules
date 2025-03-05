<?php
class  mwmod_mw_facebook_helper_response extends mw_apsubbaseobj {
	public $fb;
	public $fbHelper;
	public $response;
	public $errors;
	public $method="GET";
	public $endPoint="me";
	function __construct($response=false,$fbHelper=false){
		$this->init($response,$fbHelper);
	}
	function getParams(){
		return array("fields"=>"id,name");	
	}
	function getEndPoint(){
		return $this->endPoint;	
	}
	function getRequestMethod(){
		return $this->method;	
	}
	final function init($response=false,$fbHelper=false){
		$this->setResponse($response);
		$this->setFBHelper($fbHelper);
			
	}
	function proc($fbHelper=false){
		if(!$fbHelper){
			$fbHelper=$this->fbHelper;	
		}
		if($fbHelper){
			$m=$this->getRequestMethod();
			$ep=$this->getEndPoint();
			$p=$this->getParams();
			
			
			return $fbHelper->sendRequestProccess($this,$m,$ep,$p);	
			//return $fbHelper->sendRequestProccess($this,"GET","me",array("fields"=>"id,name"));	
		}
	}
	function getResponseData($cod=false){
		if(!$d=$this->getDecodedBody()){
			return;	
		}
		return mw_array_get_sub_key($d,$cod);
	}
	function getDecodedBody(){
		if($this->response){
			return $this->response->getDecodedBody();	
		}
	}
	function isOK(){
		if($this->response){
			return true;	
		}
		return false;
	}
	function setResponse($response=false){
		if($response){
			$this->response=$response;
		}
			
	}
	
	function setFBHelper ($fbHelper=false){
		if($fbHelper){
			$this->fbHelper=$fbHelper;
			$this->fb=$fbHelper->fb;	
		}
	}
	function reportError($e){
		if(!$e){
			return;	
		}
		if(!$this->errors){
			$this->errors=array();	
		}
		$this->errors[]=$e;
		
	}

}
?>