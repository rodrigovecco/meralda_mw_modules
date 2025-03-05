<?php

class mwmod_mw_jsobj_date extends mwmod_mw_jsobj_obj{
	var $time=false;
	var $include_hour=true;
	function __construct($date=false){
		$this->set_value($date);
	}
	function set_value($date=false){
		$this->time=false;
		if(!$date){
			return;	
		}
		$h=new mwmod_mw_ap_helper();
		if($t=$h->dateman->checkTimeOrDate($date)){
			$this->time=$t;
			return $t;
		}
		//$this->jsonStr=$jsonStr;	
	}
	function get_as_js_val(){
		if(!$t=$this->time){
			return "new Date()";
		}
		$m=date("m",$t)-1;
		if($this->include_hour){
			return "new Date(".date("Y,{$m},d,H,i,s",$t).")";	
		}else{
			return "new Date(".date("Y,{$m},d",$t).")";	
		}
		
	}

}
?>