<?php
class mwmod_mw_financial_xirr_item extends mw_apsubbaseobj{
	var $date;//str
	var $time;
	var $amount=0;
	var $amount_for_objective=0;
	var $change_on_objective_search=false;
	var $index=0;
	var $amounts_num=0;
	var $xirrman;
	function __construct($date,$index,$xirrman){
		$this->init_item($date,$index,$xirrman);
	}
	function add_amount_for_objective($amount){
		//no usado
		$amount=$amount+0;
		$this->amount_for_objective+=$amount_for_objective;
		$this->change_on_objective_search=true;
	}
	function add_amount($amount){
		$amount=$amount+0;
		$this->amount+=$amount;
		$this->amounts_num++;
	}
	function get_amount(){
		return $this->amount+$this->amount_for_objective;	
	}
	function populate_data(&$dates,&$amounts){
		$dates[]=$this->time;	
		$amounts[]=$this->amount;
		return true;
	}
	function get_debug_data(){
		$r=array(
			"index"=>$this->index,
			"date"=>$this->date,
			"time"=>$this->time,
			"amount"=>$this->amount,
			"amounts_num"=>$this->amounts_num,
			"amount_for_objective"=>$this->amount_for_objective,
		
		);
		return $r;
	}
	function set_date($date){
		$t=strtotime($date);
		$this->date=date("Y-m-d",$t);
		$this->time=strtotime($this->date);
	}
	function init_item($date,$index,$xirrman){
		$this->set_date($date);
		$this->index=$index+0;
		$this->xirrman=$xirrman;
		
	}
	
}
?>