<?php
class mwmod_mw_db_sql_limit extends mwmod_mw_db_sql_querypart{
	var $active=false;
	var $from;
	var $num;
	function __construct($query=false){
		if($query){
			$this->set_query($query);	
		}
	}
	function set_limit($num,$from=false){
		$this->num=intval(abs($num));
		if(!$from){
			$this->from=false;	
		}else{
			$this->from=intval(abs($from));
		}
		//$this->from=$from;
		
	}
	function append_to_parameterized_sql($pq,&$tempSubSQLstr=""){
		$pq->appendSQL($this->get_sql());
	}
	function get_sql(){
		$sql="";
		if(!$this->num){
			return "";	
		}
		
		if(!is_int($this->num)){
			return "";	
		}
		if(is_int($this->from)){
			if($this->dbModeCheckSQLsrv()){
				return "OFFSET $this->from ROWS FETCH NEXT $this->num ROWS ONLY ";	
			}
			return " limit $this->num OFFSET $this->from ";	
		}
		if($this->dbModeCheckSQLsrv()){
			return "OFFSET 0 ROWS FETCH NEXT $this->num ROWS ONLY ";	
		}
		return " limit $this->num ";	
		
	}

	
	function get_sql_start(){
		//revisar!
		//return " from ";	
		return " ";	
	}

	
}
?>