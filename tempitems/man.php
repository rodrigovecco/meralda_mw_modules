<?php
//todo: cron delete expired items
class mwmod_mw_tempitems_man extends mwmod_mw_manager_man{
	public $deleteExpiredItemsLimit=100;
	function __construct($mainap,$code="temp_items"){
		$this->init($code,$mainap,$code);	
	
	}
	function create_item($tblitem){
		$item=new mwmod_mw_tempitems_item($tblitem,$this);
		return $item;
	}
	function deleteExpiredItems(){
		$n=0;
		$query=$this->get_tblman()->new_query();
		$query->where->add_date_cond("exp_date",date("Y-m-d H:i:s"),"<");
		$query->limit->set_limit($this->deleteExpiredItemsLimit);
		if($items=$this->get_items_by_query($query)){
			foreach($items as $item){
				$item->do_delete();
				$n++;
			}
		}
		return $n;


	}

}
?>