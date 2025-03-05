<?php
abstract class mwmod_mw_paymentapi_debugui_mod_subabs extends mwmod_mw_paymentapi_debugui_abs{
	function do_exec_page_in(){
		if(!$m=$this->getCurrentModule()){
			"Invalid module";	
		}
		echo $m->get_name();
		
	}

}
?>