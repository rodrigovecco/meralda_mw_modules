<?php
class mwmod_mw_ui_main_def extends mwmod_mw_ui_main_uimainabsajax{
	function __construct($ap,$baseurl="/admin/"){
		$this->set_mainap($ap);	
		$this->url_base_path=$baseurl;
		//$this->subinterface_def_code="uidebug";
		//$this->set_lngmsgsmancod("debug");
	}
	
}
?>