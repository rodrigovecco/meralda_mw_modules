<?php

class mwmod_mw_db_sql_where_subwhere extends mwmod_mw_db_sql_where{
	var $cod="";
	var $querypart;
	var $general_cond="AND";
	var $not=false;
	
	function __construct($cod=false,$querypart=false){
		$this->set_cod($cod);
		$this->set_query_part($querypart);
	}
	function get_sql_as_other(){
		return $this->get_sql_other_prev().$this->get_sql();	
	}
	function get_sql_as_first(){
		return $this->get_sql();	
	}
	function get_sql_no_items(){
		return "";	
	}
	
	function append_to_sql(&$sql){
		if($this->pre_append_to_sql($sql)){
			$sql.=$this->get_sql_as_other();	
		}else{
			$sql.=$this->get_sql_as_first();		
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
	
	
	function set_and(){
		$this->general_cond="AND";	
	}
	function set_or(){
		$this->general_cond="OR";	
	}
	 
	function is_ok(){
		return true;	
	}
	
	
	function set_cod($cod=false){
		if($cod){
			$this->cod=$cod;
		}
		
	}
	function get_cod(){
		return $this->cod;	
	}
	final function set_query_part($part=false){
		if($part){
			$this->querypart=$part;
			$this->set_query($part->query);
		}
	}
	
	function get_sql_start(){
		return "(";	
	}
	function get_sql_end(){
		return ")";	
	}
	function get_sql_other_prev(){
		return " ".$this->general_cond;
	}
	
	function append_to_parameterized_sql($pq,&$tempSubSQLstr=""){

		if(!$items=	$this->get_items_ok()){
			 $pq->appendSQL($this->get_sql_no_items());
			 return;
		}
		if($this->pre_append_to_sql($tempSubSQLstr)){
			
			$pq->appendSQL($this->get_sql_other_prev(),$tempSubSQLstr);	
		}
		$pq->appendSQL(" ");

		$pq->appendSQL($this->get_sql_start());
		$sqlItemsTemp="";
		foreach ($items as $item){
			if($this->debug_mode){
				$item->debug_mode=true;	
			}
			
			$item->append_to_parameterized_sql($pq,$sqlItemsTemp);	
			if($this->debug_mode){
				$sql.="\n";
			}

		}
		$pq->appendSQL($this->get_sql_end());
		return true;
	}
	
	function get_sql(){
		$sql="";
		if(!$items=	$this->get_items_ok()){
			return $this->get_sql_no_items();	
		}
		foreach ($items as $item){
			if($this->debug_mode){
				$item->debug_mode=true;	
			}
			
			$item->append_to_sql($sql);	
			if($this->debug_mode){
				$sql.="\n";
			}

		}
		return $this->get_sql_start().$sql.$this->get_sql_end();
		
	}
	
}
?>