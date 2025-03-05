<?php
class mwmod_mw_datafield_checkbox extends mwmod_mw_datafield_datafielabs{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);	
	}
	function get_js_man_class($frm=false){
		return "mw_input_elem_chkbox";	
	}
	function get_html_bootstrap($bt_output_man){
		return $bt_output_man->get_html_checkbox($this);
	}

	function get_value_as_html(){
		if($this->get_value()){
			return $this->lng_common_get_msg_txt("yes","Sí");	
		}else{
			return $this->lng_common_get_msg_txt("no","No");	
			
		}
	}
	

}
?>