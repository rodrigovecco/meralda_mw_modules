<?php
abstract class mwmod_mw_bootstrap_html_template_abs extends mwmod_mw_html_elem{
	private $main_elem;
	private $cont_elem;
	private $title_elem;
	final function set_main_elem($elem){
		$this->main_elem=$elem;
		$this->add_cont($elem);
		$this->set_key_cont("main",$elem);
		
	}
	function create_cont($main){
		$this->set_main_elem($main);
		//extender
	}
	function add_additional_class($class){
		if($this->main_elem){
			return $this->main_elem->add_additional_class($class);	
		}
	}
	function unset_additional_classes(){
		if($this->main_elem){
			return $this->main_elem->unset_additional_classes();	
		}
	}
	function set_display_mode($mode="default"){
		if($this->main_elem){
			return $this->main_elem->set_display_mode($mode);	
		}
	}
	final function set_cont_elem($elem){
		$this->cont_elem=$elem;
		$this->set_key_cont("cont",$elem);
	}
	final function set_title_elem($elem){
		$this->title_elem=$elem;
		$elem->only_visible_when_has_cont=true;
		$this->set_key_cont("title",$elem);
	}
	
	function get_html_open(){
		return "";
	}
	function get_html_close(){
		return "";
		
	}
	final function __get_priv_main_elem(){
		return $this->main_elem;	
	}
	final function __get_priv_title_elem(){
		return $this->title_elem;	
	}
	final function __get_priv_cont_elem(){
		return $this->cont_elem;	
	}
	
	
}

?>