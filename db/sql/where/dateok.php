<?php
class mwmod_mw_db_sql_where_dateok extends mwmod_mw_db_sql_where_abs{
	function __construct($field,$cod=false,$querypart=false){
		$this->set_query_part($querypart);
		$this->field=$field;
		$this->set_cod($cod);
		$this->set_operator(">");
	}
	function get_sql_formated_left(){
		return $this->field;
		
	}
	function append_to_parameterized_sql($pq,&$tempSubSQLstr=""){
		if(!$this->is_ok()){
			return "";	
		}
		if($this->pre_append_to_sql($tempSubSQLstr)){
			
			$pq->appendSQL($this->get_sql_other_prev(),$tempSubSQLstr);	
		}
		if($this->dbModeCheckSQLsrv()){
			//only allows op >!!!!


			$pq->appendSQL(" TRY_CONVERT(datetime, ".$this->field.") IS NOT NULL",$tempSubSQLstr);
			

		}else{
			
			$pq->appendSQL(" ".$this->get_sql_in(),$tempSubSQLstr);
			

			
		}



		
		
		
	}
	function get_sql_formated_right(){
		return "0000-00-00";	
	}
	function get_sql_in(){
		if(!$this->is_ok()){
			return "";	
		}
		return $this->get_sql_formated_left().$this->cond."'".$this->real_escape_string($this->get_sql_formated_right())."'";
		
	}
	
}
?>