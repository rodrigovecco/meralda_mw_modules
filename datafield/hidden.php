<?php
class mwmod_mw_datafield_hidden extends mwmod_mw_datafield_datafielabs{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);	
	}
	function get_html_bootstrap($bt_output_man){
		
		return $this->get_input_html();
		//return $bt_output_man->get_html_checkbox($this);
	}
	
	function get_input_att_as_array(){
		$a=$this->att;
		if(!is_array($a)){
			$a=array();	
		}
		$a["type"]="hidden";	
		if($id=$this->get_frm_field_id()){
			$a["id"]=$id;	
		}
		if($name=$this->get_frm_field_name()){
			$a["name"]=$name;	
		}
		$this->set_input_att_value($a);
		return $a;
	}

	
}
?>