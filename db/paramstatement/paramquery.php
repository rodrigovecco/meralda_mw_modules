<?php
class mwmod_mw_db_paramstatement_paramquery extends mw_apsubbaseobj{
	public $sql="";
	
	private $_params=array();
	function __construct(){

	}
	function addParam($value){
		$item=new mwmod_mw_db_paramstatement_param($value,$this);
		//echo $item->value."<br>";
		return $this->addParamItem($item);

	}
	
	function appendSQL($sql,&$tempSubSQLStr=""){
		if(!is_string($sql)){
			return false;
		}
		$this->sql.=$sql;
		if(!is_string($tempSubSQLStr)){
			$tempSubSQLStr="";
		}
		$tempSubSQLStr.=$sql;
		
		return true;
	}
	final function addParamItem($item){
		$this->_params[]=$item;
		return $item;
	}
	final function getParamsItems(){
		return $this->_params;
	}
	function getParams(){
		$r=array();
		if($items =$this->getParamsItems()){
			foreach ($items as $item) {
				$r[]=$item->getValue();
			}
		}
		return $r;
	}
	function getSQL(){
		return $this->sql;
	}
	function getDebugData(){
		$r=array();
		$r["sql"]=$this->getSQL();
		$r["params"]=array();
		if($items= $this->getParamsItems()){
			$x=1;
			foreach ($items as $item) {
				$r["params"][$x]=$item->getDebugData();
				$x++;
			}
			
		}
		$r["paramsValues"]=$this->getParams();
		return $r;
	}


}

