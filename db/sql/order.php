<?php
class mwmod_mw_db_sql_order extends mwmod_mw_db_sql_querypart{
	function __construct($query=false){
		if($query){
			$this->set_query($query);	
		}
	}
	function add_order_desc($sql,$cod=false){
		if(is_string($sql)){
			$item = new mwmod_mw_db_sql_order_order($sql,$cod,$this);
			$item->set_desc();
			return $this->add_item($item);
		}
	}
	
	function add_order($sql,$cod=false){
		if(is_string($sql)){
			$item = new mwmod_mw_db_sql_order_order($sql,$cod,$this);
			return $this->add_item($item);
		}
	}
	function get_sql_start(){
		return " order by ";	
	}

	
}
?>