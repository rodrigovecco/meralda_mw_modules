<?php
class mwmod_mw_datafield_number extends mwmod_mw_datafield_input{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		$this->set_def_params();
	}
	function set_def_params(){
		$this->set_param("int",true);
	}
	
}
?>