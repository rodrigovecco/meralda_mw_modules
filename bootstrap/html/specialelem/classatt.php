<?php
class mwmod_mw_bootstrap_html_specialelem_classatt{
	private $elem;
	function __construct($elem){
		$this->set_elem($elem);
	}
	
	final function set_elem($elem){
		$this->elem=$elem;
	}
	final function __get_priv_elem(){
		return $this->elem;	
	}

	function get_as_att($cod){
		return "class='".$this->elem->get_class_name()."'";
	}
	
}

?>