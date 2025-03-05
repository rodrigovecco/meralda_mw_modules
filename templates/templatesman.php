<?php
class mwmod_mw_templates_templatesman extends mw_apsubbaseobj{
	function __construct(){
		$this->set_mainap();
	}
	//crea templates por defecto
	function create_template_datafields($df=false){
			
		$m=new mwmod_mw_datafield_template();
		return $m;

	}
	function create_template_frm($frm=false){
		$m=new mwmod_mw_templates_frmtemplate();
		return $m;
	}
	
	function __call($a,$b){
		return false;	
	}
	
}
?>