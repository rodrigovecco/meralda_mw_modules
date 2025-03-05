<?php
class mwmod_mw_datafield_html extends mwmod_mw_datafield_datafielabs{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);	
	}
	function get_input_html(){
		return $this->get_value_as_html();
	}

	
}
?>