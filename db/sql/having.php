<?php
//20210222 repoder
class mwmod_mw_db_sql_having extends mwmod_mw_db_sql_where{
	function __construct($query=false){
		if($query){
			$this->set_query($query);	
		}
	}
	function get_sql_start(){
		return " having ";	
	}
	function XXXXXXappend_to_parameterized_sql($pq,&$tempSubSQLstr=""){

		if(!$items=	$this->get_items_ok()){
			 $pq->appendSQL($this->get_sql_no_items());
			 return;
		}
		$pq->appendSQL($this->get_sql_start());
		if($this->query){
			if($this->query->autoSetCountFullQuery){
				$this->query->useFullQueryCount=true;
			}
		}
		$sqlItemsTemp="";
		$firstDone=false;
		foreach ($items as $item){
			if(!$firstDone){
				$item->isFirst=true;
				$firstDone=true;
			}else{
				$item->isFirst=false;
			}
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

	
}
?>