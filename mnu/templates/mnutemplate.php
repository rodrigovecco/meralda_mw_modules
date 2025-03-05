<?php
class mwmod_mw_mnu_templates_mnutemplate extends mw_apsubbaseobj{
	function __construct(){
		
	}
	function get_html_item_by_outputcod($item){
		if($fnc=$this->get_item_outputcod_fnc($item)){
			return $this->$fnc($item);	
		}else{
			return false;	
		}
	}
	function allow_get_html_by_outputcod_item($item){
		if($fnc=$this->get_item_outputcod_fnc($item)){
			return true;	
		}else{
			return false;	
		}
	}
	function get_item_outputcod_fnc($item){
		if(!$cod=$item->get_template_output_cod()){
			return false;	
		}
		$fnc="gethtmlitemcus_".$cod;
		if(method_exists($this,$fnc)){
			return $fnc;	
		}
	}
	function get_html_open(){
		return "";	
	}
	function get_html_close(){
		return "";	
	}
	function get_html($mnu){
		$h=$this->get_html_open();
		$h.=$this->get_items_html($mnu);
		$h.=$this->get_html_close();
		return $h;
	}
	function get_item_html($item){
		return $item->get_html();	
	}
	function get_item_html_cus_if_avaible($item){
		return $item->get_html_for_template($this);	
	}
	//

	function get_items_html($mnu){
		if(!$items=$mnu->get_items_allowed()){
			return false;	
		}
		$r="";
		foreach ($items as $item){
			if($mnu->vertical){
				$r.="<div>";	
			}
			$r.=$this->get_item_html_cus_if_avaible($item);	
			if($mnu->vertical){
				$r.="</div>";	
			}
		}
		return $r;
	}



}

?>