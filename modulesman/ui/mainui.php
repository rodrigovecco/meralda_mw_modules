<?php
class mwmod_mw_modulesman_ui_mainui extends mwmod_mw_modulesman_ui_abs{
	function __construct($cod,$maininterface){
		$this->initui($cod,$maininterface);
		$this->set_def_title($this->lng_common_get_msg_txt("modulos","Módulos"));
		$this->subinterface_def_code="info";
		
	}
	/*
	function before_exec(){
		///esto se ha hecho ya que sus subinterfaces requieren scripñts que antes se cargaban con todas
		$p=new mwmod_mw_html_manager_uipreparers_default($this);
		$p->preapare_ui();

		
		$this->add_req_js_scripts();	
		$this->add_req_css();
	}
	*/

	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		if($subs=$this->get_subinterfaces_by_code("info,update,classinfo,dirinfo,fileinfo",true)){
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		
		return $mnu;
	}
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	
	

	
	function add_mnu_items($mnu){
		$this->add_sub_interface_to_mnu_by_code($mnu,"info");
		$this->add_sub_interface_to_mnu_by_code($mnu,"classinfo");
		
		
	}
	
	function load_all_subinterfases(){
		
		$si=$this->add_new_subinterface(new mwmod_mw_modulesman_ui_info("info",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_modulesman_ui_classinfo("classinfo",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_modulesman_ui_dirinfo("dirinfo",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_modulesman_ui_fileinfo("fileinfo",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_modulesman_ui_update("update",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_modulesman_ui_explore("explore",$this));
		
	}

	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		

		
	}
}
?>