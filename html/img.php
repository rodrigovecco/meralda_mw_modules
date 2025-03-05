<?php

class mwmod_mw_html_img extends mwmod_mw_html_elem{
	
	function __construct($src=false,$atts=false){
		$this->set_tagname("img");
		$this->init_atts($atts);
		$this->set_dont_close(true);
		if($src){
			$this->set_att("src",$src);
		}
		
	}
	function set_title($title){
		$this->set_att("title",$title);
		$this->set_att("alt",$title);
		
	}
	function get_html_in(){
		return "";	
	}
	function get_def_tag_name(){
		return "img";	
	}
	function do_output_in(){
		//
	}
	
}

?>