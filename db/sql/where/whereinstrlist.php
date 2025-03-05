<?php
class mwmod_mw_db_sql_where_whereinstrlist extends mwmod_mw_db_sql_where_abs{
	var $input_list;
	public $allowEmpty=false;
	function __construct($field,$list,$cod=false,$querypart=false){
		$this->field=$field;
		$this->crit="";
		$this->input_list=$list;
		$this->set_cod($cod);
		$this->set_query_part($querypart);
	}
	/*
	function append_to_sql(&$sql){
		if($this->pre_append_to_sql($sql)){
			$sql.=$this->get_sql_as_other();	
		}else{
			$sql.=$this->get_sql_as_first();		
		}
	}
	
	function append_to_parameterized_sql($pq,&$tempSubSQLstr=""){
		if(!$this->is_ok()){
			return "";	
		}
		if($this->pre_append_to_sql($tempSubSQLstr)){
			
			$pq->appendSQL($this->get_sql_other_prev(),$tempSubSQLstr);	
		}
		if($this->dbModeCheckSQLsrv()){
			if(!$this->include_hour){
				$pq->appendSQL(" CONVERT(date, ".$this->field.")".$this->cond."?",$tempSubSQLstr);
				$pq->addParam(date("Y-m-d",$this->time));

			}else{
	 			$pq->appendSQL(" CONVERT(datetime, " . $this->field . ")" . $this->cond . "?", $tempSubSQLstr);
	       		$pq->addParam(date("Y-m-d H:i:s", $this->time));
			}


		}else{

			if(!$this->include_hour){
				$pq->appendSQL(" ".$this->get_sql_formated_left().$this->cond."?",$tempSubSQLstr);
				$pq->addParam(date("Y-m-d",$this->time));

			}else{
	 			$pq->appendSQL(" ".$this->get_sql_formated_left().$this->cond."?", $tempSubSQLstr);
	       		$pq->addParam(date("Y-m-d H:i:s", $this->time));
			}
		}



		
		
		
	}
	*/

	function append_to_parameterized_sql($pq,&$tempSubSQLstr=""){
		if(!$this->input_list){
			return false;	
		}
		$list=$this->input_list;
		if(!is_array($list)){
			$list=explode(",",$this->input_list."");	
		}
		$r=array();
		foreach($list as $id){
			if($id=mw_get_string($id)){
				$id=trim($id);
				$r[$id]=$id;
			}elseif($this->allowEmpty){
				$r[""]="";
			}
		}
		if(!sizeof($r)){
			return false;	
		}
		reset($r);




		if($this->pre_append_to_sql($tempSubSQLstr)){
			
			$pq->appendSQL($this->get_sql_other_prev(),$tempSubSQLstr);	
		}

		$not="";
		if($this->notIn){
			$not=" NOT ";
		}
		

		$pq->appendSQL(" ".$this->field." $not in (",$tempSubSQLstr);
		$rph=array();
		foreach($r as $id){
			$rph[]="?";
			$pq->addParam($id);

		}
		$liststr=implode(",",$rph);

		$pq->appendSQL($liststr.") ",$tempSubSQLstr);
		return true;

		


		
		
		
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
			//if($id=mysql_real_escape_string(trim($id))){
			if($id=$this->real_escape_string(trim($id))){
				$r[$id]="'".$id."'";
			}elseif($this->allowEmpty){
				$r[""]="''";
			}
		}
		if(!sizeof($r)){
			return false;	
		}
		return implode(", ",$r);
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