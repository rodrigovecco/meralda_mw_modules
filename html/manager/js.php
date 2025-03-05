<?php
//rvh 2015-01-10 v 1
class mwmod_mw_html_manager_js extends mwmod_mw_html_manager_abs{
	
	function __construct(){
		$this->init_man();
		$this->set_def_path("/res/js/");
	}
	function get_htmlscript($code,$onpageload=true){
		if ($onpageload){
			//revisar;
			//$code="mw_addLoadEvent(function(){".$code."})";
		}
		return "<script language='javascript' type='text/javascript'>\n$code\n</script>\n";
	}
	function add_jquery(){
		if($item=$this->get_item("jquery")){
			return $item;	
		}
		$item=new mwmod_mw_html_manager_js_jquery();
		return $this->add_item_by_item($item);
	}
	function add_globalize(){
		if($item=$this->get_item("globalize")){
			return $item;	
		}
		$item=new mwmod_mw_html_manager_js_globalize();
		return $this->add_item_by_item($item);
	}

	
	function create_item($cod){
		if(!$cod=$this->check_str_key($cod)){
			return false;	
		}
		$item= new mwmod_mw_html_manager_item_js($cod);

		return $item;	
	}

	
}
?>