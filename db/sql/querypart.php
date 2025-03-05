<?php
abstract class mwmod_mw_db_sql_querypart extends mwmod_mw_db_sql_abs{
	private $query;
	private $_items=array();
	private $_items_by_cod=array();
	
	function append_to_parameterized_sql($pq,&$tempSubSQLstr=""){

		if(!$items=	$this->get_items_ok()){
			 $pq->appendSQL($this->get_sql_no_items());
			 return;
		}
		$pq->appendSQL($this->get_sql_start());
		$sqlItemsTemp="";
		foreach ($items as $item){
			if($this->debug_mode){
				$item->debug_mode=true;	
			}
			
			$item->append_to_parameterized_sql($pq,$sqlItemsTemp);	
			if($this->debug_mode){
				$pq->appendSQL("\n");
			}

		}
		$pq->appendSQL($this->get_sql_end());
		return true;
	}

	function get_sql_no_items(){
		return "";	
	}
	function get_sql_start(){
		return "";	
	}
	function get_sql_end(){
		return " ";	
	}
	function get_sql(){
		$sql="";
		if(!$items=	$this->get_items_ok()){
			return $this->get_sql_no_items();	
		}
		foreach ($items as $item){
			if($this->debug_mode){
				$item->debug_mode=true;	
			}
			
			$item->append_to_sql($sql);	
			if($this->debug_mode){
				$sql.="\n";
			}

		}
		return $this->get_sql_start().$sql.$this->get_sql_end();
		
	}
	function get_items_ok(){
		if(!$items=	$this->get_items()){
			return false;	
		}
		$r=array();
		foreach ($items as $item){
			if($item->is_ok()){
				$r[]=$item;	
			}
		}
		if(sizeof($r)>0){
			return $r;
		}
		return false;
			
	}
	final function set_query($query=false){
		if($query){
			$this->query=$query;	
		}
	}
	final function __get_query(){
		return $this->query;	
	}
	function __get_priv_query(){
		return $this->__get_query();	
	}
	final function get_items(){
		return $this->_items;	
	}
	final function get_item($cod){
		//nueva!!!
		if(!$cod){
			return false;	
		}
		if(array_key_exists($cod,$this->_items_by_cod)){
			return $this->_items_by_cod[$cod];
		}
		return false;
	}
	final function add_item($item,$cod=false){
		$this->_items[]=$item;
		if(!$cod){
			$cod=$item->get_cod();	
		}
		if($cod){
			$this->_items_by_cod[$cod]=$item;	
		}
		return $item;
	}
	function get_debug_data(){
		$r=array();
		$r["class"]=get_class($this);
		$r["dbmanclass"]=get_class($this->dbman);
		$r["sql"]=$this->get_sql();
		if($items=$this->get_items()){
			$x=1;
			$r["items"]=array();
			foreach($items as $item){
				$r["items"][$x]=$item->get_debug_data();
				$x++;	
			}
		}
		return $r;
			
	}
	function load_dbman(){
		if($this->query){
			if($m=$this->query->dbman){
				return $m;	
			}
		}
		return 	$this->mainap->get_submanager("db");	
	}

	
}
?>