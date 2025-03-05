<?php
//rvh 2015-01-10 v 1

class mwmod_mw_html_manager_item_jscus extends mwmod_mw_html_manager_item_js{
	var $js_container;
	function __construct($cod,$js_container=false){
		$this->init_item($cod);
		if(!$js_container){
			$js_container=new mwmod_mw_jsobj_codecontainer();	
		}
		$this->js_container=$js_container;
	}
	function get_html_declaration(){
		$r="";
		if($this->js_container){
			if($cod=$this->js_container->get_as_js_val()){
				$r.="<script type='text/javascript'  language='javascript'>$cod</script>\n";
			}
		}
		return $r;
		
	}
}
?>