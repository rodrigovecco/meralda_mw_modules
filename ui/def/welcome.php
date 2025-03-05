<?php
class mwmod_mw_ui_def_welcome extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_def_title($this->lng_common_get_msg_txt("home","Inicio"));
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		echo "<div calss='card-body'>";
		echo $this->lng_common_get_msg_txt("welcome","Bienvenido");
		echo "</div>";
		

		
	}
	
	function is_allowed(){
		return $this->allow("admin");	
	}
	
	
}
?>