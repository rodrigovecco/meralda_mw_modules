<?php
class mwmod_mw_datafield_ckeditor extends mwmod_mw_datafield_textarea{
	
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		//$this->set_def_params();
	}
	function set_input_att_value(&$a=array()){
		return false;
	}
	function set_toolbar_openxml_mode(){
		$obj=new mwmod_mw_jsobj_obj();
		//$obj->set_param("");	
	}
	function add2jsreqclasseslist(&$list){
		$c="mw_input_elem_ckeditor";
		$list[$c]=$c;
		

		
	}
	function prepare_params_for_bootstrap(){
		$this->set_param("value",$this->value."");	
	}
	
	function get_js_man_class($frm=false){
		return "mw_input_elem_ckeditor";	
	}

	function get_input_html(){
		
		return $r="<textarea ".$this->get_input_att()." ></textarea>";
		/*
		$v=$this->value;
		
		if($this->fix_slashes_and_quotes){
			$v=$this->fix_slashes_and_quoutes_str($v);
		}
		
		
		return $r="<textarea ".$this->get_input_att()." >".$v."</textarea>";
		*/
	}
	

}
?>