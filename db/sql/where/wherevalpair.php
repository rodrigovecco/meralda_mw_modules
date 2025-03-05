<?php
class mwmod_mw_db_sql_where_wherevalpair extends mwmod_mw_db_sql_where_abs{
	function __construct($field,$val,$cod=false,$querypart=false){
		$this->set_query_part($querypart);
		$this->field=$field;
		//$this->crit=mysql_real_escape_string($val);
		$this->crit=$val;
		$this->set_cod($cod);
	}
	
	function append_to_parameterized_sql($pq,&$tempSubSQLstr=""){
		
		if($this->pre_append_to_sql($tempSubSQLstr)){
			
			$pq->appendSQL($this->get_sql_other_prev(),$tempSubSQLstr);	
		}
		$pq->appendSQL(" ".$this->field.$this->cond."?",$tempSubSQLstr);
		$pq->addParam($this->crit);

		


		
		
		
	}

	function get_sql_in(){
		return $this->field.$this->cond."'".$this->real_escape_string($this->crit)."'";
		
		
	}
	
	
}
?>