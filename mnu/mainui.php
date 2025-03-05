<?php

class mwmod_mw_mnu_mainui extends mwmod_mw_mnu_mnu{
	private $maininterface;
	function __construct($maininterface,$cod="mnu"){
		$this->set_cod($cod);
		$this->set_main_interface($maininterface);
	}
	final function set_main_interface($maininterface){
		$ap=$maininterface->mainap;
		$this->set_mainap($ap);	
		$this->maininterface=$maininterface;
	}
	final function __get_priv_maininterface(){
		return $this->maininterface; 	
	}

}

?>