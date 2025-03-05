<?php
class mwmod_mw_db_sql_group extends mwmod_mw_db_sql_querypart{
	function __construct($query=false){
		if($query){
			$this->set_query($query);	
		}
	}
	function add_group($sql,$cod=false){
		if(is_string($sql)){
			$item = new mwmod_mw_db_sql_group_group($sql,$cod,$this);
			return $this->add_item($item);
		}
	}
	function get_sql_start(){
		return " group by ";	
	}

	
}
?>