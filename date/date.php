<?php

class mwmod_mw_date_date extends mwmod_mw_date_dateabs{
	function __construct($str_date=false,$man=false){
		$this->init($man);
		if($str_date){
			$this->set_time_by_str($str_date);	
		}
	}
	function diff_days($date,$includehour=false,$absolute=false){
		if(!$interval=$this->diff($date,$includehour,$absolute)){
			return false;
		}
		return $interval->format("%r%a")+0;
	}
	function get_diff_debug_data($date){
		$r["datefull"]=$this->get_sys_date(true);
		$r["diff_days"]=$this->diff_days($date);
		$r["diff_days_with_hours"]=$this->diff_days($date,true);
		if($interval=$this->diff($date,false,false)){
			$rr=array(
				"y"=>$interval->y,
				"m"=>$interval->m,
				"d"=>$interval->d,
				"h"=>$interval->h,
				"i"=>$interval->i,
				"s"=>$interval->s,
				"Td"=>$interval->format("%r%a")+0,
			);
			$r["difnohour"]=$rr;
		}
		if($interval=$this->diff($date,true,false)){
			$rr=array(
				"y"=>$interval->y,
				"m"=>$interval->m,
				"d"=>$interval->d,
				"h"=>$interval->h,
				"i"=>$interval->i,
				"s"=>$interval->s,
				"Td"=>$interval->format("%r%a")+0,
			);
			$r["dif"]=$rr;
		}
		return $r;
	}
	
	function diff($date,$includehour=true,$absolute=false){
		if(!$date=$this->get_other_mw_date($date)){
			return false;	
		}
		if($includehour){
			$dt=$date->DateTime;
			$ref=$this->DateTime;
			
		}else{
			$dt=$date->DateTimeNoHour;		
			$ref=$this->DateTimeNoHour;		
		}
		if(!$ref){
			return false;	
		}
		if(!$dt){
			return false;	
		}
		return $ref->diff($dt,$absolute);
		
	}
	function get_other_mw_date($date=false){
		if($date){
			if(is_object($date)){
				if(is_a($date,"mwmod_mw_date_date")){
					return $date;	
				}
			}
		}
		$r=new mwmod_mw_date_date(false,$this->dateman);
		if($date){
			if(is_string($date)){
				$r->set_time_by_str($date);	
			}elseif(is_numeric($date)){
				$r->set_time($date);		
			}
		}
		return $r;	
	}
	
	
	function get_js_date($includehour=true){
		if(!$str=$this->get_js_str_date($includehour)){
			return NULL;
		}
		$obj=new mwmod_mw_jsobj_newobject("Date",array("datestr"=>$str),true);
		return $obj;
	}
	function get_debug_data(){
		$d=array(
			"time"=>$this->get_time(),
			"time_no_hour"=>$this->get_time_no_hour(),
			//"dateTime"=>$this->dateTime->format("Y-m-d H:i:s"),
			//"DateTimeNoHour"=>$this->DateTimeNoHour->format("Y-m-d H:i:s"),
			"sys_date"=>$this->get_sys_date(false),
			"sys_date_hours"=>$this->get_sys_date(true),
			"js_str_date"=>$this->get_js_str_date(false),
			"js_str_date_hours"=>$this->get_js_str_date(true),
			"short"=>$this->get_date_short(),
			"long"=>$this->get_date_long(),
			"parts"=>$this->get_parts(),
			
			
		
		);
		return $d;
	}
	function get_cus_formated($format){
		if(!$params=$this->get_parts()){
			return "";	
		}
		if(is_array($params)){
			foreach($params as $cod=>$v){
				$format=str_replace("%{$cod}%",$v,$format);
			}
		}
		return $format;
	}
	function get_datetime_short(){
		if(!$p=$this->get_parts()){
			return "";	
		}
		return $this->lng_get_msg_txt("format_datetimeshort","%j%-%M%-%Y% %H%:%i%",$p)." ";
	}
	function get_date_short(){
		if(!$p=$this->get_parts()){
			return "";	
		}
		return $this->lng_get_msg_txt("format_dateshort","%j%-%M%-%Y%",$p);
	}
	function get_date_med(){
		if(!$p=$this->get_parts()){
			return "";	
		}
		return $this->lng_get_msg_txt("format_datemed","%j% de %F% %Y%",$p);
	}
	
	function get_date_long(){
		if(!$p=$this->get_parts()){
			return "";	
		}
		return $this->lng_get_msg_txt("format_datelong","%l%, %j% de %F% de %Y%",$p);
	}
	function load_parts(){
		if(!$time=$this->get_time()){
			return false;	
		}
		$cods=array('d','D','j','l','N','S','w','z','W','F','m','M','n','t','L','o','Y','y','a','A','B','g','G','h','H','i','s','u','e','I','O','P','T','Z','c','r','U'
);
		$p=array();
		foreach($cods as $c){
			$p[$c]=date($c,$time);	
		}
		$p["_M"]=$p["M"];
		$p["_F"]=$p["F"];
		$p["_D"]=$p["D"];
		$p["_l"]=$p["l"];
		if($m=$this->dateman->get_month($p["n"])){
			$this->month=$m;
			$p["M"]=$m->shortname;	
			$p["F"]=$m->name;	
		}
		if($wd=$this->dateman->get_weekday($p["N"])){
			$this->weekday=$wd;
			$p["D"]=$wd->shortname;	
			$p["l"]=$wd->name;	
		}
		$p["SEMESTERrom"]="";
		$p["SEMESTER"]=$this->dateman->getSemesterFromMonth($p["n"],$p["SEMESTERrom"]);
		$p["TRIMESTERrom"]="";
		$p["TRIMESTER"]=$this->dateman->getTrimesterFromMonth($p["n"],$p["TRIMESTERrom"]);
		return $p;
		
	}
	function get_semester($rom=false){
		if($rom){
			return $this->get_part("SEMESTERrom");	
		}
		return $this->get_part("SEMESTER");	
		
	}
	function get_trimester($rom=false){
		if($rom){
			return $this->get_part("TRIMESTERrom");	
		}
		return $this->get_part("TRIMESTER");	
		
	}
	function get_js_str_date($includehour=true){
		if(!$time=$this->get_time()){
			return NULL;	
		}
		if($includehour){
			return date("Y/m/d H:i:s",$time);	
		}else{
			return date("Y/m/d",$time);	
		}
		
	}
	
	function get_sys_date($includehour=true){
		if(!$time=$this->get_time()){
			return "";	
		}
		if($includehour){
			return date("Y-m-d H:i:s",$time);	
		}else{
			return date("Y-m-d",$time);	
		}
		
	}
	
	function get_time(){
		if($this->is_valid()){
			return $this->time;	
		}
	}
	function is_valid(){
		return $this->valid;
	}
	function set_time_by_str($date){
		$this->unset_data();
		if(!$date){
			return false;	
		}
		if(!is_string($date)){
			return false;	
		}
		if($date=="0000-00-00"){
			return false;	
		}
		if($date=="0000-00-00 00:00"){
			return false;	
		}
		if($date=="0000-00-00 00:00:00"){
			return false;	
		}
		if(!$time=strtotime($date)){
			return false;	
		}
		return $this->set_time($time);
		
		
		
	}
	function now(){
		return $this->set_time(time());	
	}
	
	function set_time($time){
		$this->unset_data();
		if($time){
			$this->time=$time;
			$this->valid=true;
			return $time;	
		}
	}
	
	
	final function get_weekdays(){
		$this->_init_weekdays();
		return $this->_weekdays;
	}
	
	function load_weekdays(){
		$sh=explode(",","lun,mar,mie,jue,vie,sáb,dom");
		$l=explode(",","Lunes,Martes,Miércoles,Jueves,Viernes,Sábado,Domingo");
		$r=array();
			
			

		for($x=0;$x<7;$x++){
			$id=$x+1;
			$shortname=$this->lng_get_msg_txt("weekday_{$id}_short",$sh[$x]);
			$name=$this->lng_get_msg_txt("weekday_{$id}_long",$l[$x]);
			$r[$id]=new mwmod_mw_date_weekday_def($id,$shortname,$name,$this);
		}
		return $r;
			
	}
	
	private function _init_weekdays(){
		if(isset($this->_weekdays)){
			return;	
		}
		$this->_weekdays=$this->load_weekdays();
	}
	final function get_weekday($n){
		if(!$n=$n+0){
			return false;	
		}
		$this->_init_weekdays();
		return $this->_weekdays[$n];
	}
	

	
	
	
	//////////
	final function get_months(){
		$this->_init_months();
		return $this->_months;
	}
	function load_months(){
		$sh=explode(",","ene,feb,mar,abr,may,jun,lul,ago,sep,oct,nov,dic");
		$l=explode(",","Enero,Febrero,Marzo,Abril,Mayo,Junio,Julio,Agosto,Septiembre,Octubre,Noviembre,Diciembre");
		$r=array();
		
		for($x=0;$x<12;$x++){
			$id=$x+1;
			$shortname=$this->lng_get_msg_txt("month_{$id}_short",$sh[$x]);
			$name=$this->lng_get_msg_txt("month_{$id}_long",$l[$x]);
			
			$r[$id]=new mwmod_mw_date_month_def($id,$shortname,$name,$this);
		}
		return $r;
			
	}
	private function _init_months(){
		if(isset($this->_months)){
			return;	
		}
		$this->_months=$this->load_months();
	}
	final function get_month($n){
		if(!$n=$n+0){
			return false;	
		}
		$this->_init_months();
		return $this->_months[$n];
	}
	
}
?>