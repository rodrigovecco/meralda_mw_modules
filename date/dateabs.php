<?php

abstract class mwmod_mw_date_dateabs extends mw_apsubbaseobj{
	private $dateman;
	var $valid=false;
	var $time;
	var $timenohour;
	var $parts;
	var $month;
	var $weekday;
	private $DateTime;
	private $DateTimeNoHour;
	function load_DateTimeNoHour(){
		if($d=$this->get_sys_date(false)){
			return new DateTime($d);
		}
		
	}
	final function __get_priv_DateTimeNoHour(){
		if(isset($this->DateTimeNoHour)){
			return $this->DateTimeNoHour;	
		}
		if($t=$this->load_DateTimeNoHour()){
			$this->DateTimeNoHour=$t;
			return $this->DateTimeNoHour;	
		}
		
	}
	
	function load_DateTime(){
		if($d=$this->get_sys_date(true)){
			return new DateTime($d);
		}
		
	}
	final function __get_priv_DateTime(){
		if(isset($this->DateTime)){
			return $this->DateTime;	
		}
		if($t=$this->load_DateTime()){
			$this->DateTime=$t;
			return $this->DateTime;	
		}
		
	}
	
	
	function init_parts(){
		if(isset($this->parts)){
			return true;	
		}
		if($p=$this->load_parts()){
			$this->parts=$p;
			return true;	
		}
	
			
	}
	function get_parts(){
		if($this->init_parts()){
			return $this->parts;	
		}
	}
	function get_part($cod){
		if($this->init_parts()){
			return $this->parts[$cod];	
		}
		
	}
	
	final function unset_data(){
		$this->valid=false;
		unset($this->time);	
		unset($this->DateTime);	
		unset($this->DateTimeNoHour);	
		unset($this->timenohour);	
		unset($this->parts);	
		unset($this->month);	
		unset($this->weekday);	
	}
	
	final function init($man=false){
		$this->set_mainap();
		$this->set_lngmsgsmancod("date");	
		if($man){
			$this->set_dateman($man);	
		}
		
	}
	final function set_dateman($dateman){
		if($dateman){
			
			$this->dateman=$dateman;
			return true;	
		}
	}
	function get_time_no_hour(){
		if($this->is_valid()){
			return $this->__get_priv_timenohour();	
		}
			
	}
	function load_time_no_hour(){
		if($d=$this->get_sys_date(false)){
			return strtotime($d);
		}
	}
	final function __get_priv_timenohour(){
		if(isset($this->timenohour)){
			return $this->timenohour;	
		}
		if($t=$this->load_time_no_hour()){
			$this->timenohour=$t;
			return $this->timenohour;	
		}
		
	}
	
	final function __get_priv_dateman(){
		if(isset($this->dateman)){
			return $this->dateman;	
		}
		
		$this->dateman=false;
		if($fm=$this->mainap->get_submanager("dateman")){
			$this->dateman=$fm;
		}
		return $this->dateman;	
	}
	
}
?>