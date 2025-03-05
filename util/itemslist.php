<?php
class  mwmod_mw_util_itemslist extends mw_apsubbaseobj{
	var $items=array();
	var $cods_list=array();
	
	function __construct(){
		
	}
	function get_cods(){
		if(!sizeof($this->cods_list)){
			return false;	
		}
		return $this->cods_list;	
	}
	
	function add_cods($cods){
		if(!$cods){
			return false;	
		}
		if(!is_array($cods)){
			$cods=explode(",",$cods);
		}
		$n=0;
		if(is_array($cods)){
			foreach($cods as $c){
				if($this->add_cod($c)){
					$n++;	
				}
			}
		}
		return $n;
		
	}
	function add_cod($cod){
		if(!$cod=trim($cod)){
			return false;
		}
		$this->cods_list[$cod]=$cod;
		return $cod;	
	}
	
	
	function add_item($item){
		$this->items[]=$item;
		return $item;	
	}
	function get_items(){
		reset($this->items);
		return $this->items;
	}

}
?>