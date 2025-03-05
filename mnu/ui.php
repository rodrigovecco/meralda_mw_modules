<?php
class mwmod_mw_mnu_ui extends mwmod_mw_mnu_mainui{
	private $subinterface;	

	function __construct($subinterface,$cod="mnu"){
		$this->set_cod($cod);
		$this->set_sub_interface($subinterface);
	}
	final function set_sub_interface($subinterface){
		$main=$subinterface->maininterface;
		$this->set_main_interface($main);
		$this->subinterface=$subinterface;
	}
	final function __get_priv_subinterface(){
		return $this->subinterface; 	
	}

}


?>