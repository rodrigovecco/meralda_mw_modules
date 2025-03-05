<?php
class mwmod_mw_ui_debug_tests_pdf extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		
		$this->set_def_title("PDF");
		
		
	}
	
	function execfrommain_getcmd_dl_test($params=array(),$filename=false){
		echo "Hola";
		
		
	}


	function do_exec_page_in(){
		$container= new mwmod_mw_html_elem();
		$url=$this->get_exec_cmd_dl_url("test",[],"test.pdf");
		$e=$container->add_cont_elem("<a href='$url' target='_blank'>Descargar</a>");
		
		
		
		
		
		echo $container;
		
		
		


		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	
}
?>