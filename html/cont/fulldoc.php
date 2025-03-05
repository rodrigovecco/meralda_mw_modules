<?php

class mwmod_mw_html_cont_fulldoc extends mwmod_mw_html_cont_varcont{
	var $dom_html;
	var $head;
	var $body;
	
	function __construct(){
		$this->create_cont();
	}
	function create_cont(){
		$this->add_cont("<!DOCTYPE HTML>\n");
		$this->dom_html=new mwmod_mw_html_elem("html");
		$this->add_cont($this->dom_html);
		$this->head=new mwmod_mw_html_elem("head");
		$this->dom_html->add_cont($this->head);
		$this->body=new mwmod_mw_html_elem("body");
		$this->dom_html->add_cont($this->body);
		
		
		
			
	}
	
}

?>