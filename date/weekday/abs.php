<?php
//
abstract class mwmod_mw_date_weekday_abs extends mwmod_mw_date_month_abs{
	
	final function init_weekday($id,$shortname,$name,$man){
		$this->init_month($id,$shortname,$name,$man);
	}
	function load_replace_list(){
		$replace=array();
		$replace["L"]=$this->get_name();
		$replace["l"]=$this->name;
		$replace["D"]=$this->get_short_name();
		return $replace;
	}

}


?>