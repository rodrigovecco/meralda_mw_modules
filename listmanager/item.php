<?php
class mwmod_mw_listmanager_item extends mw_apsubbaseobj{
	public $value;
	public $lbl;
	public $relateditem;
	public $data;
	function __construct($value,$lbl=false,$data=array(),$relateditem=false){
		$this->init($value,$lbl,$data,$relateditem);
	}
	function get_debug_data(){
		$r["object"]=$this;
		$r["value"]=$this->get_value();
		$r["lbl"]=$this->get_lbl();
		return $r;
	
	}
	function get_data_for_js_array($codkey="cod",$namekey="name",$extrakeys=false){
		$r[$codkey]=$this->get_value();
		$r[$namekey]=$this->get_lbl();
		if($extrakeys){
			if(is_string($extrakeys)){
				$extrakeys=explode(",",$extrakeys);	
			}
			if(is_array($extrakeys)){
				foreach($extrakeys as $c){
					if($c=trim($c)){
						$r[$c]=$this->get_data_val($c);
					}
				}
			}
		}
		return $r;
	}

	
	function get_items_by_value(){
		$value=$this->get_value();
		return array($value=>$this);
	}
	function is_data_val($cod,$val){
		if(!$v=$this->get_data_val($cod)){
			return false;	
		}
		
		if($val==$v){
			return true;	
		}
	}
	function get_data_val($cod){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;	
		}
		
		return $this->data[$cod];
	}
	final function init($value,$lbl,$data,$relateditem=false){
		$this->value=$value;
		$this->lbl=$lbl;
		$this->relateditem=$relateditem;
		if(!is_array($data)){
			$data=array("id"=>$id,"lbl"=>$lbl);	
		}
		$this->data=$data;
		$this->set_mainap();
	}
	function get_id(){
		return $this->value+0;	
	}
	function get_value(){
		return $this->value;	
	}
	function is_selected($value=NULL){
		if((is_numeric($this->value)and($this->value==0))){
			if(is_numeric($value)){	
				if($value==0){
					return true;	
				}
			}
			return false;
		}
		if($value==$this->value){
			return true;
		}
	}
	function get_lbl(){
		return $this->lbl;
	}
	function get_value_for_option_html(){
		return addslashes($this->value);
	}
	function __toString(){
		return $this->get_lbl()."";
	}
	function get_options_html($value=NULL){
		$s="";
		if($this->is_selected($value)){
			$s=" selected ";	
			
		}
		
		
		$r="<option $s value='".$this->get_value_for_option_html()."'>".$this->get_lbl()."</option>\n";
		return $r;
	}
}

?>