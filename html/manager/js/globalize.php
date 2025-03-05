<?php
//rvh 2015-01-10 v 1
class mwmod_mw_html_manager_js_globalize extends mwmod_mw_html_manager_item_js{
	function __construct($cod="globalize"){
		$this->init_item($cod);
		
	}
	function get_html_declaration(){
		$r="<script type='text/javascript'  language='javascript' src='".$this->get_src()."'></script>\n";
		$r.="<script type='text/javascript'  language='javascript' src='/res/globalize/mwculture_def.js'></script>\n";
		
		if($lng=$this->mainap->get_current_lng_man()){
			if($src=$this->get_culture_src($lng->get_globalize_locale_src())){
				$r.="<script type='text/javascript'  language='javascript' src='".$src."'></script>\n";
			}
			if($src=$this->get_mw_culture_src($lng->get_ini_cfg_value("globalize_locale_mw_src"))){
				$r.="<script type='text/javascript'  language='javascript' src='".$src."'></script>\n";
			}
			if($cod=$lng->get_globalize_locale_cod()){
				$r.="<script type='text/javascript'  language='javascript'>Globalize.culture('$cod');</script>\n";
			}
		}
		
		return $r;
		
	}
	function get_mw_culture_src($file){
		if(!$file){
			return false;	
		}
		return "/res/globalize/mwculture/$file";
	}
	
	function get_culture_src($file){
		if(!$file){
			return false;	
		}
		return "/res/globalize/cultures/$file";
	}
	function get_src(){
		return "/res/globalize/globalize.min.js";
	}

	
}
?>