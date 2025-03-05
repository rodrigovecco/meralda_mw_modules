<?php
class  mwmod_mw_util_itemsbycod extends mw_apsubbaseobj{
	var $items=array();
	public $addItemsAssocMode=false;
	public $isEnabledMethod;
	public $defaultItem;
	public $itemNameMethod="get_name";
	var $getItemCodMethod="get_id,get_cod";
	function __construct(){
		
	}
	function getItemsNum(){
		return sizeof($this->items);
	}
	function addItemsByDataArray($allData,$codField="id",$nameField="name"){
		//mw_array2list_echo($allData);
		if(is_array($allData)){
			foreach($allData as $d){
				$this->addItemByData($d,$codField,$nameField);
			}
		}
	}
	function addItemByData($data,$codField="id",$nameField="name"){
		
		if(!is_array($data)){
			return false;
		}
		if(!$codField){
			return false;
		}
		if(!$cod=$data[$codField]??null){
			return false;
		}

		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;
		}
		
		$item=new mwmod_mw_util_itemsbycod_data($cod,$data);
		if($nameField){
			if($name=$data[$nameField]??null){
				$item->name=$name;
			}
		}
		return $this->add_item($item);

	}
	function getDefaultItem(){
		if(isset($this->defaultItem)){
			return $this->defaultItem;	
		}
		if($item=$this->loadDefaultItem()){
			return $this->setDefaultItem($item);
		}
	}
	function loadDefaultItem(){
		return false;	
	}
	function setDefaultItem($item){
		$this->defaultItem=$item;
		return $item;
	}
	function getItem($cod){
		return $this->get_item($cod);	
	}
	function getItemsNames($method=false){
		if(!$method){
			$method=$this->itemNameMethod;	
		}
		if($list=$this->getItemsIfAny()){
			$r=array();
			foreach($list as $id=>$item){
				if(method_exists($item,$method)){
					$r[$id]=$item->$method();	
				}
			}
			return $r;
		}
	}

	
	function getItemsIfAny(){
		if($list=$this->getItems()){
			if(sizeof($list)){
				return $list;	
			}
		}
	}
	function getItems(){
		return $this->get_items();
	}
	function getItemsEnabled($opossite=false){
		return $this->getItemsByMethod($this->isEnabledMethod,$opossite);
	}
	function getItemsByMethod($method,$opossite=false){
		if(!$method){
			return false;	
		}
		$r=array();
		if($items=$this->get_items()){
			foreach($items as $id=>$item){
				$ok=-1;
				if($item->$method()){
					$ok=1;
				}
				if($opossite){
					$ok=$ok*-1;
				}
				if($ok==1){
					$r[$id]=$item;
				}
			}
		}
		
		return $r;
	}
	
	
	function addItemsAssoc($items){
		$n=0;
		if(is_array($items)){
			foreach($items as $id=>$item){
				if($this->add_itemByCod($id,$item)){
					$n++;	
				}
			}
		}
		return $n;
	}
	function addItemsUnssoc($items){
		$n=0;
		if(is_array($items)){
			foreach($items as $id=>$item){
				if($this->add_item($item)){
					$n++;	
				}
			}
		}
		return $n;
	}
	function addItems($items){
		if($this->addItemsAssocMode){
			return $this->addItemsAssoc($items);
				
		}
		return $this->addItemsUnssoc($items);
	}
	
	function get_items(){
		return $this->items;
	}
	
	function get_item($cod){
		if(!$cod){
			return false;	
		}
		if(isset($this->items[$cod])){
			return $this->items[$cod];	
		}
		
	}
	function add_item($item){
		$cod=$this->get_item_cod($item);
		return $this->add_itemByCod($cod,$item);
	}
	function get_item_cod($item){
		if($this->getItemCodMethod){
			$methods=explode(",",$this->getItemCodMethod);
			foreach($methods as $m){
				if($m=trim($m)){
					if(method_exists($item,$m)){
						if($c=$item->$m()){
							return $c;	
						}
					}
				}
				
			}
		}
	}
	function add_itemByCod($cod,$item){
		if(!$cod){
			return false;	
		}
		$this->items[$cod]=$item;
		return $item;	
	}
	
}
?>