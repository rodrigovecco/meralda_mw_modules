<?php

abstract class mwmod_mw_ui_sub_withfrm extends mwmod_mw_ui_sub_uiabs{
	function prepare_before_exec_no_sub_interface(){
		$p=new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
	}
}
?>