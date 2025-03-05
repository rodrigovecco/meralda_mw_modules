<?php
class mwmod_mw_mnu_items_special_empty extends mwmod_mw_mnu_items_cus{
	function __construct($cod,$etq,$parent,$url=false){
		$this->init($cod,$etq,$parent,$url);
	}
	function create_html_elem(){
		$this->html_elem=new mwmod_mw_html_cont_varcont();
	}
	
}

?>