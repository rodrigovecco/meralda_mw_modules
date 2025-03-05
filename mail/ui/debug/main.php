<?php
class mwmod_mw_mail_ui_debug_main extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("More mail tests");
		$this->sucods="defcfg,cuscfg";//su1,su2
		
	}
	function _do_create_subinterface_child_defcfg(){
		$si=new mwmod_mw_mail_ui_debug_sendmail("defcfg",$this);
		return $si;
	}
	function _do_create_subinterface_child_cuscfg(){
		$si=new mwmod_mw_mail_ui_debug_sendmailcus("cuscfg",$this);
		return $si;
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	function allowcreatesubinterfacechildbycode(){
		return true;	
	}

	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		
		
		return $mnu;
	}

	
}
?>