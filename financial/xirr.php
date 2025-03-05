<?php
class mwmod_mw_financial_xirr extends mw_apsubbaseobj{
	var $items=array();
	var $financial_helper;
	var $xirr;
	var $guess=0.1;
	function __construct(){
	}
	function add_amount($date,$amount){
		if($item=$this->get_or_add_item_by_date($date)){
			$item->add_amount($amount);
			return $item;	
		}
	}
	function get_or_add_item_by_date($date){
		if($item=$this->get_item_by_date($date)){
			return $item;	
		}
		return $this->add_item($date);
		
	}
	function get_date_cod($date){
		if(!$date){
			return false;	
		}
		$t=strtotime($date);
		return date("Y-m-d",$t);
	
	}
	function get_item_by_date($date){
		if(!$cod=$this->get_date_cod($date)){
			return false;	
		}
		return $this->items[$cod]??null;
	}
	function get_financial_helper(){
		if(!isset($this->financial_helper)){
			$this->financial_helper=new mwmod_mw_financial_helper();	
		}
		return $this->financial_helper;
	}
	function unset_data(){
		unset($this->xirr);
		$this->items=array();	
	}
	function set_guess($val=0.1){
		$this->guess=$val;
		unset($this->xirr);
	}
	function get_xirr(){
		if(!isset($this->xirr)){
			$this->xirr=$this->load_xirr();
		}
		return $this->xirr;
			
	}
	function load_xirr(){
		if(!$fin=$this->get_financial_helper()){
			return false;	
		}
		$dates=array();
		$amounts=array();
		if($items=$this->get_items()){
			foreach($items as $i=>$item){
				$item->populate_data($dates,$amounts);
			}
		}
		$r=$fin->XIRR($amounts, $dates, $this->guess);
		return $r;
		
		
		
	}
	function get_debug_data_short(){
		$r=array();
		$xirr=$this->get_xirr();
		$r["xirr"]=$xirr;
		$r["xirr_percent"]=$xirr*100;
		$r["guess"]=$this->guess;
		return $r;
		
	}
	
	function get_debug_data(){
		$r=array();
		$dates=array();
		$amounts=array();
		$xirr=$this->get_xirr();
		$r["xirr"]=$xirr;
		$r["xirr_percent"]=$xirr*100;
		$r["guess"]=$this->guess;
		
		
		
		if($items=$this->get_items()){
			foreach($items as $i=>$item){
				$r["items"][$i]=$item->get_debug_data();
				//$item->populate_data($dates,$amounts);	
			}
		}
		//$r["dates"]=$dates;
		//$r["amounts"]=$amounts;
		
		return $r;
	}
	
	function get_items(){
		return $this->items;
	}
	function new_item($date){
		$index=sizeof($this->items)+1;
		$item=new mwmod_mw_financial_xirr_item($date,$index,$this);
		return $item;	
	}
	function add_item($date){
		if(!$cod=$this->get_date_cod($date)){
			return false;	
		}
		
		if(!$item=$this->new_item($cod)){
			return false;	
		}
		return $this->_add_item($item);
	}
	function _add_item($item){
		$i=$item->date;
		$this->items[$i]=$item;
		return $item;
	}
	
}
?>