<?php
class mwmod_mw_datafield_btnsgroup extends mwmod_mw_datafield_group{
	
	
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
	}
	function get_full_input_html(){
		if(!$t=$this->get_template()){
			return false;	
		}
		return $t->get_full_btnsgroup_html($this);
	}

	function get_html_bootstrap($bt_output_man){
		
		return $bt_output_man->get_full_btnsgroup_html($this);
		//return $this->get_full_input_html();
	}

	
}
?>