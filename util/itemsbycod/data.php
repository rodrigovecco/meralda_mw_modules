<?php
//generic item
class  mwmod_mw_util_itemsbycod_data extends mwmod_mw_util_itemsbycod_item{
	public $data=array();
	function __construct($cod,$data=false){
		$this->cod=$cod;
		if($data){
			$this->data=$data;
		}
	}
	function get_data($cod=false){
		if(!$cod){
			return $this->data;	
		}
		return $this->data[$cod]??null;	
	}
	
	
}
?>