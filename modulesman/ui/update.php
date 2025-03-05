<?php
class mwmod_mw_modulesman_ui_update extends mwmod_mw_modulesman_ui_abs{
	function __construct($cod,$maininterface){
		$this->initui($cod,$maininterface);
		$this->set_def_title("Update Info");
		
	}
	function do_exec_page_in(){
		$this->modulesman->update_csv();
		echo "Done";
		//$info=$this->modulesman->get_dirs_mans_debug_data();
		//mw_array2list_echo($info);
		
	}
	function is_responsable_for_sub_interface_mnu(){
		return false;	
	}

	function do_exec_no_sub_interface(){
	}
	
}
?>