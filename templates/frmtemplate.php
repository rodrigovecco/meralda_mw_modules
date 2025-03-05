<?php
//
class mwmod_mw_templates_frmtemplate extends mwmod_mw_templates_basetemplate{
	function __construct($htmlclasspref="sys_inteface_sub"){
		if($htmlclasspref){
			$this->htmlclasspref=$htmlclasspref;	
		}
	}
	function can_do_html_frm(){
		return false;	
	}
	function get_html_frm($frm){
		
		$r.=$frm->get_html_before();	
		$r.=$frm->get_html_open();	
		$r.=$frm->get_html_in();	
		$r.=$frm->get_html_close();	
		$r.=$frm->get_html_after();	
		$r.=$frm->get_htmltag_jsscript();	
		return $r;

	}
	function new_input_template($datafield=false){
		$t=new mwmod_mw_datafield_template();
		return $t;
	}

	

}


?>