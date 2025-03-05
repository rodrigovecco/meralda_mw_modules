<?php

class mwmod_mw_date_man extends mw_apsubbaseobj{
	private $_months;
	private $_weekdays;
	private $_romannums;//hasta 4, se puede ampliar
	
	function __construct(){
		$this->set_mainap();
		$this->set_lngmsgsmancod("date");	
	}
	function get_sys_date($timeOrDate,$includehour=true){
		if(!$time=$this->checkTimeOrDate($timeOrDate)){
			return "";	
		}
		if($includehour){
			return date("Y-m-d H:i:s",$time);	
		}else{
			return date("Y-m-d",$time);	
		}
		
	}
	
	
	function checkTime($time){
		if(!is_numeric($time)){
			return false;
		}
		if($time>0){
			return $time;	
		}
		return false;
		
	}
	function checkTimeOrDate($timeOrDate){
		if(!$timeOrDate){
			return false;	
		}
		if(is_numeric($timeOrDate)){
			return $this->checkTime($timeOrDate);	
		}
		if(!is_string($timeOrDate)){
			return false;	
		}
		$timeOrDate=str_replace("/","-",$timeOrDate);
		
		if($timeOrDate=="0000-00-00"){
			return false;	
		}
		if($timeOrDate=="0000-00-00 00:00"){
			return false;	
		}
		if($timeOrDate=="0000-00-00 00:00:00"){
			return false;	
		}
		$a=explode(" ",$timeOrDate);
		if(!$date=$a[0]){
			return false;	
		}
		$d=explode("-",$date);
		if(sizeof($d)!=3){
			return false;	
		}
		if(!$time=strtotime($timeOrDate)){
			return false;	
		}
		if($time>0){
			return $this->checkTime($time);	
		}
		return false;
		
		
	}
	
	final function get_weekdays(){
		$this->_init_weekdays();
		return $this->_weekdays;
	}
	function new_date($str=false){
		$d=new mwmod_mw_date_date($str,$this);
		return $d;
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
	
	final function _init_weekdays(){
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
	final function init_roman_nums(){
		if(isset($this->_romannums)){
			return;	
		}
		$this->_romannums=$this->load_roman_nums();
	}
	function load_roman_nums(){
		$l=explode(",","I,II,III,IV");
		$r=array();
		$id=0;
		foreach($l as $rom){
			$id++;
			$r[$id]=$rom;
		}
			
		return $r;
			
	}
	
	final function get_roman_nums(){
		$this->init_roman_nums();
		return $this->_romannums;
			
	}
	final function get_roman_num($n){
		if(!$n=$n+0){
			return false;	
		}
		$this->init_roman_nums();
		return $this->_romannums[$n];
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
	function getSemesterFromMonth($n,&$rom=""){
		return $this->getYearPartFromMonth($n,6,$rom);
			
	}
	function getTrimesterFromMonth($n,&$rom=""){
		return $this->getYearPartFromMonth($n,3,$rom);
			
	}
	private function getYearPartFromMonth($n,$div,&$rom=""){
		if(!$n=round($n+0)){
			return false;	
		}
		if($n<1){
			$n=1;	
		}
		if($n>12){
			$n=12;	
		}
		$p=ceil($n/$div);
		$rom=$this->get_roman_num($p);
		return $p;
		
		
			
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