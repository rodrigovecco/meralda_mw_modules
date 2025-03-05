<?php
class mwmod_mw_db_sql_from_sql extends mwmod_mw_db_sql_from_tbl{
	
	var $sql="";
	
	function __construct($sql,$cod,$querypart=false){
		$this->sql=$sql;
		$this->set_cod($cod);
		$this->set_query_part($querypart);
	}
	function set_join_both(){
		$this->join_mode="join";
	}
	function set_join_right(){
		$this->join_mode="right join";
	}
	function set_join_left(){
		$this->join_mode="left join";
	}
	function is_as_mode(){
		return $this->as_mode;	
	}
	function get_as_cod(){
		return $this->get_cod();	
	}
	function get_sql_as_other_as_mode(){
		$r=" ".$this->join_mode." ".$this->get_sql_in();
		if(!$cod=$this->get_as_cod()){
			return false;
		}
		$r.=" as $cod ";
		$tblname=$cod;	
		
		
		if($this->specialON){
			$r.=" on (".$this->specialON.")";
		}elseif($this->external_join_field){
			$r.=" on (".$this->external_join_field."=".$tblname.".".$this->inner_join_field.")";
		}
		return $r;
	}
	
	function get_sql_as_other(){
		if($this->is_as_mode()){
			return $this->get_sql_as_other_as_mode();	
		}
		$r=" ".$this->join_mode." ".$this->get_sql_in();
		
		if($cod=$this->get_cod()){
			$r.=" as $cod ";	
		}
		
		
		if($this->specialON){
			$r.=" on (".$this->specialON.")";
		}elseif($this->external_join_field){
			$r.=" on (".$this->external_join_field."=".$this->get_cod().".".$this->inner_join_field.")";
		}
		return $r;
	}
	function get_sql_as_first(){
		return $this->get_sql_in();	
	}
 
	function get_sql_in(){
		
		return "(".$this->sql.")";
	}
	
	function get_sql(){
		
		$sql=$this->get_sql_in();
		if($cod=$this->get_cod()){
			$sql.=" as $cod";	
		}
		return $sql;
	}
	
}
?>