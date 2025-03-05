<?php
//rvh 20240313
class  mwmod_mw_db_tbl extends mw_apsubbaseobj{
	private $dbman;
	private $tbl;
	private $tblfields;
	private $id_field="id";
	private $items=array();
	private $items2preload=array();
	private $allitems;
	private $_readonly_fields=array();
	private $_fieldManagers;
	
	var $only_update_if_different=false;
	function __construct($dbman,$tbl){
		$this->init($dbman,$tbl);	
	}
	final function setIDfield($id_field){
		$this->id_field=$id_field;
	}
	final function getFields(){
		$this->initFields();
		return $this->_fieldManagers;	
			
	}
	final function getField($cod){
		if(!$cod){
			return false;	
		}
		$this->initFields();
		return $this->_fieldManagers[$cod]??null;	
	}
	final function initFields(){
		if(isset($this->_fieldManagers)){
			return;	
		}
		$this->_fieldManagers=array();
		if($items=$this->createFieldsManagers()){
			foreach($items as $id=>$item){
				$this->_fieldManagers[$id]=$item;
			}
		}
		
		
	}
	function createFieldsManagers(){
		$sql="SHOW FULL COLUMNS from ".$this->tbl;
		$r=array();
		if($query=$this->dbman->query($sql)){
			while ($data=$this->fetch_assoc($query)){
				if($id=$data["Field"]){
					if($item=$this->createFieldMan($id,$data)){
						$r[$id]=$item;	
					}
				}
			}
		}
		return $r;

	}


	function getFieldsDebugData(){
		$r=array();
		if($items=$this->getFields()){
			foreach($items as $id=>$item){
				$r[$id]=$item->getDebugData();	
			}
		}
		return $r;
	}
	function createFieldMan($cod,$data=false){
		$item=new mwmod_mw_db_tblfield($cod,$data,$this);
		return $item;	
	}
	
	
	
	function real_escape_string($txt){
		return $this->dbman->real_escape_string($txt);	
	}
	function format_time($time=true){
		return $this->dbman->format_time($time);
	}
	
	final function add_read_only_field($cod){
		if(!$info=$this->get_field_info($cod)){
			return false;	
		}
		$this->_readonly_fields[$cod]=$cod;
		return true;
	}
	final function get_read_only_fields(){
		return $this->_readonly_fields;	
	}
	function new_query(){

		$query=new mwmod_mw_db_sql_query($this->tbl);
		$query->set_dbman($this->dbman);
		return $query;	
	}

	function get_new_avaible_id(){
		return $this->get_current_status("Auto_increment");
	}
	function fetch_assoc($query){
		return $this->dbman->fetch_assoc($query);
	}
	function get_current_status($cod=false){
		
		$sql="SHOW TABLE STATUS LIKE '$this->tbl'";
		
		if(!$query=$this->dbman->query($sql)){
			return false;
		}
		if(!$data=$this->fetch_assoc($query)){
			return false;
		}
		
		if(!is_string($cod)){
			$cod=false;	
		}
		if(!$cod){
			return $data;
		}
		return $data[$cod];
		
	}
	
	//////////////
	function get_all_items(){
		if(isset($this->allitems)){
			return $this->allitems;
		}
		$sql=$this->get_sql_load_all_items();
		
		$this->allitems=$this->get_items_by_sql($sql);
		return $this->allitems;
		
	}
	function get_field_from_sql($sql,$field){
		return $this->dbman->get_field_from_sql($sql,$field);	
	}
	function get_array_from_sql($sql){
		return $this->dbman->get_array_from_sql($sql);	
	}
	final function get_sql_load_all_start(){
		$sql="select * from ".$this->tbl." ";	
		return $sql;
	}
	function get_sql_load_all_items(){
		$sql=$this->get_sql_load_all_start();
		return $sql;
	}
	function generate_insert_sql($data,$paramQuery=false){
		//ignore was added to prevent fatal error rvh 20230923
		$sql="insert IGNORE  into ".$this->tbl." ";
		if($this->dbman->dbModeCheckSQLsrv()){
			$sql="insert into ".$this->tbl." ";
		}
		if($paramQuery){
			$paramQuery->appendSQL($sql);
		}
		$fieldslist=array();
		$valueslist=array();
		$valueslistPH=array();
		$ok=false;
		foreach($data as $c=>$v){
			if(is_string($c)){
				if(!is_array($v)){
					//20231206
					if($field=$this->getField($c)){

						if($this->dbman->dbModeCheckSQLsrv()){
							$fieldslist[]="$c";
						}else{
							$fieldslist[]="`$c`";
						}

						
						$valueslistPH[]=" ? ";
						if(is_null($v) and $field->nullAllowed()){
							$valueslist[]=" NULL ";
							if($paramQuery){
								$paramQuery->addParam(null);

							}
						}else{
							$valueslist[]="'".$this->real_escape_string($v)."'";
							if($paramQuery){
								$paramQuery->addParam($v);

							}
						}
						$ok=true;
					
					}
					
				}
			}
		}
		if(!$ok){
			
			return false;	
		
		}
		$sql.="(".implode(",",$fieldslist).") ";
		$sql.=" values (".implode(",",$valueslist).") ";
		if($paramQuery){
			$paramQuery->appendSQL("(".implode(",",$fieldslist).") ");
			$paramQuery->appendSQL(" values (".implode(",",$valueslistPH).") ");

		}
		return $sql;
		
		
	
	}
	function get_insert_sql($data){
		return $this->generate_insert_sql($data);
		
	
	}
	function getInsertOrUpdateSQL($data,$keys){
		//todo: paramQ
		if(!is_array($data)){
			return false;
		}
		if(!is_array($keys)){
			return false;
		}
		$nData=array();
		$nDataUpdate=array();
		foreach($data as $c=>$v){
			if($this->getField($c)){
				$nData[$c]=$v;
				$nDataUpdate[$c]=$v;
			}
		}
		$nKeys=array();
		foreach($keys as $c=>$v){
			if($this->getField($c)){
				$nKeys[$c]=$v;
				$nData[$c]=$v;
			}else{
				return false;
			}
		}
		if(!sizeof($nKeys)){
			return false;
		}
		if(!sizeof($nDataUpdate)){
			return false;
		}
		if(!$sqlInsert=$this->get_insert_sql($nData)){
			return false;
		}
		$sql=$sqlInsert." ON DUPLICATE KEY UPDATE ";
		reset($nDataUpdate);
		$updateLines=array();
		foreach($nDataUpdate as $c=>$v){
			$updateLines[]="`$c`= VALUES(`$c`)";
		}
		$sql.=implode(",", $updateLines);
		return $sql;



	}
	function insertOrUpdate($data,$keys){
		if(!$sql=$this->getInsertOrUpdateSQL($data,$keys)){
			return false;
		}
		
		return $this->dbman->query_get_affected_rows($sql);
		
	}

	private function _insert_item($data){
		
		if($this->dbman->useAlwaysParameterizedMode()){
			$paramQuery=new mwmod_mw_db_paramstatement_paramquery();
			if(!$sql=$this->generate_insert_sql($data,$paramQuery)){
				
				return false;
			}
			
			$sql=$paramQuery;

		}else{
			if(!$sql=$this->get_insert_sql($data)){
				return false;
			}
						
		}
		if(!$insertResponse=$this->dbman->insert($sql)){

			return false;
		}
		if(is_array($insertResponse)){
			$id=$insertResponse[$this->id_field]??null;
		}else{
			$id=$insertResponse;
		}
		if(!isset($id)){
			
			return false;
		}

		return $id;
		
	
	}
	function insert_item_width_id($data){
		if(!$id=$this->insert_item_width_id_get_id($data)){
			return false;
		}
		return $this->load_item($id);
	
	}
	function insert_item_width_id_get_id($data){
		if(!is_array($data)){
			return false;
		}
		return $this->_insert_item($data);
	}
	function insert_item($data){
		if(!is_array($data)){
			return false;
		}
		unset($data[$this->id_field]);
		if(!$id=$this->_insert_item($data)){
			return false;
		}
	
		return $this->load_item($id);
	}
	function create_item($id,$data){
		$item= new mwmod_mw_db_row($id,$data,$this);
		return $item;	
	}
	final function add_item($item){
		$id=$item->get_id();
		if($this->items[$id]??false){
			return 	$this->items[$id];
		}
		$this->items[$id]=$item;
		return 	$this->items[$id];
		
	}
	function get_or_create_item($data,$extradatamode=false){
		if(!is_array($data)){
			return false;	
		}
		if(!$id=$data[$this->id_field]+0){
			return false;	
		}
		if($item=$this->get_item_if_loaded($id)){
			if($extradatamode){
				$item->set_extra_data($data);
			}
			return $item;
		}
		if($item=$this->create_item($id,$data)){
			return $this->add_item($item);	
		}
	}
	final function preload(){
		if(sizeof($this->items2preload)<=0){
			return false;	
		}
		$ids=implode(",",$this->items2preload);
		$this->items2preload=array();
		$sql="select * from ".$this->tbl." where ".$this->id_field." in ($ids)";
		$this->get_items_by_sql($sql);
	}
	function query_get_affected_rows($sql){
		return $this->dbman->query_get_affected_rows($sql);
	}
	//20240314
	function get_item_by_mwQuery($mwQuery){
		$mwQuery->set_dbman($this->dbman);
		$sql=$mwQuery->get_sql_or_parameterized_query();
		return $this->get_item_by_sql($sql);

	}
	function get_item_by_sql($sql){
		if(!$query=$this->dbman->query($sql)){

			return false;
		}

		if($data=$this->fetch_assoc($query)){
			if($item=$this->get_or_create_item($data)){
				return $item;
			}
		}
		
	}
	//20240314
	function get_items_by_mwQuery($mwQuery,$extradatamode=false){
		$mwQuery->set_dbman($this->dbman);
		$sql=$mwQuery->get_sql_or_parameterized_query();
		return $this->get_items_by_sql($sql,$extradatamode);


	}
	function get_items_by_sql($sql,$extradatamode=false){
		if(!$query=$this->dbman->query($sql)){

			return false;
		}
		$r=array();
	
		while ($data=$this->fetch_assoc($query)){
			if($item=$this->get_or_create_item($data,$extradatamode)){
				$id=$item->get_id();
				$r[$id]=$item;	
			}
		}
		if(sizeof($r)){
			return $r;
		}
		
		
	}
	final function add_to_preload($id){
		if(!$id){
			return false;	
		}
		if(is_array($id)){
			$r=0;
			foreach($id as $id1){
				if($this->_add_to_preload($id1)){
					$r++;	
				}
			}
			return $r;
		}else{
			return $this->_add_to_preload($id);
		}
	}
	final function _add_to_preload($id){
		if(!is_numeric($id)){
			return false;
		}
		if(!$id=$id+0){
			return false;	
		}
		if($this->items[$id]??false){
			return true;		
		}
		$this->items2preload[$id]=$id;
		return true;
	}
	
	final function load_item($id){

		if(!$id=mw_get_number($id)){
			return false;
		}
		
		$this->add_to_preload($id);
		$this->preload();
		return $this->get_item_if_loaded($id);
	}
	final function get_item_if_loaded($id){
		
		if(!$id=mw_get_number($id)){
			return false;
		}
		if(!$id=$id+0){
			return false;	
		}
		if(isset($this->items[$id])){
			return $this->items[$id]; 	
		}
	
	}
	final function get_loaded_items(){
		return $this->items; 	
	
	}

	final function get_item($id){
		if($item=$this->get_item_if_loaded($id)){
			return $item;	
		}
		
		return $this->load_item($id);
	}
	
	final function __get_priv_dbman(){
		return $this->dbman; 	
	}
	final function __get_priv_id_field(){
		return $this->id_field; 	
	}
	final function get_field_info($cod){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;	
		}
		if(!$this->_init_tbl_fields()){
			return false;	
		}
		return $this->tblfields[$cod]??null;
	}
	final function reload_tbl_flields(){
		unset($this->tblfields);	
		unset($this->_fieldManagers);	
		$this->_init_tbl_fields();
	}
	function load_tbl_fields(){
		$sql="SHOW COLUMNS from ".$this->tbl;
		
		if(!$query=$this->dbman->query($sql)){
			return false;
		}
		$r=array();
		while ($data=$this->fetch_assoc($query)){
			if($id=$data["Field"]){
				$r[$id]=$data;	
			}
		}
		return $r;
	}
	private function _init_tbl_fields(){
		if(isset($this->tblfields)){
			return true;	
		}
		if(!$allData=$this->load_tbl_fields()){
			return false;
		}
		$this->tblfields=array();
		foreach($allData as $id=>$d){
			$this->tblfields[$id]=$d;	
		}
		
		/*
		$sql="SHOW COLUMNS from ".$this->tbl;
		
		if(!$query=$this->dbman->query($sql)){
			return false;
		}
		$this->tblfields=array();
		while ($data=$this->fetch_assoc($query)){
			if($id=$data["Field"]){
				$this->tblfields[$id]=$data;	
			}
		}
		*/
		return true;
		
		
	}
	final function get_tbl_fields(){
		return $this->__get_priv_tblfields();	
	}
	final function __get_priv_tblfields(){
		if(!$this->_init_tbl_fields()){
			return false;	
		}
		
		return $this->tblfields; 	
	}
	final function __get_priv_tbl(){
		return $this->tbl; 	
	}

	function __toString(){
		return $this->tbl;	
	}
	final function init($dbman,$tbl){
		$ap=$dbman->mainap;
		$this->tbl=$tbl;
		$this->dbman=$dbman;
		$this->set_mainap($ap);	
	
	}
	
}

?>