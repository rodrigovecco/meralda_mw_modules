<?php
//20240014
class mwmod_mw_devextreme_data_queryhelper extends mw_apsubbaseobj{
	public $filter;
	public $loadOptions;
	public $filterInput;
	public $query;
	public $maxPageSize=1000;
	public $defPageSize=20;
	public $sorted=false;
	public $noLimit=false;

	public $allowLoadAllWhenOptionTakeNotPresent=false;//used for ui with selection no pages mode
	
	private $fields=array();
	private $autoSetOptions=true;

	public $createFiltersBysearchExprEnabled=false; //enable for autocomplete and others
	
	function __construct(){
		
	}
	function jsFormatData(&$data){
		if(!is_array($data)){
			return false;	
		}
		$cods=array_keys($data);
		foreach($cods as $c){
			if($f=$this->getField($c)){
				$data[$c]=$f->jsFormatValue($data[$c]);	
			}
		}
		return $data;
	}
	function aplay2Query($query){
		$this->query=$query;
		$this->filterQuery($query);
		$this->sortQuery($query);
		$this->limitQuery($query);
		
	}
	function limitQuery($query){
		if($this->noLimit){
			return;	
		}
		if($this->getLoadOptions("isLoadingAll")){
			return;	
		}
		
		
		if(!$take=intval(abs($this->getLoadOptions("take")))+0){
			if($this->allowLoadAllWhenOptionTakeNotPresent){
				return;
			}
			$take=$this->defPageSize;	
		}
		if($this->maxPageSize){
			if($take>$this->maxPageSize){
				$take=$this->maxPageSize;	
			}
		}
		$skip=intval(abs($this->getLoadOptions("skip")+0));
		$query->limit->set_limit($take,$skip);
			
	}
	
	function filterQuery($query){
		if($this->filter){
			$this->filter->aplay2Query($query);
		}
			
	}
	function sortQuery($query){
		if(!$input=$this->getLoadOptions("sort")){
			return false;	
		}
		if(is_string($input)){
			$input=@json_decode($input,true);	
		}
		if(!is_array($input)){
			return false;	
		}
		if(!sizeof($input)){
			return false;	
		}
		$r=array();
		foreach($input as $d){
			if($s=$this->sortQueryEntry($query,$d)){
				$r[]=$s;	
			}
		}
		if(!sizeof($r)){
			return false;	
		}
		return $r;
		
			
	}
	function sortQueryEntry($query,$input){
		if(!is_array($input)){
			return false;	
		}
		if(!$field=$this->getField($input["selector"])){
			return false;		
		}
		if(!$field->allowSort()){
			return false;	
		}
		$s=$query->order->add_order($field->getSqlExp());
		if($input["desc"]){
			$s->set_desc();	
		}
		$this->sorted=true;
		return $s;
		
		
	}
	

	
	function addAllTblFields($tblman){
		$r=array();
		//getFields
		//if($tblfields=$tblman->get_tbl_fields()){
		if($tblfields=$tblman->getFields()){
			foreach($tblfields as $c=>$f){
				if($item=$this->addFieldBySqlExp($c,$tblman->tbl.".".$c)){
					$r[]=$item;
					if($this->autoSetOptions){
						$item->setOptionsByField($f);	
					}
					
				}
			}
		}
		return $r;
		
	}
	//20231211
	function addALLFieldsByQuerySelects($query){
		if($items=$query->select->get_items_ok()){
			foreach($items as $item){
				$this->addFieldByQuerySelect($item);
			}
		}
	}
	function addFieldByQuerySelect($selectItem){
		if(!$cod=$selectItem->get_cod()){
			$cod=$selectItem->get_sql_in();
		}
		if(!$field=$this->addFieldBySqlExp($cod,$selectItem->get_sql_in())){
			return false;
		}
		if($selectItem->dataType){
			$field->dataType=$selectItem->dataType;
		}
		return $field;
	}
	function addFieldBySqlExp($cod,$sql=false){
		return $this->addField(new mwmod_mw_devextreme_data_fields_field($cod,$sql));
	}
	final function removeField($cod){
		if(!$f=$this->getField($cod)){
			return false;	
		}
		unset($this->fields[$cod]);
		return $f;
	}
	function setFieldsNames($data){
		if(!is_array($data)){
			return false;	
		}
		$r=array();
		foreach($data as $c=>$n){
			if($f=$this->getField($c)){
				$f->name=$n."";
				$r[$f->cod]=$f;	
			}
		}
		if(sizeof($r)){
			return $r;
		}
		
		
	}
	function getFieldsDxCols($cods=false){
		if(!$cods){
			$fields=$this->getFields();	
		}else{
			$fields=$this->getFieldsByCods($cods);	
		}
		
		if(!$fields){
			return false;	
		}
		$r=array();
		foreach($fields as $f){
			if($f->dxCol){
				$r[$f->cod]=$f->dxCol;
			}
		}
		if(sizeof($r)){
			return $r;
		}
	}

	function getFieldsByCods($cods){
		if(!$cods){
			return false;	
		}
		if(!is_array($cods)){
			if(!$cods=explode(",",$cods."")){
				return false;	
			}
		}
		
		$r=array();
		foreach($cods as $c){
			if($c=trim($c."")){
				if($f=$this->getField($c)){
					$r[$f->cod]=$f;	
				}
			}
		}
		if(sizeof($r)){
			return $r;
		}
	}
	final function getFields(){
		return $this->fields;	
	}
	final function getField($cod){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;	
		}
		return $this->fields[$cod];	
	}
	
	final function addField($field){
		if(!$cod=$field->cod){
			return false;	
		}
		$this->fields[$cod]=$field;
		$field->setQueryHelper($this);
		return $field;
	}
	
	function createFilters(){
		if(!$input=$this->getLoadOptions("filter")){
			return false;	
		}
		if(is_string($input)){
			$input=@json_decode($input,false);	
		}
		if(!is_array($input)){
			
			return false;	
		}
		$this->filterInput=$input;
		$this->filter=new mwmod_mw_devextreme_data_filter_filterhelper();
		$this->filter->setQueryHelper($this);
		$this->filter->addItemsByArray($input);
		return $this->filter;
	}
	function getDebugData(){
		$r=array(
			"loadOptions"=>$this->getLoadOptions(),
			//"filterInput"=>$this->filterInput,
			"filterInputStr"=>@json_encode($this->filterInput),
		);
		$r["fields"]=array();
		if($items=$this->getFields()){
			foreach($items as $cod=>$item){
				$r["fields"][$cod]=$item->getDebugData();
			}
		}
		if($this->filter){
			$r["filter"]=$this->filter->getDebugData();	
		}
		return $r;
			
	}
	function resetLoadOptions(){
		unset($this->loadOptions);
		unset($this->filter);
		
	}
	function setLoadOptions($options,$proc=true){
		$this->resetLoadOptions();
		$this->loadOptions=$options;
		if($proc){
			$this->procAllLoadOptions();	
		}
	}
	function getLoadOptions($cod=false){
		if(!is_array($this->loadOptions)){
			return false;	
		}
		if(!$cod){
			return $this->loadOptions;	
		}
		return mw_array_get_sub_key($this->loadOptions,$cod);
	}
	function createFiltersBysearchExpr(){
		if(!$searchExpr=$this->getLoadOptions("searchExpr")){
			return false;	
		}
		if(!is_string($searchExpr)){
			return false;
		}
		$searchValue=$this->getLoadOptions("searchValue");
	
		if(!((is_string($searchValue)or(is_numeric($searchValue)))  )){
			return false;
		}
		$searchOperation=$this->getLoadOptions("searchOperation");
		if(!$this->filter){
			$this->filter=new mwmod_mw_devextreme_data_filter_filterhelper();
			$this->filter->setQueryHelper($this);
		}
		$this->filter->addItemBySearchExpr($searchExpr,$searchValue,$searchOperation);

		
		
	}
	function procAllLoadOptions(){

		$this->createFilters();
		if($this->createFiltersBysearchExprEnabled){
			$this->createFiltersBysearchExpr();
		}
		
	}
	
	
}
?>