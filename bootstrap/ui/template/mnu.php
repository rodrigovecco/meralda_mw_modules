<?php
class  mwmod_mw_bootstrap_ui_template_mnu extends mwmod_mw_mnu_templates_mnutemplate{
	function __construct(){
			
	}
	
	function get_items_html($mnu){
		if(!$items=$mnu->get_items_allowed()){
			return false;	
		}
		$r="";
		foreach ($items as $item){
			$r.=$item->get_html_as_list_item();	
		}
		return $r;
	}


	
}
?>