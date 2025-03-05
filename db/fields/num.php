<?php
abstract class mwmod_mw_db_fields_num extends mwmod_mw_db_fields_abs{
	var $UNSIGNED=false;
	
	function set_col_definition_sql_list_options(&$list){
		if($this->UNSIGNED){
			$list[]="UNSIGNED";	
		}
		$this->set_col_definition_sql_list_options_null_def($list);
	}
	function escape_val($val){
		return $val+0;	
	}
	
	function is_numeric(){
		return true;	
	}
	function after_init(){
		$this->DEFAULT=0;	
	}

	
	
	
}
?>