<?php
abstract class  mwmod_mw_service_base extends mwmod_mw_service_abs{
	function doExecOk($path=false){
			
	}
	function execNotAllowed($path=false){
		http_response_code($this->authFailCode);
		if(!$this->authFailSilent && $this->errorResponseData){
			$this->outputJSON($this->errorResponseData);
		}
	}
	function isAllowed(){
		return $this->isAllowedByParent();
		
	}
	
	function validateAllowedAsChild(){
		//acá podrá validar usuarios si root no lo ha hecho
		return $this->isAllowed();
	}
	
	function validateAllowedAsRoot(){
		//acá podrá validar usuarios, etc
		return $this->isAllowed();
	}



	function createChild($cod){
		if(is_numeric($cod)){
			return $this->createChildByNum($cod+0);	
		}else{
			return $this->createChildByMethod($cod);	
		}
		return false;
	}
	function createChildByNum($num){
		return false;
	}
	function createChildByMethod($cod){
		if(!$cod=$this->check_child_cod($cod)){
			return false;	
		}
		$method="createChildByMethod_$cod";
		if(method_exists($this,$method)){
			return $this->$method($cod);	
		}
		return false;
	}
	

	function childrenCreationEnabled(){
		if(!$this->isFinal()){
			return true;	
		}
		return false;	
	}
	function allowCreateChild($cod){
		if(!$this->childrenCreationEnabled()){
			return false;
		}
		return $this->check_child_cod($cod);
	}
	
}
?>