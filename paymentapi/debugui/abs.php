<?php
abstract class mwmod_mw_paymentapi_debugui_abs extends mwmod_mw_ui_sub_uiabs{
	private $paymentModulesMan;
	final function __get_priv_paymentModulesMan(){
		if(!isset($this->paymentModulesMan)){
			if($m=$this->loadPaymentModulesMan()){
				$this->paymentModulesMan=$m;
			}else{
				return false;	
			}
			//=$this->loadPaymentModulesMan();	
		}
		
		return $this->paymentModulesMan; 	
	}
	function getPaymentModulesMan(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->getPaymentModulesMan();
		}
			
	}
	function loadPaymentModulesMan(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->getPaymentModulesMan();
		}
	}
	function getCurrentModule(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->getCurrentModule();
		}
			
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
	}
	
	function allowcreatesubinterfacechildbycode(){
		return true;	
	}
	
		
}
?>