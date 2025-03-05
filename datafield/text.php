<?php
class mwmod_mw_datafield_text extends mwmod_mw_datafield_input{
	
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		$this->fix_slashes_and_quotes=true;
		
	}
	
}
?>