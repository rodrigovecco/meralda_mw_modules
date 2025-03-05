<?php
//20210222 repoder
class mwmod_mw_db_sql_having extends mwmod_mw_db_sql_where{
	function __construct($query=false){
		if($query){
			$this->set_query($query);	
		}
	}
	function get_sql_start(){
		return " having ";	
	}

	
}
?>