<?php
class  mwmod_mw_db_tblfield extends mw_apsubbaseobj{
	private $tbl;
	private $cod;
	//private $fieldMan; en el futuro puede crear un manejador según tipo de datos
	private $typeInfo;
	public $info=array();
	function __construct($cod,$info,$tbl){
		$this->init($cod,$tbl);
		$this->setInfo($info);
	}
	function isNum(){
		if(!$t=$this->getTypeInfo("type")){
			return false;	
		}
		$list=strtolower("TINYINT,MEDIUMINT,INTEGER,BIGINT,SMALLINT,DECIMAL,NUMERIC,FLOAT,REAL,INT,DEC,FIXED");
		$a=explode(",",$list);
		if(in_array($t,$a)){
			return true;	
		}
		return false;
	}
	//20231206
	function nullAllowed(){
		if(!$v=strtolower($this->info["Null"]??"")){
			return false;
		}
		if($v=="yes"){
			return true;
		}
		return false;
	}   
	function isBool(){
		if(!$t=$this->getTypeInfo("type")){
			return false;	
		}
		if($t=="boolean"){
			return true;
		}
		if($t=="bool"){
			return true;
		}
		if($t=="tinyint"){
			if($this->getTypeInfo("spec")==1){
				return true;
			}
		}
		return false;
		
	}
	function isDec(){
		if(!$t=$this->getTypeInfo("type")){
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
		if(!$t=$this->getTypeInfo("type")){
			return false;	
		}
		$a=array("date","datetime","timestamp");
		if(in_array($t,$a)){
			return true;	
		}
		return false;
	}
	function setInfo($info){
		if(is_array($info)){
			$this->info=$info;	
		}
	}
	function getInfoData($cod=false){
		if(!$cod){
			return $this->info;
		}
		return $this->info[$cod]??null;
	}
	
	function getDebugData(){
		$r=array(
			"cod"=>$this->cod,
			"isDate"=>$this->isDate(),
			"isBool"=>$this->isBool(),
			"isDec"=>$this->isDec(),
			"null"=>$this->nullAllowed(),
			
			"isNum"=>$this->isNum(),
			
			"comment"=>$this->getComment(),
			"info"=>$this->info,
			"type"=>$this->getTypeInfo(false)
		);	
		return $r;
			
	}
	function getComment(){
		return $this->info["Comment"]??null;	
	}
	function getInfo(){
		$r=array(
			"cod"=>$this->cod,
			"info"=>$this->info,
			"type"=>$this->getTypeInfo(false)
		);	
		return $r;
	}
	function getTypeInfo($cod="fulltype"){
		$r=$this->__get_priv_typeInfo();
		if(!$cod){
			return $r;	
		}
		return $r[$cod]??null;
	}
	final function __get_priv_typeInfo(){
		if(!isset($this->typeInfo)){
			if(!$this->typeInfo=$this->loadTypeInfo()){
				$this->typeInfo	=array();
			}
		}
		
		return $this->typeInfo; 	
	}
	function loadTypeInfo(){
		$r=array();
		if(!$type=$this->info["Type"]??null){
			return $r;	
		}
		$type=strtolower($type);
		$r["fulltype"]=$type;
		$coltype=$type;
		$r["type"]=$coltype;
		$r["spec"]="";
		if($pos=strpos($type,"(")){
			$coltype=substr($type,0,$pos);
			$r["type"]=$coltype;
			$typespec=trim(substr($type,$pos),"()");
			$r["spec"]=$typespec;
			
			
		}
		return $r;
		
	}
	function getFullName(){
		return $this->tbl->tbl.".".$this->cod;
	}
	final function init($cod,$tbl){
		$this->cod=$cod;
		$this->tbl=$tbl;
	
	}
	final function __get_priv_tbl(){
		return $this->tbl; 	
	}
	final function __get_priv_cod(){
		return $this->cod; 	
	}

	function __toString(){
		return $this->cod;	
	}
	
}

?>