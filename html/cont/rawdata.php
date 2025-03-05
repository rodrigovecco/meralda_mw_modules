<?php

class mwmod_mw_html_cont_rawdata extends mwmod_mw_html_elem{
	public $data=array();
	function __construct($data=false,$tagname="pre"){
		$this->set_tagname($tagname);
		$this->data=$data;
	}
	function get_data_as_html(){
		return print_r($this->data,true);	
	}
	function get_html_in(){
		$r.=$this->get_cont_as_html();	
		$r.=$this->get_data_as_html();
		return $r;
		
	}

	
}

?>