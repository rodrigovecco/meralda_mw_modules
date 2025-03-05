<?php
class mwmod_mw_ui_system_cronjobs extends mwmod_mw_ui_system_abs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_lngmsgsmancod("system");
		$this->set_def_title("Cronjobs");
		$this->subinterface_def_code="log";
		
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		
		//$this->add_2_mnu($mnu);
		if($subs=$this->get_subinterfaces_by_code("log,info",true)){
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		return $mnu;
	}
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	
	function load_all_subinterfases(){
		$si=$this->add_new_subinterface(new mwmod_mw_ui_system_cronjobs_logs("log",$this));
		//$si=$this->add_new_subinterface(new mwmod_mw_ui_system_cronjobs_info("info",$this));
	}

	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		echo "";
		
	}
	
}
?>