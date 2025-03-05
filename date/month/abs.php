<?php
//
abstract class mwmod_mw_date_month_abs extends mw_apsubbaseobj{
	private $id;
	private $man;
	var $name;
	var $shortname;
	private $_replace_list;
	
	
	final function init_month($id,$shortname,$name,$man){
		$this->id=$id+0;
		$this->man=$man;
		$this->name=$name;
		$this->shortname=$shortname;
		
	}
	function set_replace_list(&$list=array()){
		if(!$l=$this->get_replace_list()){
			return;	
		}
		foreach($l as $c=>$v){
			$list[$c]=$v;	
		}
	}
	function load_replace_list(){
		$replace=array();
		$replace["F"]=$this->get_name();
		$replace["M"]=$this->get_short_name();
		return $replace;
	}
	final function get_replace_list(){
		if(isset($this->_replace_list)){
			return 	$this->_replace_list;
		}
		$this->_replace_list=array();
		if(!$l=$this->load_replace_list()){
			return 	$this->_replace_list;
		}
		foreach($l as $c=>$v){
			$va=str_split($v);
			$this->_replace_list[$c]="\\".implode("\\",$va);
			
		}
		return 	$this->_replace_list;
	}
	function get_name(){
		return ucfirst($this->name);	
	}
	function get_short_name(){
		return ucfirst($this->shortname);	
	}
	function __toString(){
		return $this->get_name();	
	}
	
	final function __get_priv_man(){
		
		return $this->man;
	}
	final function __get_priv_id(){
		
		return $this->id;
	}


	
}


?>