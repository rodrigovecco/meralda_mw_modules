<?php
class  mwmod_mw_db_sqlsrv_tblfield extends mwmod_mw_db_tblfield{
	
	function __construct($cod,$info,$tbl){
		$this->init($cod,$tbl);
		$this->setInfo($info);
	}
	function isNum(){
		if(!$t=$this->getInfoData("DATA_TYPE")){
			return false;	
		}
		$list=strtolower("TINYINT,MEDIUMINT,INTEGER,BIGINT,SMALLINT,DECIMAL,NUMERIC,FLOAT,REAL,INT,DEC,FIXED");
		$a=explode(",",$list);
		if(in_array($t,$a)){
			return true;	
		}
		return false;
	}
	function nullAllowed(){
		if(!$v=$this->getInfoData("IS_NULLABLE")){
			return false;
		}
		$v=strtolower($v);
		if($v=="yes"){
			return true;
		}
		return false;
	}   
	function isDec(){
		if(!$t=$this->getInfoData("DATA_TYPE")){
			return false;	
		}
		$list=strtolower("DECIMAL,FLOAT,REAL,DEC,FIXED");
		$a=explode(",",$list);
		if(in_array($t,$a)){
			return true;	
		}
		return false;
			
	}
	function isDate(){
		if(!$t=$this->getInfoData("DATA_TYPE")){
			return false;	
		}
		$a=array("date","datetime","timestamp");
		if(in_array($t,$a)){
			return true;	
		}
		return false;
	}
	function isBool(){
		//revisar!
		if(!$t=$this->getInfoData("DATA_TYPE")){
			return false;	
		}
		if($t=="boolean"){
			return true;
		}
		if($t=="bool"){
			return true;
		}
		
		return false;
		
	}
	
}

?>