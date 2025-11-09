<?php
//20240122
class mwmod_mw_devextreme_data_filter_filteritem extends mw_apsubbaseobj{
	private $items=array();
	public $negative=false;
	public $connectiveOperator="AND";
	
	public $parentItem;
	public $fieldName;
	public $errorMsg;
	public $dateProperty;
	public $clause="=";
	public $index=1;
	public $value;
	public $parseMode;
	public $datePropertyMode=false;
	private $_isOK;

	public $forceANDMode=false;
	
	function __construct($parent=false){
		if($parent){
			$this->setParent($parent);
		}
	}
	function isORMode(){
		
		if($this->forceANDMode){
			return false;	
		}
		if($this->connectiveOperator=="OR"){
			return true;	
		}
		return false;
	}
	function isTextCompare(){
		$clause=$this->clause;
		switch ($clause) {
			case "startswith":
			case "endswith":
			case "contains":
			case "notcontains": {
				return $clause;	
			}
		}
		return false;
	
	}
	
	function isNormalClause(){
		$clause=$this->clause;
		switch ($clause) {
			case "=":
			case "<>":
			case ">":
			case ">=":
			case "<":
			case "<=": {
				return $clause;	
			}
		}
		return "=";
	
	}
	
	/**
	 * @param mwmod_mw_db_sql_where $queryWhere 
	 * @return mixed|mixed 
	 */
	function aplay2QueryWhereAsChild($queryWhere){
		if(!$field=$this->getField()){
			return false;	
		}
		if(!$field->allowFilter()){
			return false;	
		}
		if($field->controlFilter()){
			return 	$field->controlFilterModeAplay2QueryWhereAsChild($this,$queryWhere);
		}
		
		$val=$this->getValue();
		if($c=$this->isTextCompare()){


			$w=$queryWhere->add_where_crit_like($field->getSqlExp(),$val);
			$w->setCompareMode($c);
			if($this->isORMode()){
				$w->set_or();	
			}
			if($this->negative){
				$w->not=true;	
			}
				
		}elseif($c=$this->isNormalClause()){
			if($field->isDateMode()){
				$w=$queryWhere->add_date_cond($field->getSqlExp(),$val);
				if(!$field->isDateOnly()){
					$w->include_hour=true;
				}
			}else{
				$w=$queryWhere->add_where_crit($field->getSqlExp(),$val);
			}
			$w->set_operator($c);
			if($this->isORMode()){
				$w->set_or();	
			}
			if($this->negative){
				$w->not=true;	
			}
			
		}
		return $w;
		
		
	}
	function aplay2Query($query){
		$w=$query->where;
		return $this->aplay2QueryWhere($w);
		
	}
	function aplay2QueryWhere($queryWhere){
		if(!$this->isOk()){
			return false;	
		}
		if($this->isSingle()){
			return $this->aplay2QueryWhereAsChild($queryWhere);	
		}else{
			return 	$this->aplay2QueryWhereAsParent($queryWhere);	
		}
	}
	function aplay2QueryWhereAsParent($queryWhere){
		if(!$items=$this->getChildren()){
			return false;	
		}
		$subwhere=$queryWhere->add_sub_where();
		if($this->negative){
			$subwhere->not=true;	
		}
		
		if($this->isORMode()){
			$subwhere->set_or();	
		}
		
		foreach($items as $item){
			$item->aplay2QueryWhere($subwhere);	
		}
		return $subwhere;
		
		
		
	}
	
	

	function getFieldCod(){
		return $this->getFieldName();	
	}
	function getField(){
		return $this->getFieldFromCod($this->getFieldCod());
	}
	function getFieldFromCod($cod){
		if($this->parentItem){
			return $this->parentItem->getFieldFromCod($cod);	
		}
	}
	
	function setConnectiveOperator($op){
		$this->connectiveOperator=$op;
	}
	function newChild(){
		return new mwmod_mw_devextreme_data_filter_filteritem();
		
	}
	final function addChild($item){
		$index=sizeof($this->items)+1;
		$item->setParent($this,$index);
		$this->items[$index]=$item;
		return $item;
		
	}
	final function getChildren(){
		if(sizeof($this->items)){
			return $this->items;	
		}
	}
	function parseExpressionByArray($expression) {
		if (!is_array($expression) || empty($expression)) {
			return "error";
		}

		// Detecta negación al inicio
		if ($expression[0] === "!") {
			$this->negative = true;
			array_shift($expression); // quitamos el "!"
		}

		$subExpressions = [];
		$currentOperator = "AND";

		foreach ($expression as $item) {
			if (is_string($item)) {
				$upper = strtoupper(trim($item));
				if ($upper === "AND" || $upper === "OR") {
					$currentOperator = $upper;
				}
				//die($currentOperator);
				continue;
			}

			if (is_array($item)) {
				$subExpressions[] = [
					"op" => $currentOperator,
					"data" => $item
				];
				$currentOperator = "AND"; // por defecto después de cada operador
			}
		}

		$count = count($subExpressions);

		if ($count > 1) {
			foreach ($subExpressions as $sub) {
				if ($child = $this->addChild($this->newChild())) {
					$child->setConnectiveOperator($sub["op"]);
					$child->parseExpression($sub["data"]);
				}
			}
			return "multiple";
		} elseif ($count === 1) {
			return $this->parseExpressionByArray($subExpressions[0]["data"]);
		} else {
			// Si no se encontraron subarrays, intentamos tratarlo como expresión simple
			return $this->parseExpressionSimpleByArray($expression) ? "single" : "error";
		}
	}
	
	function setFieldName($fieldName){
		$this->fieldName=trim($fieldName);	
	}
	function parseFieldName($field){
		//no probado
        $fieldParts = explode(".", $field);
        $fieldName = trim($fieldParts[0]);
        if (count($fieldParts) == 2) {
            $dateProperty = trim($fieldParts[1]);
            switch ($dateProperty) {
                case "year":
                case "month":
                case "day": {
					$this->setDatePropertyMode($dateProperty);
                    break;
                }
                case "dayOfWeek": {
					$this->setDatePropertyMode($dateProperty);
                    break;
                }
                default: {
					$this->setDatePropertyMode($dateProperty);
					$this->errorMsg="The \"".$dateProperty."\" command is not supported";
                }
            }
        }
		$this->setFieldName($fieldName);
        return $this->fieldName;
	}
	function setDatePropertyMode($dateProperty){
		$this->dateProperty=trim($dateProperty);
		$this->datePropertyMode=true;
	}
	function setValue($value){
		$this->value=$value;
	}
	public $debugExpresion;
    function parseExpressionSimpleByArray($expression) {
		
        $itemsCount = count($expression);
		$this->debugExpresion=$expression;
        $fieldName =$this->parseFieldName(trim($expression[0]));
        if ($itemsCount == 2) {
			$this->setValue($expression[1]);
        }else if ($itemsCount == 3) {
			$this->setClause($expression[1]);
			$this->setValue($expression[2]);
        }elseif ($itemsCount == 4) {
			$this->setClause($expression[1]);
			$this->setValue($expression[2]);
			//$this->setConnectiveOperator($expression[3]);
		}
		return true;
    }
	function setClause($clause){
		$this->clause=trim($clause);
	}
	function setSearchExprMode($searchExpr,$searchValue,$searchOperation){
		$this->parseMode="single";
		$this->setFieldName($searchExpr);
		$this->setClause($searchOperation);
		$this->setValue($searchValue);



	}
	
	function parseExpression($expression){
		if(is_array($expression)){
			$this->parseMode=$this->parseExpressionByArray($expression);	
		}
	}
	final function setParent($parent,$index=1){
		$this->parentItem=$parent;
		$this->index=$index;
	}
	function getDebugData(){
		$r=array(
			"index"=>$this->index,
			"parseMode"=>$this->parseMode,
			"mode"=>$this->getMode(),
			"isOk"=>$this->isOk(),
		);
		if(is_array($this->debugExpresion)){
			$r["debugExpresion"]=@json_encode($this->debugExpresion);	
		}
		if($this->hasChildren()){
			$r["children"]=array();
			if($items=$this->getChildren()){
				foreach($items as $index=>$item){
					$r["children"][$index]=$item->getDebugData();
				}
			}
		}else{
			
			$r["value"]=$this->getValue();
			$r["fildName"]=$this->getFieldName();
			$r["clause"]=$this->clause;
			if($f=$this->getField()){
				$r["fildOK"]=true;	
			}else{
				$r["fildOK"]=false;	
			}
		}
		return $r;
			
	}
	function getValue(){
		
		//return $this->convertDateTimeToMySQLValue($this->value);
		return $this->value;	
	}
	
	
	final function isOk(){
		if(!isset($this->_isOK)){
			if($this->isOkCheck()){
				$this->_isOK=true;	
			}else{
				$this->_isOK=false;	
			}
		}
		return $this->_isOK;
	}
	function isOkSingleMode(){
		if(!$this->getFieldName()){
			return false;	
		}
		if($this->isDatePropertyMode()){
			if(!$this->getDateProperty()){
				return false;	
			}
		}
		return true;
	}
	function getDateProperty(){
		$dateProperty = $this->dateProperty;
		$r=false;
		switch ($dateProperty) {
			case "year":
			case "month":
			case "day":
			case "dayOfWeek": {
				$r= $dateProperty;
			}
		}
		return $r;
		
	}
	function isDatePropertyMode(){
		return $this->datePropertyMode;	
	}
	function getFieldName(){
		return $this->fieldName;	
	}
	
	function isOkCheck(){
		if($this->isSingle()){
			return $this->isOkSingleMode();
		}else{
			return $this->isOkParentMode();
		}
			
	}
	function isOkParentMode(){
		if(!$items=$this->getChildren()){
			return false;	
		}
		$num=0;
		foreach($items as $item){
			if($item->isOk()){
				$num++;	
			}
		}
		return $num;
	}
	
	function getMode(){
		if($this->isSingle()){
			return "child";	
		}else{
			return "parent";	
		}
	}
	
	function isSingle(){
		if($this->getChildren()){
			return false;	
		}
		return true;
	}
	function hasChildren(){
		if($this->isSingle()){
			return false;	
		}
		return true;
	}

	
}
?>