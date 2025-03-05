<?php
abstract class mwmod_mw_ui_debug_app_sub extends mwmod_mw_ui_sub_uiabs{
	public $sucods;
	function do_exec_page_in(){
		if(!$this->is_allowed()){
			return false;	
		}
		echo "Pruebas de la aplicación";
		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	
	function is_responsable_for_sub_interface_mnu(){
		return false;	
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