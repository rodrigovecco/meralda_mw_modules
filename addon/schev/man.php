<?php
class mwmod_mw_addon_schev_man extends mwmod_mw_manager_manwidthtypes implements mwmod_mw_jobs_apsubmaninterface{
	private $schedule_tbl_man;
	private $schedule_tbl_name;
	private $jobs_man;
	public $ui_new_by_type=false;
	
	function __construct($code,$ap,$tblname=false){
		$this->init($code,$ap,$tblname);
		
		//$this->enable_treedata();
		$this->set_lngmsgsmancod("schev");
		$this->set_schedule_tbl_name("schev_events_cfg");	
	}
	function get_exec_sumary_period_str($data){
		if(!is_array($data)){
			return false;	
		}
		$helper= new mwmod_mw_ap_helper();
		$start_date=$helper->dateman->new_date($data["start_date"]);
		$end_date=$helper->dateman->new_date($data["end_date"]);
		if((!$start_date->is_valid())and(!$end_date->is_valid())){
			return  $this->lng_get_msg_txt("permanently","Permanentemente");	
		}
		$p=array("from"=>$start_date->get_date_short(),"to"=>$end_date->get_date_short());
		/*
		if((!$start_date->is_valid())and(!$end_date->is_valid())){
			
			return  $this->lng_get_msg_txt("permanently","Permanentemente");	
		}
		*/
		if(($start_date->is_valid())and($end_date->is_valid())){
			
			return  $this->lng_get_msg_txt("FromDATEtoDATE","Desde %from% hasta %to%",$p);	
		}
		if((!$start_date->is_valid())and($end_date->is_valid())){
			
			return  $this->lng_get_msg_txt("UntilDATE","Hasta %to%",$p);	
		}
		if(($start_date->is_valid())and(!$end_date->is_valid())){
			
			return  $this->lng_get_msg_txt("FromDATE","Desde %from%",$p);	
		}
		
		
		
		
		
/*
		$q->where->add_where("(({$tbl}.start_date<='$date')or({$tbl}.start_date='0000-00-00'))");
		$q->where->add_where("(({$tbl}.end_date>='$date')or({$tbl}.end_date='0000-00-00'))");

*/
		
		
	}
	function get_exec_sumary_dates_str($data){
		if(!$list=$this->get_exec_sumary_dates_list($data)){
			return $this->lng_get_msg_txt("no_dates_scheduled","No hay fechas programadas");	
		}
		$helper= new mwmod_mw_ap_helper();
		return $helper->concat_lng_strings($list);
		
		return implode(", ",$list);
		
	}
	function debug_sumary_dates(){
		$list=array();
		$fulllist=array();
		$years=array(0,2014);
		$months=array(0,1);
		$days=array(0,7);
		$wdays=array(0,3);
		$x=0;
		foreach($years as $y){
			reset($months);
			foreach($months as $m){	
				reset($days);
				foreach($days as $d){
					reset($wdays);
					foreach($wdays as $wd){
						$x++;
						$dd=array(
							"year"=>$y,
							"month"=>$m,
							"day"=>$d,
							"weekday"=>$wd,
						
						);
						$list[$x]=$dd;
						$dd["str"]=$this->get_exec_sumary_date_str($dd);
						$fulllist[$x]=$dd;
					}
				}
			}
						
		}
		return $fulllist;
		
			
	}
	function get_exec_sumary_date_str($data){
		if(!is_array($data)){
			return false;
		}
		$year=$data["year"]+0;
		$month=$data["month"]+0;
		$day=$data["day"]+0;
		$weekday=$data["weekday"]+0;
		//mw_array2list_echo($data);
		if((!$year)and(!$month)and(!$day)and(!$weekday)){
			return 	$this->lng_get_msg_txt("everyday","Todos los días");
		}
		$helper= new mwmod_mw_ap_helper();
		if($month){
			if(!$month_man=$helper->dateman->get_month($month)){
				return 	$this->lng_get_msg_txt("invalid_month","Mes no válido");	
			}
		}
		if($weekday){
			if(!$weekday_man=$helper->dateman->get_weekday($weekday)){
				return 	$this->lng_get_msg_txt("invalid_weekday","Día de la semana no válido");	
			}
		}
		$r=array();
		if((!$year)and($month_man)and($day)and($weekday_man)){
			$p=array(
				"D"=>$day,
				"M"=>$month_man->name,
				"WD"=>$weekday_man->name,
				
			);
			return $this->lng_get_msg_txt("All_D_of_M_that_are_WD","Todos los %D% de %M% que sean %WD%",$p);	
				
		}
		
		//if((!$year)or((!$day)and(!$weekday_man))){
		if((!$year)or((!$day)or(!$weekday_man))){
			$r[]=$this->lng_get_msg_txt("All_the","Todos los");
		}else{
			$r[]=$this->lng_get_msg_txt("The","Los");	
		}

		
		if(($day)and($weekday_man)){
			$p=array(
				"D"=>$day,
				"WD"=>$weekday_man->name,
				
			);
			$r[]=$this->lng_get_msg_txt("D_that_are_WD","días %D% que sean %WD%",$p);	
		}elseif((!$day)and($weekday_man)){
			$r[]=$weekday_man->name;	
		}elseif(($day)and(!$weekday_man)){
			$p=array(
				"D"=>$day,
			);
			if(!$month_man){
				$r[]=$this->lng_get_msg_txt("days_D","días %D%",$p);		
			}else{
				$r[]=$day;	
			}
		}else{
			$r[]=$this->lng_get_msg_txt("days","días");		
		}
		if($month_man){
			$r[]=$this->lng_get_msg_txt("of","de")." ".$month_man->name;			
		}
		
		if($year){
			$r[]=$this->lng_get_msg_txt("of","de")." ".$year;			
		}
		
		return implode(" ",$r);
	}
	
	function get_exec_sumary_dates_list($data){
		$ok=false;
		if(is_array($data)){
			if(sizeof($data)>0){
				$ok=true;	
			}
		}
		if(!$ok){
			return false;	
		}
		$list=array();
		foreach($data as $d){
			if($s=$this->get_exec_sumary_date_str($d)){
				$list[]=$s;	
			}
		}
		if(sizeof($list)){
			return $list;	
		}
	}
	function exec_items_from_cron(){
		
		if(!$items=$this->get_items_to_exec_on_time(time())){
			return 0;
		}
		$r=0;
		foreach($items as $item){
			if($item->exec_from_cron()){
				$r++;	
			}
		}
		//mw_array2list_echo($items);
		return $r;
		
	}
	function create_jobs_man(){
		$man=new mwmod_mw_jobs_jobsman($this);
		$man->add_item(new mwmod_mw_addon_schev_jobs_daily($man));
	
		
		return $man;
	}
	final function __get_priv_jobs_man(){
		if(!isset($this->jobs_man)){
			if($man=$this->create_jobs_man()){
				$this->jobs_man=$man;	
			}
		}
		return $this->jobs_man;
	}
	
	function get_items_to_exec_on_time($time){
		if(!$q=$this->get_items_to_exec_on_time_query($time)){
			return false;	
		}
		return $this->get_items_by_query($q);
	}
	
	function get_items_to_exec_on_time_query($time){
		if(!$time){
			return false;	
		}
		
		if(!$tblman=$this->get_tblman()){
			return false;	
		}
		if(!$tblman_cfg=$this->__get_priv_schedule_tbl_man()){
			return false;	
		}
		
		$tbl_cfg=$tblman_cfg->tbl;
		$tbl=$tblman->tbl;
		if(!$q=$tblman->new_query()){
			return false;	
		}
		$date=	date("Y-m-d",$time);
		
		$q->select->add_select("{$tbl}.*");
		
		$q->where->add_where("{$tbl}.active=1");
		$q->where->add_where("(({$tbl}.start_date<='$date')or({$tbl}.start_date='0000-00-00'))");
		$q->where->add_where("(({$tbl}.end_date>='$date')or({$tbl}.end_date='0000-00-00'))");
		$y=date("Y",$time);
		$m=date("n",$time);
		$d=date("j",$time);
		$wd=date("N",$time);
		$q->where->add_where("(({$tbl_cfg}.year='$y')or({$tbl_cfg}.year='0'))");
		$q->where->add_where("(({$tbl_cfg}.month='$m')or({$tbl_cfg}.month='0'))");
		$q->where->add_where("(({$tbl_cfg}.day='$d')or({$tbl_cfg}.day='0'))");
		$q->where->add_where("(({$tbl_cfg}.weekday='$wd')or({$tbl_cfg}.weekday='0'))");
		
		
		$join=$q->from->add_from_join($tblman_cfg->tbl,$tblman->tbl.".id");
		$join->inner_join_field="event_id";
		
		$q->group->add_group($tblman->tbl.".id");
		
		
		
		
		return $q;
		
	}
	
	function create_ui($cod,$parent){
		$ui=new mwmod_mw_addon_schev_ui_main($cod,$parent,$this);
		return $ui;
	}
	function is_allowed_ui($ui){
		return $ui->allow("mainadmin");	
	}
	function load_schedule_tbl_man(){
		if(!$name=$this->__get_priv_schedule_tbl_name()){
			return false;	
		}
		if(!$db=$this->mainap->get_submanager("db")){
			return false;	
		}
		if(!$tblman=$db->get_tbl_manager($name)){
			return false;
		}
		

		return $tblman;
			
	}
	final function set_schedule_tbl_name($tbl){
		$this->schedule_tbl_name=$tbl;	
	}
	final function __get_priv_schedule_tbl_name(){
		return $this->schedule_tbl_name;	
	}
	final function __get_priv_schedule_tbl_man(){
		if(isset($this->schedule_tbl_man)){
			return $this->schedule_tbl_man;
		}
		$this->schedule_tbl_man=false;
		if($man=$this->load_schedule_tbl_man()){
			$this->schedule_tbl_man=$man;	
		}
		return $this->schedule_tbl_man; 	
	}


	
	

}
?>