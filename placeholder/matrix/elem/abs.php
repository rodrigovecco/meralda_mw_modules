<?php
abstract class mwmod_mw_placeholder_matrix_elem_abs extends mw_apsubbaseobj{
	private $matrix;
	
	final function init($matrix){
		$this->matrix=$matrix;	
	}
	
	function get_text_for_ph_src($src=false){
		return "";
	}
	function get_debug_txt(){
		return "-".strip_tags($this->get_text_for_ph_src())."-";	
	}
	
	final function __get_priv_matrix(){
		return $this->matrix; 	
	}
	
	
	function get_debug_data(){
		$r=array();
		$r["class"]=get_class($this);
		$r["debugtxt"]=$this->get_debug_txt();
		return $r;
	}
	
	
}
?>