<?php
class mwmod_mw_db_sql_where_wheretime extends mwmod_mw_db_sql_where_abs{
	var $time;
	var $include_hour=false;

	
	function __construct($field,$timeOrDate,$cod=false,$querypart=false){
		$this->set_query_part($querypart);
		$this->field=$field;
		//$this->crit=mysql_real_escape_string($val);
		//$this->crit=$val;
		$this->set_cod($cod);
		$this->set_time($timeOrDate);
	}
	function set_time($timeOrDate){
		if(!$timeOrDate){
			return false;	
		}
		
		if($time=$this->helper->dateman->checkTimeOrDate($timeOrDate)){
			$this->time=$time;
			return $this->time;	
		}
		
	}
	
	function is_ok(){
		if($this->time){
			return true;	
		}
		return false;
	}
	
	function get_sql_formated_left(){
		if(!$this->include_hour){
			return "DATE_FORMAT(".$this->field.",'%Y-%m-%d')";	
		}
		return $this->field;
		
	}
	function get_sql_formated_right(){
		if(!$this->time){
			return "";	
		}
		return $this->helper->dateman->get_sys_date($this->time,$this->include_hour);
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
	function get_sql_in(){
		if(!$this->is_ok()){
			return "";	
		}
		return $this->get_sql_formated_left().$this->cond."'".$this->real_escape_string($this->get_sql_formated_right())."'";
		
		
	}
	
	
}
?>