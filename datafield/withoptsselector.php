<?php
class mwmod_mw_datafield_withoptsselector extends mwmod_mw_datafield_input{
	
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		$this->fix_slashes_and_quotes=true;
		
	}
	function add2jsreqclasseslist(&$list){
		$c="mw_input_elem_withoptsselector";
		$list[$c]=$c;
	}
	function get_js_man_class($frm=false){
		return "mw_input_elem_withoptsselector";	
	}
	
	function get_html_bootstrap($bt_output_man){
		$r="<div class='form-group'>\n";
		if(!$this->omit_lbl){
			$r.=$bt_output_man->get_html_def_lbl($this);
		}
		
		$r.="<div class='input-group'>\n";
		$r.="<div class='input-group-btn'>\n";
		$r.='<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span name="optsSelectorLbl"></span> <span class="caret"></span></button>';
		$r.="<ul class='dropdown-menu'></ul>";
		$r.="</div>\n";
		$r.=$bt_output_man->get_html_def_crt($this);
		$r.="</div>\n";
		$r.="</div>\n";
		return $r;

		
		//return $bt_output_man->get_html_def($this);
	}
	
}
?>