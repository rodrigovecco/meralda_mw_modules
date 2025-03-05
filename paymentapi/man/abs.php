<?php
abstract class  mwmod_mw_paymentapi_man_abs extends mw_apsubbaseobj{
	//may be used to load all apis, apis should me able to be created withput this manager
	private $_items;
	public $includeProduction=true;
	public $includeDebug=false;
	public $onlyEnabled=true;
	public $productionManCod;
	public $debugManCod;
	
	function loadItems(){
			
	}

	function getProductionItem(){
		return $this->get_item($this->productionManCod);	
	}
	function getDebugItem(){
		return $this->get_item($this->debugManCod);	
	}
	
	function getItems(){
		return $this->getItemsByFilter($this->includeProduction,$this->includeDebug,$this->onlyEnabled);
	}
	function setDebugMode($includeProduction=false,$includeDebug=true){
		$this->includeProduction=$includeProduction;
		$this->includeDebug=$includeDebug;
	}
	function getItemsByFilter($includeProduction=true,$includeDebug=false,$onlyEnabled=true){
		if(!$items=$this->get_items()){
			return false;	
		}
		$r=array();
		foreach($items as $cod=>$item){
			if($this->checkItemByFilter($item,$includeProduction,$includeProduction,$onlyEnabled)){
				$r[$cod]=$item;	
			}
		}
		if(sizeof($r)){
			return $r;	
		}
		return false;
	}
	function getItemByFilter($cod,$includeProduction=true,$includeDebug=false,$onlyEnabled=true){
		if(!$item=$this->get_item($cod)){
			return false;	
		}
		if($this->checkItemByFilter($item,$this->includeProduction,$this->includeDebug,$this->onlyEnabled)){
			return $item;	
		}
		return false;
		
	}
	function checkItemByFilter($item,$includeProduction=true,$includeDebug=false,$onlyEnabled=true){
		if($onlyEnabled){
			if(!$item->isEnabled()){
				return false;	
			}
		}
		if(!$includeProduction){
			if(!$item->isDebug()){
				return false;	
			}
		}
		if(!$includeDebug){
			if($item->isDebug()){
				return false;	
			}
		}
		return true;
	}
	
	
	function getItem($cod){
		return $this->getItemByFilter($cod,$this->includeProduction,$this->includeDebug,$this->onlyEnabled);
	}
	
	final function get_items(){
		$this->init_items();
		return $this->_items;
	}
	final function get_item($cod){
		if(!is_string($cod)){
			return false;	
		}
		if(!$cod){
			return false;	
		}
		$this->init_items();
		return $this->_items[$cod];
	}
	final function init_items(){
		if(isset($this->_items)){
			return;	
		}
		$this->_items=array();
		if($items=$this->loadItems()){
			foreach($items as $item){
				if($cod=$item->cod){
					$this->_items[$cod]=$item;	
				}
			}
		}
	}

	final function init(){
	}

}
?>