<?php
class mwmod_mw_bootstrap_html_specialelem_dismissiblealert extends mwmod_mw_bootstrap_html_specialelem_alert{
	function __construct($cont=false,$display_mode="info"){
		$this->init_bt_special_elem("alert","div",$display_mode);
		$this->avaible_display_modes="success,info,warning,danger";
		if($cont!==false){
			$this->add_cont($cont);
		}
		$this->set_att("role","alert");
		$this->dismissible=true;
	
	}
	
}

?>