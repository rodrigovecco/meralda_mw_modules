<?php
class mwmod_mw_db_sql_select_select extends mwmod_mw_db_sql_querysubpart{
	public $dataType;
	function __construct($sql,$cod=false,$querypart=false){
		$this->sql=$sql;
		$this->set_cod($cod);
		$this->set_query_part($querypart);
	}
	
	
	function get_sql_in(){
		
		return $this->sql;
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