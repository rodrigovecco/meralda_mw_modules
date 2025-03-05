<?php



class mwmod_mw_demo_ui_extmods_geophp extends mwmod_mw_demo_ui_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title("GEOphp");
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		$helper= new mwmod_mw_extmods_geophp();
		if($helper->moduleInstalled()){
			echo "<p>Módulo instalado</p>";
		}else{
			echo "<p>Módulo NO instalado</p>";
			return;
		}
	

	}
	

	
}
?>