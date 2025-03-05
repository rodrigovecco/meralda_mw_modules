<?php
class mwmod_mw_placeholder_matrix_elem_txt extends mwmod_mw_placeholder_matrix_elem_abs{
	var $txt;
	function __construct($txt,$matrix){
		$this->init($matrix);
		$this->txt=$txt."";
	}
	
	function get_text_for_ph_src($src=false){
		return $this->txt;
	}
	
	
}
?>