<?php

class mwmod_mw_jwt_token extends mw_apsubbaseobj{
	private $payload;
	private $tokenMan;

	public $validated=false;
	
	function __construct($tokenMan,$payloadData){
		$this->init($tokenMan,$payloadData);
		
	}
	function permissionCheck($cod){
		if(!$this->validated){
			return false;
		}

		if($this->_permissionCheck($cod)){
			return true;
		}
		if($cod!="all"){
			
			if($this->_permissionCheck("all")){
				
				return true;
			}
		}
		return false;

	}
	private function _permissionCheck($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;
		}
		if($this->payload->get_data("permissions.".$cod)){
			return true;
		}
		//override in subclasses
		return false;
	}
	function isValid(){
		return $this->validated;
	}	
	function init($tokenMan,$payloadData){
		$this->setTokenMan($tokenMan);
		$this->setPayload($payloadData);
	}
	final function setTokenMan($tokenMan){
		$this->tokenMan=$tokenMan;
	}
	final function setPayload($payloadData){
		$this->payload=new mwmod_mw_data_var_man();
		$this->payload->set_data($payloadData);
	}

	final function __get_priv_payload(){
		return $this->payload;
	}
	final function __get_priv_tokenMan(){
		return $this->tokenMan;
	}
	function getPayloadData($cod=null){
		return $this->payload->get_data($cod);
	}



	
	


}
?>