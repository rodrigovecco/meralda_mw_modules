<?php
class mwmod_mw_placeholder_matrix_elem_ph extends mwmod_mw_placeholder_matrix_elem_abs{
	var $phcod;
	var $phparams;
	function __construct($phcod,$matrix){
		$this->init($matrix);
		$this->set_ph_cod($phcod);
	}
	
	function get_text_for_ph_src($src=false){
		if(!$src){
			return "";	
		}
		if(!$item=$src->get_item_by_dot_cod($this->phcod)){
			return "";	
		}
		return $item->get_text($this->phparams);
	}
	
	function get_debug_txt(){
		$r=$this->phcod;
		if($this->phparams){
			$r.=" ".$this->phparams;	
		}
		return "[[".$r."]]";	
	}
	
	function set_ph_cod($cod){
		if(!$cod){
			return false;
		}
		$a=explode(" ",$cod,2);
		$this->phcod=$a[0]??null;	
		$this->phparams=$a[1]??null;	
	}
	
	
}
?>