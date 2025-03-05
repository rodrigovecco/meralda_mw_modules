<?php
class mwmod_mw_paymentapi_debugui_main extends mwmod_mw_paymentapi_debugui_abs{
	public $currentModule;
	
	
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Payment");
		
	}
	function before_exec(){
		$this->add_req_js_scripts();	
		$this->add_req_css();
		$this->getOrSetCurrentModuleByRequest();
	}
	function getOrSetCurrentModuleByRequest(){
		if($m=$this->getCurrentModule()){
			return $m;	
		}
		return $this->setCurrentModuleByCod($_REQUEST["mcod"]);
	}
	function setCurrentModuleByCod($cod){
		if(!$cod){
			return false;	
		}
		if(!$this->paymentModulesMan){
			return false;	
		}
		return $this->setCurrentModule($this->paymentModulesMan->get_item($cod));
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

	
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		$this->add_2_sub_interface_mnu($mnu);
		if($subs=$this->get_subinterfaces_by_code("debug",true)){
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);		
			}
		}

		
		return $mnu;
	}
	function _do_create_subinterface_child_debug($cod){
		$ui=new mwmod_mw_paymentapi_debugui_debugdata($cod,$this);
		return $ui;	
	}
	function _do_create_subinterface_child_mod($cod){
		if($mod=$this->getOrSetCurrentModuleByRequest()){
			return $mod->new_debug_ui($cod,$this);	
		}
		
		//$ui=new mwmod_mw_paymentapi_debugui_debugdata($cod,$this);
		//return $ui;	
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		if(!$items=$this->paymentModulesMan->get_items()){
			echo "No modules man";
			return false;
		}
		echo "<ul>";
		foreach($items as $id=>$item){
			$url=$this->get_url_sub_interface_by_dot_cod("mod",array("mcod"=>$id));
			
			
			
			echo "<li><a href='$url'>".$item->get_name()."</a> ".$item->get_status_info_str()."</li>";	
		}
		echo "</ul>";
		
		//mw_array2list_echo($man->get_debug_data());
		
		
		
	}
	function getPaymentModulesMan(){
		return 	$this->__get_priv_paymentModulesMan();
			
	}
	
	function loadPaymentModulesMan(){
		if($man=$this->mainap->get_submanager("paymentsmodules")){
			if(is_a($man,"mwmod_mw_paymentapi_man_abs")){
				return $man;	
			}
		}
		return new mwmod_mw_paymentapi_man_apitestalllmodulesman();
	}
	function is_allowed(){
		return $this->allow("debug");	
	}

}
?>