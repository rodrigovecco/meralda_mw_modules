<?php
abstract class mwmod_mw_db_sql_querysubpart extends mwmod_mw_db_sql_abs{
	private $query;
	var $sql="";
	var $cod="";
	var $querypart;
	function get_sql_other_prev(){
		return " ,";
	}
	function set_cod($cod=false){
		if($cod){
			$this->cod=$cod;
		}
		
	}
	function get_cod(){
		return $this->cod;	
	}
	function get_sql(){
		return $this->sql;
	}
	
	function get_sql_as_other(){
		return $this->get_sql_other_prev().$this->get_sql();	
	}
	function get_sql_as_first(){
		return $this->get_sql();	
	}
	function append_to_sql(&$sql){
		if($this->pre_append_to_sql($sql)){
			$sql.=$this->get_sql_as_other();	
		}else{
			$sql.=$this->get_sql_as_first();		
		}
	}
	function append_to_parameterized_sql($pq,&$tempSubSQLstr=""){
		if($this->pre_append_to_sql($tempSubSQLstr)){
			$pq->appendSQL($this->get_sql_as_other(),$tempSubSQLstr);	
		}else{
			$pq->appendSQL($this->get_sql_as_first(),$tempSubSQLstr);		
		}
	}
	function pre_append_to_sql(&$sql){
		if(!$sql){
			$sql="";
		}
		if(!is_string($sql)){
			$sql="";
		}
		$s=$sql;
		if(strlen(trim($s))){
			return true;	
		}
		return false;
		
	}
	function is_ok(){
		return true;	
	}
	final function set_query_part($part=false){
		if($part){
			$this->querypart=$part;
			$this->set_query($part->query);
		}
	}
	final function set_query($query=false){
		if($query){
			$this->query=$query;	
		}
	}
	function __get_priv_query(){
		return $this->__get_query();	
	}
	function get_debug_data(){
		$r=array();
		$r["class"]=get_class($this);
		$r["dbmanclass"]=get_class($this->dbman);
		$r["cod"]=$this->get_cod();
		$r["sql"]=$this->get_sql();
		return $r;
			
	}
	function load_dbman(){
		if($this->query){
			if($m=$this->query->dbman){
				return $m;	
			}
		}
		return 	$this->mainap->get_submanager("db");	
	}
	

	final function __get_query(){
		return $this->query;	
	}
	
}
?>