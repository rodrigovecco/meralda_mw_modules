<?php
class mwmod_mw_db_sql_from_subquery extends mwmod_mw_db_sql_from_sql{
	public $subquery;
	function __construct($subquery,$cod,$querypart=false){
		$this->subquery=$subquery;
		$this->set_cod($cod);
		$this->set_query_part($querypart);
		$this->set_as_mode();
	}
	
	function get_sql_as_first(){
		return $this->get_sql();	
	}
 	function append_to_parameterized_sql($pq,&$tempSubSQLstr=""){
		if ($this->isFirst) {
			// Estamos en el primer FROM: no usamos JOIN
			$pq->appendSQL("(");
			$this->subquery->append_to_parameterized_sql($pq);
			$pq->appendSQL(")");

			if ($cod = $this->get_cod()) {
				$pq->appendSQL(" as $cod");
			}
			return;
		}

		// Modo JOIN (LEFT, RIGHT, etc.)
		$pq->appendSQL(" ".$this->join_mode." (");
		$this->subquery->append_to_parameterized_sql($pq);
		$pq->appendSQL(")");

		if ($cod = $this->get_cod()) {
			$pq->appendSQL(" as $cod");
		}

		if ($this->specialON) {
			$pq->appendSQL(" on (".$this->specialON.")");
		} elseif ($this->external_join_field) {
			$pq->appendSQL(" on (".$this->external_join_field."=".$this->get_cod().".".$this->inner_join_field.")");
		}
		/*
		$pq->appendSQL("(");
		$this->subquery->append_to_parameterized_sql($pq);

		$pq->appendSQL(")");
		if($cod=$this->get_cod()){
			$pq->appendSQL(" as $cod");	
		}
			*/

	}
	function get_sql_in(){
		
		return "(".$this->subquery->get_sql().")";
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