<?php

class mwmod_mw_mnu_man extends mw_apsubbaseobj{
	private $items=array();
	
	private $maininterface;
	private $subinterface;
	private $mode="independent";
	
	function __construct(){
		$this->set_mainap();	
	}
	
	final function set_sub_interface($subinterface){
		$main=$subinterface->maininterface;
		$this->set_main_interface($main);
		$this->subinterface=$subinterface;
		$this->mode="subinterface";
	}
	final function __get_priv_subinterface(){
		return $this->subinterface; 	
	}
	
	final function set_main_interface($maininterface){
		$this->maininterface=$maininterface;
		$this->mode="maininterface";

	}
	final function __get_priv_maininterface(){
		return $this->maininterface; 	
	}
	final function __get_priv_mode(){
		return $this->mode; 	
	}
	function is_mode($mode="independent"){
		if($this->mode==$mode){
			return true;	
		}
		return false;
	}
	
	final function get_items(){
		return $this->items;
	}
	final function get_item($cod){
		if($i=$this->get_existing_item($cod)){
			return $i;	
		}
		return $this->add_item($cod,false,true);
	}
	final function get_existing_item($cod){
		if(isset($this->items[$cod])and $this->items[$cod]){
			return $this->items[$cod];	
		}
		return false;
	}
	function get_debug_data(){
		$r=array();
		$r["class"]=get_class($this);
		if($items=$this->get_items()){
			$r["items"]=array();
			foreach ($items as $cod=>$item){
				$r["items"][$cod]=$item->get_debug_data();
			}
		}
		return $r;
		
			
	}
	
	
	final function add_item($cod,$item=false,$replace=false){
		if(!$replace){
			if($i=$this->get_existing_item($cod)){
				return $i;	
			}
		}
		
		if(!$item){
			$item=$this->create_item($cod);	
		}
		
		if(!is_array($this->items)){
			$this->items=array();	
		}
		$this->items[$cod]=$item;
		return $this->items[$cod];
	}
	function create_item($cod){
		if($this->is_mode("subinterface")){
			$i=	new mwmod_mw_mnu_ui($this->subinterface,$cod);
		}elseif($this->is_mode("maininterface")){
			$i=	new mwmod_mw_mnu_mainui($this->maininterface,$cod);
		}else{
			$i=	new mwmod_mw_mnu_mnu($cod);
		}
		return $i;
	}


}

?>