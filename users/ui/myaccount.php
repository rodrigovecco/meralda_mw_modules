<?php
class mwmod_mw_users_ui_myaccount extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_def_title($this->lng_get_msg_txt("my_account","Mi cuenta"));
		$this->subinterface_def_code="data";
		
	}
	function load_all_subinterfases(){
		
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_myaccount_data("data",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_myaccount_pass("pass",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_myaccount_img("img",$this));
	}
	
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		
		$mnu = new mwmod_mw_mnu_mnu();
		
		//$item=$this->add_2_mnu($mnu);
		//$item->etq=$this->lng_get_msg_txt("information","Información");
		
		if($subs=$this->get_subinterfaces_by_code("data,img,pass",true)){
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		
		return $mnu;
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		
	}
	function is_allowed(){
		return $this->allow("editmydata");	
	}
	
}
?>