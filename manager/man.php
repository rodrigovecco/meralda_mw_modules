<?php
//v6
//Rodrigo Vecco - 2024-03-13

abstract class  mwmod_mw_manager_man extends mwmod_mw_manager_basemanabs{
	private $tblman;
	//private $code;
	private $items=array();
	private $_all_items;
	private $_all_active_items;
	
	//private $_treedataman;
	//private $_strdataman;
	
	private $_tbl_name;
	private $dbmancod="db";
	//private $_can_create_strdata=false;
	//private $_can_create_treedata=false;
	
	private $zeroAsNullFieldsCods;
	function getZeoroAsNullFieldsCods(){
		return $this->__get_priv_zeroAsNullFieldsCods();
	}
	function loadZeroAsNullFieldsCods(){
		return false;	
	}
		
	final function __get_priv_zeroAsNullFieldsCods(){
		if(!isset($this->zeroAsNullFieldsCods)){
			if(!$this->zeroAsNullFieldsCods=$this->loadZeroAsNullFieldsCods()){
				$this->zeroAsNullFieldsCods=false;	
			}
		}
		return $this->zeroAsNullFieldsCods; 	
	}
	
	
	
	final function init($code,$ap,$tblname=false){
		$this->setManCode($code);
		if(!$tblname){
			$tblname=$this->code;	
		}
		$this->set_mainap($ap);	
		$this->_tbl_name=$tblname;
	}
	function allow_admin(){
		return $this->mainap->current_admin_user_allow("admin");	
	}
	function allow_cfg(){
		return $this->allow_admin();
	}
	
	function create_item($tblitem){
		
		$item=new mwmod_mw_manager_item($tblitem,$this);
		return $item;
	}
	
	function get_filemanager(){
		if(!$fm=$this->mainap->get_submanager("fileman")){
			return false;	
		}
		return $fm;
	}
	function set_new_item_inputs($gr,$item=false){
		$subgr=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("data"));
		$this->set_new_item_inputs_data($subgr,$item);
		//
	}
	function set_new_item_inputs_data($gr,$item=false){
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("name",$this->lng_common_get_msg_txt("name","Nombre")));
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("active",$this->lng_common_get_msg_txt("active","Activo")));
		if($item){
			$gr->set_value($item->get_data());	
		}
		
	}
	function get_new_item_datafield_creator(){
		$cr=new mwmod_mw_datafield_creator();
		return $cr;
	}
	function get_item_datafield_creator($item=false){
		$cr=$this->get_new_item_datafield_creator();
		return $cr;
	}
	function get_new_avaible_id(){
		if(!$man=$this->get_tblman()){
			return false;	
		}
		return $man->get_new_avaible_id();

	}

	function get_public_url_path(){
		if(!$code=basename($this->code)){
			return false;
		}
		if(!$p=$this->mainap->get_public_userfiles_url_path()){
			return false;	
		}
		$p.="/elems/".$code;
		return $p;
		
	}
	function get_items_from_str_id_list($str){
		if(!$str){
			return false;	
		}
		if(is_string($str)){
			return $this->get_items_from_array_id_list(explode(",",$str));
		}elseif(is_numeric($str)){
			return $this->get_items_from_array_id_list(array($str));
		}
	}
	function get_items_from_array_id_list($list){
		if(!is_array($list)){
			return false;	
		}
		$listok=array();
		foreach($list as $id){
			if($id=mw_get_number($id)){
				$listok[$id]=$id;
				$this->add_to_preload($id);
			}
		}
		if(!sizeof($listok)){
			return false;	
		}
		$this->preload_items();
		foreach($listok as $id){
			if($item=$this->get_item_if_loaded($id)){
				$r[$id]=$item;	
			}
		}
		return $r;
		
	}
	


	function __get_items_path(){
		if(!$code=basename($this->code)){
			return false;
		}
		$p="elems/".$code;
		return $p;
			
	}
	
	
	function get_all_items_ccb(){
		return $this->get_items_ccb($this->get_all_items());
	}
	function get_items_ccb($items){
		if(!is_array($items)){
			return false;	
		}
		foreach ($items as $id=>$item){
			$r[$id]=$item->get_name();	
		}
		return $r;
	}
	
	function get_item_name($item){
		return $item->get_data("name");	
		
	}
	function get_select_all_sql_start(){
		if(!$m=$this->get_tblman()){
			return false;
		}
		if(!$tbl=$m->tbl){
			return false;	
		}
		
		return "select {$tbl}.* from ".$m->tbl;	
	}
	
	function get_init_all_items(){
		if(!$m=$this->get_tblman()){
			return false;
		}
		if(!$items=$m->get_all_items()){
			return false;
		}
		$r=array();
		foreach ($items as $itemtbl){
			if($item=$this->get_or_create_item($itemtbl)){
				if($id=$item->get_id()){
					$r[$id]=$item;	
				}
			}
		}
		return $r;
		
	}
	final function get_all_items(){
		if(!isset($this->_all_items)){
			$this->_all_items=$this->get_init_all_items();
		}
		return $this->_all_items;
	}
	final function get_all_active_items(){
		if(!isset($this->_all_active_items)){
			$this->_all_active_items=$this->get_init_all_active_items();
		}
		return $this->_all_active_items;
	}
	function get_active_items_query(){
		if(!$tblman=$this->get_tblman()){
			return false;	
		}
		if(!$q=$tblman->new_query()){
			return false;	
		}
		if($this->tblman->getField("name")){
			$q->order->add_order("name");
		}
		
		if($this->tblman->getField("active")){
			$q->where->add_where("active=1");
		}
		
		
		return $q;
		
	}
	function get_init_all_active_items(){
		if(!$query=$this->get_active_items_query()){
			return false;	
		}
		return $this->get_items_by_query($query);
		

	}
	function get_items_by_query($query){

		if(!$man=$this->get_tblman()){
			return false;	
		}
		if(!$items=$man->get_items_by_mwQuery($query)){
			return false;
		}
		foreach ($items as $itemtbl){
			if($item=$this->get_or_create_item($itemtbl)){
				if($id=$item->get_id()){
					$r[$id]=$item;	
				}
			}
		}
		return $r;

		//reescrito para habilitar query params
		/*
		if(!$sql=$query->get_sql()){
			return false;
		}
		return $this->get_items_by_sql($sql);
		*/
		
	}
	function get_items_by_simple_crit($crit){
		//crit array field=value
		
		if(!is_array($crit)){
			return false;
		}
		$tblman=$this->get_tblman();
		$query=$tblman->new_query();
		foreach($crit as $c=>$v){
			if($f=$tblman->getField($c)){
				$fn=$f->getFullName();
				$query->where->add_where_crit($fn,$v);
			}
		}

		if($items=$this->get_items_by_query($query)){
			return $items;
		}

	}
	function get_item_by_keys($keys){
		//return one uniq item, should be used for table with unique indexes
		if(!is_array($keys)){
			return false;
		}
		$tblman=$this->get_tblman();
		$query=$tblman->new_query();
		foreach($keys as $c=>$v){
			if($f=$tblman->getField($c)){
				$fn=$f->getFullName();
				$query->where->add_where_crit($fn,$v);
			}
		}

		if($items=$this->get_items_by_query($query)){
			foreach($items as $item){
				return $item;
			}
		}

	}
	
	
	function get_available_items_from_list($list){
		if(!is_array($list)){
			return false;
		}
		foreach ($list as $id=>$item){
			if($item->is_available()){
				$r[$id]=$item;
			}
		
		}
		return $r;
		
	}

	function get_available_item($id){
		if($item=$this->get_item($id)){
			if($item->is_available()){
				return $item;	
			}
		}
	}

	function get_items_by_sql_extra_data_mode($sql){
		//todo: paramquery mode!
		if(!$man=$this->get_tblman()){
			return false;	
		}
		if(!$items=$man->get_items_by_sql($sql,true)){
			return false;
		}
		
		foreach ($items as $itemtbl){
			if($item=$this->get_or_create_item($itemtbl)){
				if($id=$item->get_id()){
					$r[$id]=$item;	
				}
			}
		}
		return $r;
		
	}
	
	function get_items_by_sql($sql){
		if(!$man=$this->get_tblman()){
			return false;	
		}
		if(!$items=$man->get_items_by_sql($sql)){
			return false;
		}
		foreach ($items as $itemtbl){
			if($item=$this->get_or_create_item($itemtbl)){
				if($id=$item->get_id()){
					$r[$id]=$item;	
				}
			}
		}
		return $r;
		
	}
	
	function create_new_item_from_full_input($data){
		if(!is_array($data)){
			return false;
		}
		if(!$item=$this->create_new_item($data["data"])){
			return false;	
		}
		$item->after_created($data);
		return $item;
	}
	function create_new_item($data){
		if(!$nd=$this->validate_new_item_data($data)){
			return false;	
		}
		return $this->insert_item($data);
	}
	function insert_item($data){
		//no debe validar
		if(!$man=$this->get_tblman()){
			
			return false;	
		}
		
		if(!$dbitem=$man->insert_item($data)){
			//mw_array2list_echo($data);
			return false;	
		}
		
		return $this->get_or_create_item($dbitem);
			
	}
	function validate_new_item_data_sub(&$data){
		return $data;	
	}
	function validate_new_item_data(&$data){
		if(!is_array($data)){
			return false;
		}
		
		return $this->validate_new_item_data_sub($data);
		
		
	}
	
	
	
	function get_or_create_item($tblitem){
		if(!$tblitem){
			return false;	
		}
		if(!$id=$tblitem->get_id()){
			
			return false;	
		}
		
		if($item=$this->get_item_if_loaded($id)){
			return $item;	
		}
		if($item=$this->create_item($tblitem)){
			return $this->add_item($item);	
		}
	}
	final function get_item($id){
		if($item=$this->get_item_if_loaded($id)){
			return $item;	
		}
		
		return $this->load_item($id);
	}
	function load_item($id){
		if(!$man=$this->get_tblman()){
			return false;	
		}
		if($item=$man->get_item($id)){
			return $this->get_or_create_item($item);	
		}
	
	}
	final function add_item($item){
		$id=$item->get_id();
		if($this->items[$id]??null){
			return 	$this->items[$id];
		}
		$this->items[$id]=$item;
		return 	$this->items[$id];
		
	}
	function load_all_items_from_tbl_man_loaded(){
		if(!$man=$this->get_tblman()){
			return false;	
		}
		if(!$items=$man->get_loaded_items()){
			return false;	
		}
		
		foreach($items as $id=>$item){
			if((!isset($this->items[$id]))or(!$this->items[$id]??null)){
			//if(!$this->items[$id]??null){
				$this->get_or_create_item($item);		
			}
		}
		
	}
	function preload_items(){
		$this->preload();
		$this->load_all_items_from_tbl_man_loaded();
			
	}

	function preload(){
		if($tblman=$this->get_tblman()){
			return 	$tblman->preload();
		}
	}

	function add_to_preload($id){
		if($tblman=$this->get_tblman()){
			return 	$tblman->add_to_preload($id);
		}
	}
	final function get_item_if_loaded($id){
		if(!is_numeric($id)){
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
	final function get_tbl_name(){
		return $this->_tbl_name;	
	}
	function get_init_tblman_prepare($tblman){
		//
	}

	function get_init_tblman(){
		if(!$name=$this->get_tbl_name()){
			return false;	
		}
		if(!$db=$this->mainap->get_submanager($this->dbmancod)){
			return false;	
		}
		if(!$tblman=$db->get_tbl_manager($name)){
			return false;
		}
		$this->get_init_tblman_prepare($tblman);

		return $tblman;

	}
	
	final function get_tblman(){
		if(!isset($this->tblman)){
			if(!$this->tblman=	$this->get_init_tblman()){
				$this->tblman=	false;
			}
		}
		return $this->tblman;
	}
	
	final function __get_priv_tblman(){
		return $this->get_tblman(); 	
	}
	final function setDBManCode($code){
		$this->dbmancod=$code;
	}
	final function __get_priv_dbmancod(){
		return $this->dbmancod;
	}
	
	

}
?>