<?php
class mwmod_mw_bootstrap_html_specialelem_panel extends mwmod_mw_bootstrap_html_specialelem_elemabs{
	function __construct($display_mode="default"){
		$this->init_bt_special_elem("card","div",$display_mode);
		$this->avaible_display_modes="default,primary,success,info,warning,danger,green,yellow,red";
	}
}

?>