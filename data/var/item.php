<?php
class mwmod_mw_data_var_item extends mwmod_mw_data_session_item{
	function __construct($mainman,$cod){
		$this->init($mainman,$cod);	
	}
	function get_sub_item($key){
		if(!$key){
			return false;	
		}
		if(!is_string($key)){
			if(!is_numeric($key)){
				return false;
			}
		}
		$item=new mwmod_mw_data_var_subitem($key,$this);
		return $item;
	
	}
	

}


?>