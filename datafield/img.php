<?php
class mwmod_mw_datafield_img extends mwmod_mw_datafield_file{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		//$this->set_def_params();
	}
	function get_input_html_existing(){
		if(!$l=$this->get_existing_file_url()){
			return false;	
		}
		return "<div><img src='$l'></div>";
	}

	

}
?>