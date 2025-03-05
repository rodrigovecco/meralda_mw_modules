<?php

class mwmod_mw_html_lb extends mwmod_mw_html_elem{
	
	function __construct(){
		
	}
	function get_as_html(){
		if(!$this->is_visible()){
			return "";
		}
		return $this->get_html_in();
	}
	function do_output(){
		if(!$this->is_visible()){
			return false;
		}
		$this->do_output_in();
	}

	function get_html_in(){
		return "\n";	
	}
	function do_output_in(){
		echo $this->get_html_in();
	}
	function get_html_open(){
		return "";
	}
	function get_html_close(){
		return "";
		
	}
}

?>