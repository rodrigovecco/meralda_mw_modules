<?php
class mwmod_mw_bootstrap_html_specialelem_nav extends mwmod_mw_bootstrap_html_specialelem_elemabs{
	function __construct($display_mode="default"){
		$this->init_bt_special_elem("navbar","nav",$display_mode);
		$this->avaible_display_modes="default,inverse";
	}
}

?>