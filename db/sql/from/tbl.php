<?php
class mwmod_mw_db_sql_from_tbl extends mwmod_mw_db_sql_querysubpart{
	var $join_mode="left join";
	var $tbl;
	var $inner_join_field="id";
	var $external_join_field;
	
	var $as_mode=false;

	public $specialON;
	
	function __construct($tbl,$cod=false,$querypart=false){
		$this->tbl=$tbl;
		$this->set_cod($cod);
		$this->set_query_part($querypart);
	}
	function set_as_mode($as_cod=false){
		if(!$as_cod){
			$as_cod=$this->get_cod();	
		}
		if(!$as_cod){
			return false;
		}
		if(is_string($as_cod)){
			$this->as_mode=$as_cod;
			return true;	
		}
	}
	function set_join_both(){
		$this->join_mode="join";
	}
	function set_join_right(){
		$this->join_mode="right join";
	}
	function set_join_left(){
		$this->join_mode="left join";
	}
	function is_as_mode(){
		return $this->as_mode;	
	}
	function get_as_cod(){
		if(!$this->as_mode){
			return false;	
		}
		if(is_string($this->as_mode)){
			return $this->as_mode;	
		}
	}
	function get_sql_as_other_as_mode(){
		$r=" ".$this->join_mode." ".$this->get_tbl();
		$tblname=$this->get_tbl();
		if($cod=$this->get_as_cod()){
			$r.=" as $cod ";
			$tblname=$cod;	
		}
		
		
		if($this->specialON){
			$r.=" on (".$this->specialON.")";
		}elseif($this->external_join_field){
			$r.=" on (".$this->external_join_field."=".$tblname.".".$this->inner_join_field.")";
		}
		return $r;
	}
	
	function get_sql_as_other(){
		if($this->is_as_mode()){
			return $this->get_sql_as_other_as_mode();	
		}
		$r=" ".$this->join_mode." ".$this->get_tbl();
		/*
		if($cod=$this->get_cod()){
			$r.=" as $cod ";	
		}
		*/
		
		if($this->specialON){
			$r.=" on (".$this->specialON.")";
		}elseif($this->external_join_field){
			$r.=" on (".$this->external_join_field."=".$this->get_tbl().".".$this->inner_join_field.")";
		}
		return $r;
	}

	function get_sql_as_first(){
		$r=" ".$this->get_tbl();
		
		if($cod=$this->get_as_cod()){
			$r.=" as $cod ";
			
		}

		return $r;	
		//return $this->get_tbl();	
	}
 
	function get_sql_in(){
		
		return $this->sql;
	}
	function get_tbl(){
		
		return $this->tbl;
	}
	
	function get_sql(){
		
		$sql=$this->get_sql_in();
		if($cod=$this->get_cod()){
			$sql.=" as $cod";	
		}
		return $sql;
	}
	
}
?>