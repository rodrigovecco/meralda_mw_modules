<?php
class mwmod_mw_db_sql_where_wheresql extends mwmod_mw_db_sql_where_abs{
	function __construct($sql,$cod=false,$querypart=false){
		$this->sql=$sql;
		$this->set_cod($cod);
		$this->set_query_part($querypart);
	}
	
	 
	function get_sql_in(){
		
		return $this->sql;
	}
	
}
?>