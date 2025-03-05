<?php
class mwmod_mw_paymentapi_debugui_mod_testapi extends mwmod_mw_paymentapi_debugui_mod_subabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Probar api");
		
	}

	
	function do_exec_page_in(){
		if(!$m=$this->getCurrentModule()){
			"Invalid module";	
			return false;
		}
		if(!$api=$m->newApi()){
			"Invalid api";	
			return false;
				
		}
		
		$html=new mwmod_mw_html_elem();
		
		$e=$html->add_cont_elem();
		$e->add_cont_elem(get_class($api));
		$api->debugTestApiClassesLoaded();
		
		echo $html;
	}

}
?>