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
	
	function __construct($parent=false){
		if($parent){
			$this->setParent($parent);
		}
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
			if($this->connectiveOperator=="OR"){
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
			if($this->connectiveOperator=="OR"){
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
		
		if($this->connectiveOperator=="OR"){
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
		//$result = "(";
		$prevItemWasArray = false;
		$childrenmode=false;
		$arrayItems=array();
		$connectiveOperator="AND";
		foreach ($expression as $index => $item) {
            if (is_string($item)) {
                if ($index == 0) {
				    if ($item == "!") {
                        $this->negative=true;
						continue;
                    }
					if(isset($expression) && is_array($expression)){
						$this->parseExpressionSimpleByArray($expression);
						return "single";
					}else{
						//	
					}
					break;
                }
				$strItem = strtoupper(trim($item));
                if ($strItem == "AND" || $strItem == "OR") {
					$connectiveOperator=$strItem;
                }
                continue;
            }
            if (is_array($item)) {
				$item["_mwcop"]=$connectiveOperator;
				$arrayItems[]=$item;
				
				$connectiveOperator="AND";
            }
        }
		if(sizeof($arrayItems)>1){
			foreach ($arrayItems as $index => $data) {
				if($item=$this->addChild($this->newChild())){
					if($c=$data["_mwcop"]){
						$item->setConnectiveOperator($c);
						unset($data["_mwcop"]);
						$item->parseExpression($data);	
					}
				}
			}
			return "multiple";
		}elseif(sizeof($arrayItems)==1){
			$this->parseExpressionByArray($arrayItems[0]);
			return "singleByArray";
		}
		return "error";
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
			if($f=$this->getField()){
				$r["fildOK"]=true;	
			}
		}
		return $r;
			
	}
	function getValue(){
		
		//return $this->convertDateTimeToMySQLValue($this->value);
		return $this->value;	
	}
	/*
    function convertDatePartToISOValue($date) {
	    $dateParts = explode("/", $date);
	    return sprintf("%s-%s-%s", $dateParts[2], $dateParts[0], $dateParts[1]);
    }
    function convertDateTimeToMySQLValue($strValue) {
	    $result = $strValue;
    	if (preg_match("/^\d{1,2}\/\d{1,2}\/\d{4}$/", $strValue) === 1) {
	    	$result = $this->convertDatePartToISOValue($strValue);
	    }
	    else if (preg_match("/^\d{1,2}\/\d{1,2}\/\d{4} \d{2}:\d{2}:\d{2}\.\d{3}$/", $strValue) === 1) {
		    $spacePos = strpos($strValue, " ");
            $datePart = substr($strValue, 0, $spacePos);
		    $timePart = substr($strValue, $spacePos + 1);
		    $result = sprintf("%s %s", $this->convertDatePartToISOValue($datePart), $timePart);
	    }
	    return $result;
    }
	*/
	
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