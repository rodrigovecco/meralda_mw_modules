<?php
//generic item
class  mwmod_mw_util_itemsbycod_item extends mw_apsubbaseobj{
	public $cod;
	public $name;
	private $jsparams;
	public $data=array();
	function __construct($cod,$name=false){
		$this->cod=$cod;
		if($name){
			$this->name=$name;
		}
	}
	function get_id(){
		return $this->get_cod();	
	}
	function get_cod(){
		return $this->cod;	
	}
	function get_name(){
		if($this->name){
			return $this->name;	
		}
		return $this->get_cod();
	}
	final function __get_priv_jsparams(){
		if(!isset($this->jsparams)){
			$this->jsparams=new mwmod_mw_jsobj_obj();	
		}
		return $this->jsparams;
		
	}
	function get_data($cod=false){
		if(!$cod){
			return $this->data;	
		}
		return $this->data[$cod]??null;	
	}
	
	
	
}
?>