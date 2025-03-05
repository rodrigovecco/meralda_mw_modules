<?php
class mwmod_mw_datafield_password extends mwmod_mw_datafield_datafielabs{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);	
	}
	function get_input_att_as_array(){
		$a=$this->att;
		if(!is_array($a)){
			$a=array();	
		}
		$a["type"]="password";	
		if($id=$this->get_frm_field_id()){
			$a["id"]=$id;	
		}
		if($name=$this->get_frm_field_name()){
			$a["name"]=$name;	
		}
		if($this->req){
			$a["required"]="required";	
		}
		if($this->disabled){
			$a["disabled"]="disabled";	
		}
		if(!isset($a["autocomplete"])){
			$a["autocomplete"]="off";	
		}
		
		if($s=$this->get_input_style_att()){
			$a["style"]=$s;		
		}
		
		return $a;
	}

	
}
?>