<?php
class mwmod_mw_db_sql_order_order extends mwmod_mw_db_sql_querysubpart{
	var $order="ASC";
	function __construct($sql,$cod=false,$querypart=false){
		$this->sql=$sql;
		$this->set_cod($cod);
		$this->set_query_part($querypart);
	}
	function set_asc(){
		$this->order="ASC";	
	}
	function set_desc(){
		$this->order="DESC";	
	}

	function get_sql_in(){
		$r=$this->sql;
		if($this->order){
			$r.=" ".$this->order;	
		}
		return $r;
	}
	function get_sql(){
		
		$sql=$this->get_sql_in();
		return $sql;
	}
	
}
?>