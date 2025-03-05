<?php
class  mwmod_mw_jobs_jobsmainman extends mw_apsubbaseobj{
	private $_managers=array();//
	var $never_log=false;
	private $_treedataman;
	private $_registered_managers_loaded;
	function mwmod_mw_jobs_jobsmainman($ap){
		$this->init($ap);
		
	}
	function load_managers_list_by_scan(){
		if(!$p=$this->get_exec_full_path()){
			return false;	
		}
		
		$helper=new mwmod_mw_ap_helper();
		if(!$list=$helper->file_man->get_sub_dirs($p)){
			return false;	
		}
		$r=array();
		foreach($list as $path=>$cod){
			if($man=$this->get_or_load_manager($cod)){
				$r[$cod]=$man;	
			}
		}
		return $r;
		
			
	}
	function update_managers_list_by_scan(){
		if(!$td=$this->get_treedata_item("managerslist")){
			return false;	
		}
		$td->set_data(array(),"managers");
		if($managers=$this->load_managers_list_by_scan()){
			foreach($managers as $cod=>$man){
				$td->set_data(get_class($man),"managers.{$cod}.class");	
			}
			//$td->set_data(array(),"managers");
		}
		$td->save();
		
		
		
		return $managers;
	}
	function get_managers_debug_info(){
		$r=array();
		if($items=$this->load_registered_managers()){
			foreach($items as $cod=>$item){
				$r[$cod]=$item->get_debug_data();	
			}
		}
		return $r;
	}
	final function load_registered_managers(){
		if(!$this->_registered_managers_loaded){
			$this->reload_registered_managers();
			$this->_registered_managers_loaded=true;
		}
		return $this->get_managers();
	}
	function reload_registered_managers(){
		$this->reload_registered_managers_jobsreg();
		$this->reload_registered_managers_managerslist();

	}
	function reload_registered_managers_managerslist(){
		if(!$td=$this->get_treedata_item("managerslist")){
			return false;	
		}
		if(!$list=$td->get_data("managers")){
			return false;	
		}
		if(!is_array($list)){
			return false;
		}
		foreach($list as $cod=>$d){
			$this->get_or_load_manager($cod);	
		}
	}
	
	function reload_registered_managers_jobsreg(){
		if(!$td=$this->get_treedata_item("jobsreg")){
			return false;	
		}
		if(!$list=$td->get_data()){
			return false;	
		}
		if(!is_array($list)){
			return false;
		}
		foreach($list as $cod=>$d){
			$this->get_or_load_manager($cod);	
		}
	}
	function set_ini_before_exec(){
		if($cfg=$this->mainap->get_cfg()){
			if($timezone=$cfg->get_value("timezone")){
				date_default_timezone_set($timezone);	
			}
		}
	}
	function exec_job_as_cron($mancod,$jobcod){
		if(!$man=$this->get_or_load_manager($mancod)){
			return false;	
		}
		$this->set_ini_before_exec();
		return $man->exec_job_as_cron($jobcod);
	}
	function get_treedata_item_for_man($man,$code="data",$path=false){
		$p="mans/".$man->cod;
		if($path){
			$p.="/".$path;	
		}
		
		if($td=$this->get_treedata_item($code,$p)){
			return $td;	
		}

			
	}

	function register_job_exec($item){
		if(!$td=$this->get_treedata_item("jobsreg")){
			return false;	
		}
		$cod=$item->jobs_man->cod.".".$item->cod;
		if($td->get_data($cod)){
			return true;
		}
		$td->set_data(get_class($item),$cod);
		$td->save();
		return true;
		//if($td
	}
	function clear_log(){
		if(!$pm=$this->get_logs_path_man()){
			return false;	
		}
		if(!$pm->file_exists("cronjobs.csv")){
			return false;	
		}
		if(!$path=$pm->check_and_create_path()){
			return false;	
		}
		unlink($path."/cronjobs.csv");
		return true;
			
	}
	function log_on_exec($item,$result){	
		if($this->never_log){
			return false;	
		}
		if(is_bool($result)){
			if($result){
				$result=1;	
			}else{
				$result=0;	
			}
		}
		$result=$result."";
		if(!$pm=$this->get_logs_path_man()){
			return false;	
		}
		//echo $pm->get_path();
		$addtitles=false;
		if(!$pm->file_exists("cronjobs.csv")){
			$addtitles=true;
		}
		if(!$path=$pm->check_and_create_path()){
			return false;	
		}
		
		$h=fopen($path."/cronjobs.csv", 'a');
		
		if($addtitles){
			
			fwrite($h,"sep=;\n");
			$data=array(
				"manager","job","date","result","time(secs)"
			);
			fputcsv($h,$data,";");
		}
		$data=array(
			$item->jobs_man->cod,$item->cod,date("Y-m-d H:i:s"),$result,$item->exec_as_cron_seconds
		);
		fputcsv($h,$data,";");
		fclose($h);
		chmod($path."/cronjobs.csv",0755);
		return true;

	}
	function get_logs_path_man(){
		if($pm=$this->mainap->get_logs_path_man()){
			return $pm->get_sub_path_man("cronjobs");	
		}
	}
	function get_treedata_item($code="data",$path=false){
		if($m=$this->get_treedataman()){
			return $m->get_datamanager($code,$path);	
		}
	}
	

	final function get_treedataman(){
		if(isset($this->_treedataman)){
			return 	$this->_treedataman;
		}
		if($m=$this->get_init_treedataman()){
			$this->_treedataman=$m;
			return 	$this->_treedataman;
		}
	}
	function get_init_treedataman(){
		$m= new mwmod_mw_data_tree_man("cronjobs");
		return $m;
	}
	
	final function get_managers(){
		return $this->_managers;	
	}
	final function add_manager($man){
		if(!$cod=$man->cod){
			return false;	
		}
		$this->_managers[$cod]=$man;
		return $man;
	}
	function get_or_load_manager($cod){
		if($man=$this->get_manager($cod)){
			return $man;	
		}
		if(!$man=$this->load_manager($cod)){
			return false;	
		}
		if(is_object($man)){
			if(is_a($man,"mwmod_mw_jobs_jobsman")){
				return $this->add_manager($man);	
			}
		}
		
		
	}
	function load_manager($cod){
		if(!$apsubman=$this->mainap->get_submanager($cod)){	
			return false;
		}
		$interfaces = class_implements($apsubman);

		if (isset($interfaces['mwmod_mw_jobs_apsubmaninterface'])) {
			return $apsubman->jobs_man;
		}
		return false;
			
		//if(impl
	}
	final function get_manager($cod){
		if(!$cod){
			return false;	
		}
		return $this->_managers[$cod]??null;	
	}
	function get_exec_path(){
		return "cronjobs";	
	}
	function get_exec_full_path(){
		$p=$this->mainap->get_path("public")."/".$this->get_exec_path();
		return $p;
	}
	
	final function init($ap){
		$this->set_mainap($ap);
	}
	/*
	final function __get_priv_mode(){
		return $this->mode; 	
	}
	*/
	
}

?>