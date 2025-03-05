<?php

class mwmod_mw_html_cont_varcont extends mwmod_mw_html_elem{
	
	function __construct($cont=false){
		//$this->nlonclose=false;
		//$this->nloncont=false;

		if($cont!==false){
			$this->add_cont($cont);
		}
	}
	function get_html_open(){
		return "";
	}
	function get_html_close(){
		return "";
		
	}
	
}

?>