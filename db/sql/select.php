<?php
class mwmod_mw_db_sql_select extends mwmod_mw_db_sql_querypart{
	function __construct($query=false){
		if($query){
			$this->set_query($query);	
		}
	}
	function add_count($field="id",$as="num",$distinct=true){
		$sql="count(";
		if($distinct){
			$sql.="DISTINCT ";	
		}
		$sql.=$field.")";
		return $this->add_select($sql,$as);
	}
	function add_group_concat($field="id",$as="ids_list",$distinct=true){
		$sql="group_concat(";
		if($distinct){
			$sql.="DISTINCT ";	
		}
		$sql.=$field.")";
		return $this->add_select($sql,$as);
	}
	
	
	function add_select($sql,$as=false){
		$item = new mwmod_mw_db_sql_select_select($sql,$as,$this);
		return $this->add_item($item);
	}
	function get_sql_no_items(){
		///20250727
		//BUG fix cuando hay varios froms y no hay selects

		$from = $this->query->from;
		$items = $from->get_items(); // Suponiendo que tienes este método en el `from`

		if (count($items) > 1) {
			// Elegimos el primer alias o tabla como base del *
			foreach ($items as $item) {
				if ($alias = $item->get_alias_or_table()) {
					return "select {$alias}.* ";
				}
			}
		}


		return "select * ";	
	}
	function get_sql_start(){
		return "select ";	
	}

	
}
?>