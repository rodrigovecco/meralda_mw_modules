<?php

abstract class mwmod_mw_db_sql_value_abs extends mwmod_mw_db_sql_abs{

	public $value=null;
	public $nullAllowed;
	public $paramQueryAllowed=true;
	//this values are set before generaring update or insert sql
	public $row; //not always present
	public $tblman; //not always present

	function setValue($value){
		$this->value=$value;
	}
	function setRow($row){
		//to work with updates
		$this->row=$row;
		$this->setTblMan($row->tblman);
		

	}
	function setTblMan($tblman){
		$this->tblman=$tblman;
		$this->set_dbman($this->tblman->dbman);
	}
	function setField($tblField){

		//$tblField mwmod_mw_db_tblfield
		$this->nullAllowed=$tblField->nullAllowed();


	}
	function getValueAsData(){
		return $this->getValue();
	}
	function getValue(){
		return $this->value;
	}
	function isParamQueryAllowed(){
		return $this->paramQueryAllowed;
	}
	function getValueForParamQuery(){
		return $this->getValue();
	}
	function getSQLValueStrQuoted(){
		//returns sql value
		if($this->isNull()){
			if($this->nullAllowed){
				return "NULL";
			}
			return "''";
		}
		$v=$this->getValue();
		return "'".$this->real_escape_string($v)."'";
		 
	}
	function isNull(){
		if(!isset($this->value)){
			return true;
		}
		if(isNull($this->value)){
			return true;
		}
		return false;
	}

	
	
	
}


?>