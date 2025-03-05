<?php
class mwmod_mw_paymentapi_debugui_mod_main extends mwmod_mw_paymentapi_debugui_mod_mainabs{
	function __construct($module,$cod,$parent){
		$this->initFromModule($module,$cod,$parent);
	}
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function _do_create_subinterface_child_testapi($cod){
		$ui=new mwmod_mw_paymentapi_debugui_mod_testapi($cod,$this);
		return $ui;	
	}
	function _do_create_subinterface_child_createfiles($cod){
		$ui=new mwmod_mw_paymentapi_debugui_mod_createfiles($cod,$this);
		return $ui;	
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		$this->add_2_sub_interface_mnu($mnu);
		if($subs=$this->get_subinterfaces_by_code("createfiles,testapi",true)){
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);		
			}
		}

		
		return $mnu;
	}
	/*
	function do_exec_page_in(){
		
		
	}
	*/

}
?>