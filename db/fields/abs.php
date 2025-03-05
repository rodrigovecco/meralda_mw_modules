<?php
//no usado!!!
abstract class mwmod_mw_db_fields_abs extends mw_apsubbaseobj{
	var $Type;
	var $Length=1;
	var $notNull=true;
	var $DEFAULT;
	var $isPRIMARY=false;
	var $COMMENT;
	private $info;//info from tblman->get_tbl_fields()
	
	//var $AUTO_INCREMENT=
	//UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE.
	/*
	data_type [NOT NULL | NULL] [DEFAULT default_value]
      [AUTO_INCREMENT] [UNIQUE [KEY] | [PRIMARY] KEY]
      [COMMENT 'string']
      [COLUMN_FORMAT {FIXED|DYNAMIC|DEFAULT}]
      [STORAGE {DISK|MEMORY|DEFAULT}]
      [reference_definition]
	*/
	
	private $tbldef;
	private $cod;
	function get_quoted_val($val){
		return "'".$this->escape_val($val)."'";
	}
	function escape_val($val){
		return $this->real_escape_string($val);
		//return mysql_real_escape_string($val);	
	}
	function is_text(){
		return false;
	}
	
	function is_numeric(){
		return false;	
	}
	//definition
	function get_sql_type_with_size(){
		$r=$this->get_sql_rel_name();
		if($v=$this->get_sql_size()){
			$r.=$v;	
		}
		return $r;
	}
	function get_sql_size(){
		return "(".$this->Length.")";	
	}
	function get_sql_type(){
		return $this->Type;	
	}
	function get_sql_rel_name(){
		return "`".$this->get_rel_name()."`";
	}
	function get_rel_name(){
		return $this->cod;	
	}
	function get_col_definition_sql(){
		if(!$list=$this->get_col_definition_sql_list()){
			return false;	
		}
		return implode(" ",$list);
	}
	function get_col_definition_sql_list(){
		$list=array();
		$list[]=$this->get_sql_rel_name();
		if($v=$this->get_sql_type_with_size()){
			$list[]=$v;	
		}
		$this->set_col_definition_sql_list_options($list);
		return $list;
	}
	function set_col_definition_sql_list_options_null_def(&$list){
		if($this->notNull){
			$list[]="NOT NULL";	
		}
		if(isset($this->DEFAULT)){
			if(is_null($this->DEFAULT)){
				$list[]="DEFAULT NULL";		
			}else{
				$list[]="DEFAULT ".$this->get_quoted_val($this->DEFAULT);		
			}
		}
			
	}
	
	function set_col_definition_sql_list_options(&$list){
		$this->set_col_definition_sql_list_options_null_def($list);
	}
	
	function get_create_tbl_sql(){
		return $this->get_col_definition_sql();	
	}
	final function __get_priv_tbldef(){
		return $this->tbldef; 	
	}
	final function __get_priv_cod(){
		return $this->cod; 	
	}
	
	final function set_tbldef($tbl){
		$this->tbldef=$tbl;
	}
	final function init($cod){
		$this->cod=$cod;
		$this->after_init();
	}
	function after_init(){
			
	}
	private $dbman;
	function real_escape_string($txt){
		if($man=$this->get_dbman()){
			return $man->real_escape_string($txt);	
		}
		return mysql_real_escape_string($val);	
	}
	function load_dbman(){
		if($this->tbldef){
			if($m=$this->tbldef->dbman){
				return $m;	
			}
		}
		
		return 	$this->mainap->get_submanager("db");	
	}
	function get_dbman(){
		return $this->__get_priv_dbman();	
	}
	final function set_dbman($man){
		$this->dbman=$man;
	}
	final function __get_priv_dbman(){
		if(!isset($this->dbman)){
			$this->dbman=$this->load_dbman();
		}
		return $this->dbman; 	
	}


	
	
	
}
?>