<?php
abstract class mwmod_mw_paymentapi_debugui_mod_mainabs extends mwmod_mw_paymentapi_debugui_abs{
	public $currentModule;
	function initFromModule($module,$cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($module->get_name());
		$this->setCurrentModule($module);
		
	}
	function setCurrentModule($mod){
		if(!$mod){
			return false;	
		}
		$this->currentModule=$mod;
		$this->set_url_param("mcod",$mod->cod);
		return $mod;
	}
	
	function getCurrentModule(){
		return 	$this->currentModule;
			
	}
	function do_exec_page_in(){
		if(!$m=$this->getCurrentModule()){
			"Invalid module";	
		}
		echo $m->get_name();
		
	}

}
?>