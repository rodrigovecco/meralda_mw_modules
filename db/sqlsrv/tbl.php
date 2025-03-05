<?php
class  mwmod_mw_db_sqlsrv_tbl extends mwmod_mw_db_tbl{
	function __construct($dbman,$tbl){
		$this->init($dbman,$tbl);	
	}
	function createFieldsManagers(){
		$sql = "SELECT * 
            FROM [INFORMATION_SCHEMA].COLUMNS 
            WHERE TABLE_NAME = '$this->tbl'";

		//$sql="SHOW FULL COLUMNS from ".$this->tbl;
       
		$r=array();
		if($query=$this->dbman->query($sql)){
			while ($data=$this->fetch_assoc($query)){
				
				if($id=$data["COLUMN_NAME"]){
					if($item=$this->createFieldMan($id,$data)){
						$r[$id]=$item;	
					}
				}
			}
		
		}
		return $r;

	}
	function createFieldMan($cod,$data=false){
		$item=new mwmod_mw_db_sqlsrv_tblfield($cod,$data,$this);
		return $item;	
	}
	function load_tbl_fields(){
		$sql = "SELECT * 
            FROM [INFORMATION_SCHEMA].COLUMNS 
            WHERE TABLE_NAME = '$this->tbl'";

		//$sql="SHOW FULL COLUMNS from ".$this->tbl;
       
		$r=array();
		if($query=$this->dbman->query($sql)){
			while ($data=$this->fetch_assoc($query)){
				
				if($id=$data["COLUMN_NAME"]){
					$r[$id]=$data;	
					
				}
			}
		
		}
		return $r;

		
	}

	
}

?>