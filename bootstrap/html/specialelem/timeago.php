<?php
class mwmod_mw_bootstrap_html_specialelem_timeago extends mwmod_mw_bootstrap_html_specialelem_elemabs{
	var $time=false;
	var $time_container;
	function __construct($time=false,$tag="span"){
		$this->init_bt_special_elem("timeago",$tag);
		$this->time_container=new mwmod_mw_html_cont_varcont();
		$this->add_cont($this->time_container);
		$this->set_time($time);
	
	}
	function set_time($time=false){
		$this->time=false;
		if(!$time){
			$this->update_time();
			return false;
		}
		if(is_string($time)){
			if($time=="0000-00-00"){
				$this->update_time();
				return false;
			}
			if($time=="0000-00-00 00:00:00"){
				$this->update_time();
				return false;
			}
			if(!$time=strtotime($time)){
				$this->update_time();
				return false;
			}
		}
		if($time===true){
			$time=time();		
		}
		if(is_numeric($time)){
			if($time>0){
				$this->time=$time;
				$this->update_time();
				return true;
			}
		}
		$this->update_time();
		return false;
	}
	function update_time(){
		if($this->time){
			if(is_numeric($this->time)){
				if($this->time>0){
					$this->set_att("data-time",($this->time*1000));
					$this->set_att("data-date",date("Y-m-d H:i:s",$this->time));
					$this->set_att("title",date("Y-m-d H:i:s",$this->time));
					$this->time_container->set_cont(date("Y-m-d H:i",$this->time));
					return true;	
				}
			}
		}
		$this->set_att("data-time","");
		$this->set_att("data-date","");
		$this->set_att("title","");
		$this->time_container->set_cont("");

	}
	function get_class_names_list(){
		$r=array();
		if($this->main_class_name){
			$r[]=$this->main_class_name;
		}
		if($list=$this->get_addicional_classes()){
			foreach($list as $c){
				$r[]=$c;	
			}
		}
		$this->add_other_class_names_to_list($r);
		return $r;
		
	}

	
}

?>