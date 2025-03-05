<?php

abstract class mwmod_mw_db_sql_abs extends mw_apsubbaseobj{
	private $dbman;
	private $helper;
	var $debug_mode=false;

	function dbModeCheck($mode){
		$man=$this->get_dbman();
		return $man->dbModeCheck($mode);	
	}
	function dbModeCheckMySQL(){
		return $this->dbModeCheck("mysql");
	}
	function dbModeCheckSQLsrv(){
		return $this->dbModeCheck("sqlsrv");
	}
	function real_escape_string($txt){
		$man=$this->get_dbman();
		return $man->real_escape_string($txt);	
	}
	function load_dbman(){
		return 	$this->mainap->get_submanager("db");	
	}
	function get_dbman(){
		return $this->__get_priv_dbman();	
	}
	final function set_dbman($man){
		$this->dbman=$man;
	}
	final function __get_priv_helper(){
		if(!isset($this->helper)){
			$this->helper=new mwmod_mw_ap_helper();
		}
		return $this->helper; 	
	}

	final function __get_priv_dbman(){
		if(!isset($this->dbman)){
			$this->dbman=$this->load_dbman();
		}
		return $this->dbman; 	
	}

	
	
}


?>