<?php
class mwmod_mw_ui_sub_uinotallowed extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		
		$this->set_def_title($this->get_msg("No permitido"));
	}
	function do_exec_no_sub_interface(){
		//echo "ttttt";	
	}
	function do_exec_page_in(){
		echo "<p>".$this->get_msg("No permitido.")."</p>";
	}

	function is_allowed(){
		return true;
	}
	
}
?>