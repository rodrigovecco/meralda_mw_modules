<?php
/** @package  */
class mwmod_mw_db_sql_from extends mwmod_mw_db_sql_querypart{
	function __construct($query=false){
		if($query){
			$this->set_query($query);	
		}
	}
	function hasAlias($alias){
		if($items=$this->get_items_ok()){
			foreach($items as $item){
				if($item->checkAlias($alias)){
					return true;	
				}
				
			}
		}
		return false;
	}
	function add_from_join_sql($sql,$as){
		if(is_string($sql)){
			$item = new mwmod_mw_db_sql_from_sql($sql,$as,$this);
			//$item->external_join_field=$external_field;
			//$item->inner_join_field=$inner_join_field;
			return $this->add_item($item);
		}
	}
	/**
	 * @param mixed $subquery 
	 * @param mixed $cod 
	 * @param mixed $external_field 
	 * @param string $inner_join_field 
	 * @return mwmod_mw_db_sql_from_subquery 
	 */
	function add_subquery($subquery,$cod,$external_field,$inner_join_field="id"){
		$item = new mwmod_mw_db_sql_from_subquery($subquery,$cod,$this);
		$item->external_join_field=$external_field;
		$item->inner_join_field=$inner_join_field;
		return $this->add_item($item);

	}
	
	/**
	 * @param string $tbl 
	 * @param string $external_field 
	 * @param string $inner_join_field 
	 * @param string $as 
	 * @return mwmod_mw_db_sql_from_tbl|void 
	 */
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
	/**
	 * @param mixed $tbl 
	 * @param string|false $as 
	 * @return mwmod_mw_db_sql_from_tbl|void 
	 */
	function add_from($tbl,$as=false){
		if(is_string($tbl)){
			$item = new mwmod_mw_db_sql_from_tbl($tbl,$as,$this);
			return $this->add_item($item);
		}
		if(is_object($tbl)){
			if($tbl instanceof mwmod_mw_db_sql_query){
				if(!$as){
					$as="subquery";
				}
				$item = new mwmod_mw_db_sql_from_subquery($tbl,$as,$this);
				return $this->add_item($item);
			}
		}
	}
	function get_sql_start(){
		return " from ";	
	}

	
}
?>