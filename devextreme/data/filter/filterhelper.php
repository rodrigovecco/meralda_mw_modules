<?php
//20240122
class mwmod_mw_devextreme_data_filter_filterhelper extends mw_apsubbaseobj{
	public $expressions=array();
	public $filterMainItem;
	private $queryHelper;
	
	function __construct(){
		
	}
	function aplay2Query($query){
		//$w=$query->where;
		$item=$this->getFilterMainItem();
		return $item->aplay2Query($query);
		
	}
	function getFieldFromCod($cod){
		if($this->queryHelper){
			return $this->queryHelper->getField($cod);	
		}
	}
	final function setQueryHelper($queryHelper){
		$this->queryHelper=$queryHelper;
	}
	final function __get_priv_queryHelper(){
		return $this->queryHelper;
	}
	
	function getFilterMainItem(){
		if(!$this->filterMainItem){
			$this->filterMainItem=new mwmod_mw_devextreme_data_filter_filteritem($this);
		}
		return $this->filterMainItem;
	}
	function addItemBySearchExpr($searchExpr,$searchValue,$searchOperation){
		$item=$this->getFilterMainItem();
		$sitem=$item->newChild();
		$sitem->setSearchExprMode($searchExpr,$searchValue,$searchOperation);
		$item->addChild($sitem);
		return $sitem;

	}
	
	function addItemsByArray($expression) {
		$item=$this->getFilterMainItem();
		$item->parseExpression($expression);
		return $item;
	}
	function getDebugData(){
		$r=array(
			"filter"=>$this->getFilterMainItem()->getDebugData(),
		);
		return $r;
			
	}
	

	
}
?>