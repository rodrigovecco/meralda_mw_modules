<?php
class mwmod_mw_datafield_date extends mwmod_mw_datafield_datafielabs{
	var $calendar_mode=false;
	var $time_mode=false;
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array(),$calmode=true){
		$this->init($name,$lbl,$value,$req,$att,$style);
		$this->set_calendar_mode($calmode);	
	}
	/*
	
	function get_js_man_class_bootstrap_mode($frm=false){
		return "mw_input_elem_btdatepicker";	
	}
	function get_html_bootstrap($bt_output_man){
		return $bt_output_man->get_html_date($this);
	}
	function prepare_params_for_bootstrap(){
		$this->set_param("dpid",$this->get_frm_field_id_plain()."_gr");	
		if($lngman=$this->mainap->get_submanager("lng")){
			if($c=$lngman->get_locale_cod()){
				$this->set_param("locale",$c);	
					
			}
		}
	}
	*/
	
	function set_calendar_mode($val=true){
		$this->calendar_mode=$val;
	}
	/*
	function prepare_js_bootstrap_req_clases($frm){
		$frm->bootstrap_req_class_add("moment","/res/moment/moment-with-locales.js");
		$css=new mwmod_mw_jsobj_array();
		$css->add_data("/res/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css");
		$p=array(
			"css"=>$css,
		);
		$frm->bootstrap_req_class_add("bootstrapdatetimepicker","/res/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js",$p);	
		
		
		if($c=$this->get_js_man_class_bootstrap_mode()){
			$frm->bootstrap_req_class_add($c);
			
		}
		
	}
	*/
	
	function set_time_mode($val=true){
		$this->time_mode=$val;
		$this->set_param("time_mode",$val);
	}
	function add2jsreqclasseslist(&$list){
		if(!$this->is_calendar_mode()){
			return $this->add2jsreqclasseslist_no_cal($list);	
		}
		if(!is_array($list)){
			$list=array();	
		}
		$c="mw_input_elem_calendar";
		$list[$c]=$c;
		

		
	}
	function add2jsreqclasseslist_no_cal(&$list){
		
		if(!$c=$this->get_js_man_class()){
			return false;	
		}
		if(!is_array($list)){
			$list=array();	
		}
		$list[$c]=$c;
		

		
	}

	function get_js_man_class($frm=false){
		if($this->is_calendar_mode()){
			return "mw_input_elem_calendar";	
		}
		return "mw_input_elem_def";	
	}
	
	function get_value_for_input(){
		if(!$val=$this->get_value()){
			return "";
		}
		if($this->time_mode){
			if($val=$val+0){
				return $val;	
			}else{
				return "";	
			}
		}
		if($val=="0000-00-00"){
			return "";	
		}
		if($val=="0000-00-00 00:00:00"){
			return "";	
		}
		
		return addslashes($this->get_value());	
	}
	function is_calendar_mode(){
		return $this->calendar_mode;
	}
	
}

?>