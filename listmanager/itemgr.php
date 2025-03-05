<?php

class mwmod_mw_listmanager_itemgr extends mwmod_mw_listmanager_item{
	var $items=array();
	function __construct($value,$lbl=false,$data=array(),$relateditem=false){
		$this->init($value,$lbl,$data,$relateditem);
	}
	
	function get_debug_data(){
		$r["object"]=$this;
		$r["value"]=$this->get_value();
		$r["lbl"]=$this->get_lbl();
		if(!sizeof($this->items)){
			return $r;
		}
		$r["items"]=array();
		foreach ($this->items as $item){
			$r["items"][]=$item->get_debug_data();
		}
		return $r;
	
	
	}

	
	function add_item($item){
		
		$index=$item->get_value();
		$this->items[$index]=$item;
		
	}
	function get_items_by_value(){
		$r=array();
		foreach ($this->items as $item){
			if($vv=$item->get_items_by_value()){
				if(is_array($vv)){
					foreach ($vv as $id=>$iv){
						$r[$id]=$iv;	
					}
				}
			}
		}
		return $r;
		
	}
	function get_options_html($value=NULL){
		if(!sizeof($this->items)){
			return false;
		}
		$r.="<optgroup label='".$this->get_lbl()."'>\n";
		foreach ($this->items as $item){
			$r.=$item->get_options_html($value);
		}
		$r.="</optgroup>\n";
		return $r;
	}
	
	
	
}

?>