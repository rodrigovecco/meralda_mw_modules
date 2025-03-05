<?php
class mwmod_mw_db_sql_from extends mwmod_mw_db_sql_querypart{
	function __construct($query=false){
		if($query){
			$this->set_query($query);	
		}
	}
	function add_from_join_sql($sql,$as){
		if(is_string($sql)){
			$item = new mwmod_mw_db_sql_from_sql($sql,$as,$this);
			//$item->external_join_field=$external_field;
			//$item->inner_join_field=$inner_join_field;
			return $this->add_item($item);
		}
	}
	function add_subquery($subquery,$cod,$external_field,$inner_join_field="id"){
		$item = new mwmod_mw_db_sql_from_subquery($subquery,$cod,$this);
		$item->external_join_field=$external_field;
		$item->inner_join_field=$inner_join_field;
		return $this->add_item($item);

	}
	
	function add_from_join_external($tbl,$external_field,$inner_join_field="id",$as=false){
		if(is_string($tbl)){
			$item = new mwmod_mw_db_sql_from_tbl($tbl,$as,$this);
			$item->external_join_field=$external_field;
			$item->inner_join_field=$inner_join_field;
			return $this->add_item($item);
		}
	}
	function add_from_join($tbl,$external_field,$as=false){
		if(is_string($tbl)){
			$item = new mwmod_mw_db_sql_from_tbl($tbl,$as,$this);
			$item->external_join_field=$external_field;
			return $this->add_item($item);
		}
	}
	function add_from($tbl,$as=false){
		if(is_string($tbl)){
			$item = new mwmod_mw_db_sql_from_tbl($tbl,$as,$this);
			return $this->add_item($item);
		}
	}
	function get_sql_start(){
		return " from ";	
	}

	
}
?>