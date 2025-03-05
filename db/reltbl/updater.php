<?php
//maneja una tabla que vincula dos tablas
//se usa al salvar y crear relaciones con mwmod_mw_db_reltbl
class mwmod_mw_db_reltbl_updater extends mw_apsubbaseobj{
	public $cod;
	public $rel;
	public $isSecondary=false;
	public $editable=true;
	
	function __construct($cod,$rel,$isSecondary=false){
		$this->cod=$cod;
		$this->rel=$rel;
		$this->isSecondary=$isSecondary;
	}
	function relatedItemsIds($mainItem,&$idsList=array()){
		$query=new mwmod_mw_db_sql_query($this->rel->tbl);
		$f=$this->getSecondaryField();
		$query->select->add_select("GROUP_CONCAT(distinct {$f})","relIds");
		$query->group->add_group($this->getMainField());
		$query->where->add_where_crit($this->getMainField(),$mainItem->get_id());
		if($data=$query->get_one_row_result()){
			if($ids=explode(",",$data["relIds"])){
				foreach($ids as $id){
					if($id=mw_get_number($id)){
						if(!$idsList){
							$idsList=array();	
						}
						$idsList[$id]=$id;	
					}
				}
			}
			
		}
		if($idsList){
			if(sizeof($idsList)){
				return $idsList;	
			}
		}
		
		
	}
	
	
	function add2Query($query){
		$this->rel->add2Query($this->cod,$query,$this->isSecondary);
	}
	function getMainField(){
		if($this->isSecondary){
			return $this->rel->elem2field;	
		}else{
			return $this->rel->elem1field;		
		}
	}
	function getSecondaryField(){
		if($this->isSecondary){
			return $this->rel->elem1field;		
		}else{
			return $this->rel->elem2field;	
		}
	}
	
	function doUpdateList($mainItemID,$relatedItsmsIds){
		if(!$this->editable){
			return;	
		}
		if($this->isSecondary){
			return $this->rel->doUpdateListForElem2($mainItemID,$relatedItsmsIds);	
		}else{
			return $this->rel->doUpdateListForElem1($mainItemID,$relatedItsmsIds);	
		}
		
	}
	
	function get_cod(){
		return $this->cod;	
	}
}


?>