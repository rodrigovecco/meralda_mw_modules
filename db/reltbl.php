<?php
//maneja una tabla que vincula dos tablas
class mwmod_mw_db_reltbl extends mwmod_mw_db_sql_abs{
	public $tbl;
	public $elem1tbl;
	public $elem1field;
	public $elem2tbl;
	public $elem2field;
	
	//public $elem1Man;//no siempre existe, es un mwmod_mw_manager_man
	//public $elem1Man;//no siempre existe, es un mwmod_mw_manager_man
	
	function __construct($tbl,$field1,$field2,$elem1tbl=false,$elem2tbl=false){
		$this->tbl=$tbl;
		$this->elem1field=$field1;
		$this->elem2field=$field2;
		if($elem1tbl){
			$this->elem1tbl=$elem1tbl;
		}
		if($elem2tbl){
			$this->elem2tbl=$elem2tbl;
		}
			
	}
	function add2Query($selName,$query,$isElem2=false){
		if($isElem2){
			$f1=$this->elem2field;
			$mainTbl=$this->elem2tbl;
			$f2=$this->elem1field;	
		}else{
			$f1=$this->elem1field;	
			$f2=$this->elem2field;
			$mainTbl=$this->elem1tbl;
		}
		$query->from->add_from_join_external($this->tbl,$mainTbl.".id",$f1);
		return $query->select->add_select("GROUP_CONCAT(distinct {$this->tbl}.{$f2})",$selName);
			
	}
	function doUpdateListForElem1($itemID,$strRelItemsIds){
		return $this->doUpdateList($itemID,$strRelItemsIds,false);	
		
	}
	function doUpdateListForElem2($itemID,$strRelItemsIds){
		return $this->doUpdateList($itemID,$strRelItemsIds,true);	
		
	}
	function doUpdateList($itemID,$strRelItemsIds,$isElem2=false){
		if(!$sqlList=$this->getSQLUpdateList($itemID,$strRelItemsIds,$isElem2)){
			return false;	
		}
		if(!$db=$this->get_dbman()){
			return;	
		}
		foreach($sqlList as $sql){
			$db->query($sql);	
		}
		return true;
		
	}
	
	function getSQLUpdateListForElem1($itemID,$strRelItemsIds){
		return $this->getSQLUpdateList($itemID,$strRelItemsIds,false);
	}
	function getSQLUpdateListForElem2($itemID,$strRelItemsIds){
		return $this->getSQLUpdateList($itemID,$strRelItemsIds,true);
	}
	function getSQLUpdateList($itemID,$strRelItemsIds,$isElem2=false){
		if(!$itemID=$itemID+0){
			return false;	
		}
		if($isElem2){
			$f1=$this->elem2field;	
			$f2=$this->elem1field;	
		}else{
			$f1=$this->elem1field;	
			$f2=$this->elem2field;	
		}
		$r=array();
		$delSQL="delete from ".$this->tbl." where $f1 = $itemID ";
		if(!$ids=$this->getIdsList($strRelItemsIds)){
			$r["delete"]=$delSQL;
			return $r;
		}
		
		$delSQL.=" and $f2 not in (".implode(",",$ids).")";
		$r["delete"]=$delSQL;
		$insSQL="insert into ".$this->tbl." ( $f1 , $f2 ) VALUES ";
		$valslist=array();
		foreach($ids as $id){
			$valslist[]="( ".$itemID.", ".$id.")";
		}
		$insSQL.=implode(",",$valslist);
		$r["insert"]=$insSQL;
		return $r;
		
		
		
		
	}
	function getIdsList($input){
		if(!$input=trim($input)){
			return false;	
		}
		$r=array();
		$list=explode(",",$input);
		foreach($list as $id){
			if($id=abs(round(trim($id)+0))){
				$r[$id]=$id;
			}
		}
		if(sizeof($r)){
			return $r;	
		}
		
	}
	
	
	
}


?>