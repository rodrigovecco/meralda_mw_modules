<?php
//no usado!!
class  mwmod_mw_db_tbldef extends mw_apsubbaseobj{
	private $dbman;
	private $tbl_name;
	private $tblman;
	private $_fields=array();
	private $_keys=array();
	
	/*
	private $tblfields;
	private $id_field="id";
	private $items=array();
	private $items2preload=array();
	private $allitems;
	*/
	function __construct($tbl_name,$dbman=false){
		$this->init($tbl_name,$dbman);	
	}
	
	
	function add_field_id($id="id"){
		$item= new mwmod_mw_db_fields_int($id);
		$item->AUTO_INCREMENT=true;
		
		if(!$new_item=$this->add_field($item)){
			return false;	
		}
		//falta set_prim_key
		return $new_item;
	}
	function add_fields_varchar($codsstr,$Length=255,$DEFAULT=""){
		if(!$cods=$this->get_new_fields_codes_from_str($codsstr)){
			return false;	
		}
		$items=array();
		foreach($cods as $cod){
			$items[$cod]=new mwmod_mw_db_fields_varchar($cod,$Length,$DEFAULT);
		}
		return $this->add_fields($items);
	}
	
	function add_fields_int($codsstr,$UNSIGNED=true){
		if(!$cods=$this->get_new_fields_codes_from_str($codsstr)){
			return false;	
		}
		$items=array();
		foreach($cods as $cod){
			$items[$cod]=new mwmod_mw_db_fields_int($cod,$UNSIGNED);
		}
		return $this->add_fields($items);
	}
	private function get_new_fields_codes_from_str($codsstr){
		if(!$codsstr){
			return false;	
		}
		$r=array();
		$codsstr=str_replace(" ",",",$codsstr);
		$cods=explode(",",$codsstr);
		foreach($cods as $cod){
			if($cod=trim($cod)){
				$r[$cod]=$cod;	
			}
				
		}
		if(sizeof($r)){
			return $r;	
		}
	}
	
	function add_fields($items){
		if(!$items){
			return false;	
		}
		if(!is_array($items)){
			return false;	
		}
		if(sizeof($items)){
			if(sizeof($items)==1){
				return $this->add_field($items[0]);	
			}
			$r=array();
			foreach($items as $cod=>$item){
				if($this->add_field($item)){
					$r[$cod]=$item;	
				}
			}
			return $r;
	
		}
	}
	final function get_field($cod){
		if(!$cod){
			return false;	
		}
		return $this->_fields[$cod];
	}

	final function add_field($item){
		if(!$item){
			return false;	
		}
		if(!is_object($item)){
			return false;	
		}
		$cod=$item->cod;
		$this->_fields[$cod]=$item;
		$item->set_tbldef($this);
		return $item;
	}
	
	
	////
	function get_tbl_man(){
		if($this->tblman){
			return $this->tblman;	
		}
		return $this->set_tbl_man_from_db_man();
		
	}
	function set_tbl_man_from_db_man(){
		if($this->tblman){
			return $this->tblman;	
		}
		if(!$this->dbman){
			return false;	
		}
		if($man=$this->dbman->get_tbl_manager($this->tbl_name)){
			return $this->set_tbl_man($man);
		}
	}
	final function set_tbl_man($tbl_man=false){
		if($tbl_man){
			$this->tblman=$tbl_man;
			return $tbl_man;
		}
	}
	
	final function set_db_man($dbman=false){
		if($dbman){
			$this->dbman=$dbman;	
		}
	}
	final function init($tbl_name,$dbman=false){
		$this->tbl=$tbl_name;
		$this->set_db_man($dbman);
	}
	final function __get_priv_dbman(){
		return $this->dbman; 	
	}
	final function __get_priv_tbl_name(){
		return $this->tbl_name; 	
	}
	final function __get_priv_tblman(){
		return $this->tblman; 	
	}
	
}

?>