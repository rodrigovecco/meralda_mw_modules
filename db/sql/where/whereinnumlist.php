<?php
class mwmod_mw_db_sql_where_whereinnumlist extends mwmod_mw_db_sql_where_abs{
	var $input_list;
	function __construct($field,$list,$cod=false,$querypart=false){
		$this->field=$field;
		$this->crit="";
		$this->input_list=$list;
		$this->set_cod($cod);
		$this->set_query_part($querypart);
	}
	
	function get_ok_list(){
		if(!$this->input_list){
			return false;	
		}
		$list=$this->input_list;
		if(!is_array($list)){
			$list=explode(",",$this->input_list."");	
		}
		$r=array();
		foreach($list as $id){
			//if($id=mw_get_number($id)){
			if($id=mw_get_number($id)){
				$r[$id]=$id;
			}
		}
		if(!sizeof($r)){
			return false;	
		}
		return implode(",",$r);
	}
	 
	function get_sql_in(){
		if(!$list=$this->get_ok_list()){
			return "1=0";	
		}
		$not="";
		if($this->notIn){
			$not=" NOT ";
		}
		return $this->field." $not in ($list)";
	}
	
	
}
?>