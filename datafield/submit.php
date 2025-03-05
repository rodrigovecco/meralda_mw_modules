<?php
class mwmod_mw_datafield_submit extends mwmod_mw_datafield_btn{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		$this->input_type="submit";
	}
	/*
	function get_html_bootstrap($bt_output_man){
		return $bt_output_man->get_html_btn($this);
	}

	
	function get_full_input_html(){
		if(!$t=$this->get_template()){
			return false;	
		}
		return $t->get_full_btn_html($this);
	}

	
	
	function get_input_att_as_array(){
		$a=$this->att;
		if(!is_array($a)){
			$a=array();	
		}
		$a["type"]="submit";	
		if($id=$this->get_frm_field_id()){
			$a["id"]=$id;	
		}
		if($name=$this->get_frm_field_name()){
			$a["name"]=$name;	
		}
		if($lbl=$this->get_lbl()){
			$a["value"]=$lbl;	
		}
		if($this->disabled){
			$a["disabled"]="disabled";	
		}
		if($s=$this->get_input_style_att()){
			$a["style"]=$s;		
		}
		
		return $a;
	}
	*/

}
?>