<?php

class mwmod_mw_devextreme_data_fields_field2tblrel extends mwmod_mw_devextreme_data_fields_abs{
	public $relUpdater;//mwmod_mw_db_reltbl_updater
	function __construct($cod,$relUpdater){
		$this->setCod($cod);
		$this->relUpdater=$relUpdater;
	}
	function controlFilter(){
		return true;	
	}
	function controlFilterModeAplay2QueryWhereAsChild($filterItem,$queryWhere){
		if(!$value=$filterItem->getValue()){
			return false;
		}
		if(!$idslist=$this->relUpdater->rel->getIdsList($value)){
			return false;	
		}
		$idsStr=implode(",",$idslist);
		$sql="SUM(IF(".$this->relUpdater->rel->tbl.".".$this->relUpdater->getSecondaryField()." in ($idsStr),1,0))";
		if(!$query=$queryWhere->query){
			return;	
		}
		//$fakecod=$this->get_select_helper_fake_cod();
		//$query->select->add_select($sql,$fakecod);
		$w=$query->having->add_where("$sql > 0");
		$query->useFullQueryCount=true;
		if($filterItem->connectiveOperator=="OR"){
			$w->set_or();	
		}
		if($filterItem->negative){
			$w->not=true;	
		}
		return $w;
		
		/*
		$w=$queryWhere->add_where($sql);
		*/
	}
	
	
}
?>