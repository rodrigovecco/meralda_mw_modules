<?php
class mwmod_mw_db_sql_where extends mwmod_mw_db_sql_querypart{
	function __construct($query=false){
		if($query){
			$this->set_query($query);	
		}
	}
	
	function add_date_ok($field,$cod=false){
		$item=new mwmod_mw_db_sql_where_dateok($field,$cod,$this);
		return $this->add_item($item);
		
	}
	
	function add_date_cond($field,$timeOrDate,$operator="=",$cod=false){
		$item=new mwmod_mw_db_sql_where_wheretime($field,$timeOrDate,$cod,$this);
		$item->set_operator($operator);
		$item->include_hour=false;
		return $this->add_item($item);
		
	}
	function add_time_cond($field,$timeOrDate,$operator="=",$cod=false){
		$item=new mwmod_mw_db_sql_where_wheretime($field,$timeOrDate,$cod,$this);
		$item->set_operator($operator);
		$item->include_hour=true;
		
		return $this->add_item($item);
		
	}
	
	
	function new_where($where,$cod=false){
		if(is_string($where)){
			return new mwmod_mw_db_sql_where_wheresql($where,$cod,$this);
		}
		if(is_object($where)){
			return $where;
		}
		return false;
			
	}
	
	function add_sub_where($cod=false){
		$item=new mwmod_mw_db_sql_where_subwhere($cod,$this);
		return $this->add_item($item);
		
	}
	
	function add_where_num_list($field,$list,$cod=false){
		$item=new mwmod_mw_db_sql_where_whereinnumlist($field,$list,$cod);
		return $this->add_item($item);
	}
	function add_where_str_list($field,$list,$cod=false){
		$item=new mwmod_mw_db_sql_where_whereinstrlist($field,$list,$cod);
		return $this->add_item($item);
	}
	function add_where_crit($field,$val,$cod=false){
		$item = new mwmod_mw_db_sql_where_wherevalpair($field,$val,$cod,$this);
		return $this->add_item($item);
		
	}
	function add_where_crit_like($field,$val,$cod=false){
		$item = new mwmod_mw_db_sql_where_textcomp($field,$val,$cod,$this);
		return $this->add_item($item);
		
	}
	function add_where($where,$cod=false){
		if($item=$this->new_where($where,$cod)){
			return $this->add_item($item);
		}
	}
	function get_sql_start(){
		return " where ";	
	}

	
}
?>