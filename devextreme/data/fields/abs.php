<?php
abstract class mwmod_mw_devextreme_data_fields_abs extends mw_apsubbaseobj{
	private $cod;
	private $queryHelper;
	private $sqlExp;
	public $dateMode=false;
	public $dataType;
	public $allowFilter=true;
	public $allowSort=true;
	public $name;
	public $dxCol;
	function jsFormatValue($val){
		if($this->isDateMode()){
			if(!$val){
				return "";	
			}
			if($val=="0000-00-00"){
				return "";	
			}
			if($val=="0000-00-00 00:00:00"){
				return "";	
			}
			if(!$t=strtotime($val)){
				return "";	
			}
			return date("Y/m/d H:i:s",$t);
	
		}
		return $val;
	}
	function addDxCol($dg){
		$cod=$this->cod;
		$name=$this->getName();
		if($this->isBool()){
			$col=$dg->add_column_boolean($cod,$name);	
		}elseif($this->isDec()){
			$col=$dg->add_column_number($cod,$name);
			$col->js_data->set_prop("format","fixedPoint");
			$col->js_data->set_prop("precision",2);
		}elseif($this->isInt()){
			$col=$dg->add_column_number($cod,$name);
		}elseif($this->isDateMode()){
			$col=$dg->add_column_date($cod,$name);
			$col->js_data->set_prop("format","shortDate");
		}else{
			$col=$dg->add_column_string($cod,$name);	
		}
		$this->dxCol=$col;
		return $col;
	}
	
	function add2QuerySelect($query){
		$s=$query->select->add_select($this->getSqlExp(),$this->cod);
		return $s;
	}
	function isNum(){
		if($this->isBool()){
			return true;	
		}
		if($this->isDec()){
			return true;	
		}
		if($this->isInt()){
			return true;	
		}
		return false;
	}
	function isInt(){
		if($this->dataType=="int"){
			return true;	
		}
		return false;
		
	}
	function isBool(){
		if($this->dataType=="bool"){
			return true;	
		}
		return false;
		
	}
	function isDec(){
		if($this->dataType=="dec"){
			return true;	
		}
		return false;
			
	}

	function getName(){
		if($this->name){
			return $this->name;	
		}
		return $this->cod;
	}
	
	function setOptionsByField($field){
		// $field mwmod_mw_db_tblfield;
		if(!$field){
			return false;	
		}
		if($field->isDate()){
			$this->setDateMode();
		}else{
			if($field->isBool()){
				$this->dataType="bool";	
			}elseif($field->isDec()){
				$this->dataType="dec";	
			}elseif($field->isNum()){
				$this->dataType="int";	
			}
		}
		return true;
	}
	
	function allowFilter(){
		if(!$this->isOK()){
			return false;	
		}
		return $this->allowFilter;
	}
	function allowSort(){
		if(!$this->isOK()){
			return false;	
		}
		return $this->allowSort;
	}
	function setDateMode($mode="dateOnly"){
		$this->dateMode=$mode;
		$this->dataType="date";
	}
	function isDateOnly(){
		if($this->dateMode=="dateOnly"){
			return true;	
		}
		return false;
	}
	function isDateMode(){
		return $this->dateMode;	
	}
	function isDatetime(){
		if($this->dateMode){
			return true;	
		}
		return false;
	}
	function getDebugData(){
		$r=array(
			"cod"=>$this->cod,
			"exp"=>$this->getSqlExp(),
			"name"=>$this->getName(),
			"dateMode"=>$this->isDateMode(),
			"isBool"=>$this->isBool(),
			"isDec"=>$this->isDec(),
			"isInt"=>$this->isInt(),
			"isNum"=>$this->isNum(),
		);
		return $r;
	}
	function getSqlExp(){
		$r=$this->getSqlExpRaw();
		return $r;
			
	}
	function getSqlExpRaw(){
		if($s=$this->sqlExp){
			return $s;	
		}
		return $this->cod;
	}
	final function setCod($cod){
		$this->cod=$cod;
	}
	final function setSQLExp($sqlExp){
		$this->sqlExp=$sqlExp;
	}
	final function setQueryHelper($queryHelper){
		$this->queryHelper=$queryHelper;
	}
	final function __get_priv_queryHelper(){
		return $this->queryHelper;
	}
	final function __get_priv_cod(){
		return $this->cod;
	}
	final function __get_priv_sqlExp(){
		return $this->sqlExp;
	}
	
	function isOK(){
		return true;	
	}
	//especiales para tomar el control de los metodos aplay2QueryWhereAsChild de mwmod_mw_devextreme_data_filter_filteritem
	//20210222
	function controlFilter(){
		return false;	
	}
	function controlFilterModeAplay2QueryWhereAsChild($filterItem,$queryWhere){
		//extender
	}
	
	
}
?>