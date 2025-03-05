<?php
abstract class mwmod_mw_db_sql_where_abs extends mwmod_mw_db_sql_querysubpart{
	
	var $general_cond="AND";
	var $field;
	var $crit;
	var $cond="=";
	
	var $not=false;
	public $notIn=false;

	
	function get_valid_operators(){
		$valid=explode(",","<>,<,>,<=,>=,=");
		$r=array();
		foreach($valid as $o){
			$r[$o]=$o;	
		}
		return $r;
	}
	function set_operator($op){
		$valid=$this->get_valid_operators();
		if(!$op){
			return false;
		}
		if($o=$valid[$op]){
			$this->cond=$o;
			return $o;
		}
		return false;
		
	}
	function set_operator_different(){
		return $this->set_operator("<>");	
	}
	function set_operator_equal(){
		return $this->set_operator("=");	
	}
	function set_operator_greater_or_equal(){
		return $this->set_operator(">=");	
	}
	function set_operator_less_or_equal(){
		return $this->set_operator("<=");	
	}
	function set_operator_less(){
		return $this->set_operator("<");	
	}
	function set_operator_greater(){
		return $this->set_operator(">");	
	}
	
	
	function get_sql_other_prev(){
		return " ".$this->general_cond;
	}
	
	function set_and(){
		$this->general_cond="AND";	
	}
	function set_or(){
		$this->general_cond="OR";	
	}
	 
	function get_sql_in(){
		
		return $this->sql;
	}
	
	function get_sql(){
		$sql=$this->get_sql_in();
		if($this->not){
			return " NOT (".$sql.")";	
		}
		return "(".$sql.")";
	}
	
}
?>