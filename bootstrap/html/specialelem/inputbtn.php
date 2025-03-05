<?php
class mwmod_mw_bootstrap_html_specialelem_inputbtn extends mwmod_mw_bootstrap_html_specialelem_btn{
	function __construct($display_mode="primary"){
		$this->init_bt_special_elem("btn","input",$display_mode);
		$this->avaible_display_modes="default,primary,success,info,warning,danger,link";
		$this->set_att("type","button");
	
	}
	
	
}

?>