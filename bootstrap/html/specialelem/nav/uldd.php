<?php
class mwmod_mw_bootstrap_html_specialelem_nav_uldd extends mwmod_mw_bootstrap_html_specialelem_nav_ul{
	function __construct(){
		$this->set_tagname("ul");
		$this->add_class("dropdown-menu");
		$this->only_visible_when_has_cont=true;
	}
}

?>