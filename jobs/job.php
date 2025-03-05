<?php
abstract class  mwmod_mw_jobs_job extends mw_apsubbaseobj{
	private $man;//app sub manager
	private $jobs_man; //mwmod_mw_jobs_jobsman
	
	
	private $cod;
	var $allow_exec=true;
	var $log_on_exec_enabled=false;
	var $stats_enabled=true;
	var $result=0;
	var $exec_as_cron_start;
	var $exec_as_cron_end;
	var $exec_as_cron_seconds;
	var $stats_short_log_max_num=5;
	var $timeout=30;//seconds
	var $dnstimeout=15;//seconds
	var $tries=1;
	
	
	function allow_exec(){
		return $this->allow_exec;	
	}
	function log_on_exec_enabled(){
		return $this->log_on_exec_enabled;	
	}
	function allow_exec_as_cron(){
		return $this->allow_exec();	
	}
	function exec_job(){
		//extender
		return true;	
	}
	function log_on_exec($result=true){
		return $this->jobs_man->log_on_exec($this,$result);
	}
	function exec_job_as_cron(){
		if(!$this->allow_exec_as_cron()){
			return false;
		}
		$this->before_exec_as_cron();
		$this->result=$this->exec_job();
		/*
		if($this->log_on_exec_enabled()){
			$this->log_on_exec($this->result);
		}
		*/
		$this->after_exec_as_cron();
		return $this->result;
		
	}
	
	
	final function before_exec_as_cron(){
		//no extender
		$this->exec_as_cron_start=microtime(true);
	}
	final function after_exec_as_cron(){
		$this->exec_as_cron_end=microtime(true);
		$this->exec_as_cron_seconds=$this->exec_as_cron_end-$this->exec_as_cron_start;
		if($this->stats_enabled){
			$this->register_exec_stats();	
		}
		if($this->log_on_exec_enabled()){
			$this->log_on_exec($this->result);
		}
		$this->register_job_exec();
	}
	function register_job_exec(){
		$this->jobs_man->register_job_exec($this);
	}
	function register_exec_stats(){
		if(!$td=$this->get_treedata_item("log")){
			return false;	
		}
		if(!$num=$td->get_data("execs_num")+0){
			$td->set_data(date("Y-m-d H:i:s"),"first_exec");	
		}
		$td->set_data($num+1,"execs_num");
		$td->set_data(date("Y-m-d H:i:s"),"last_exec");
		$sec=$this->exec_as_cron_seconds+0;
		$max=$td->get_data("longest_exec.exec_time")+0;
		$thisexec_info=array(
			"exec_time"=>$sec,
			"date"=>date("Y-m-d H:i:s"),
			"result"=>$this->result,
			
		);
		
		if($sec>$max){
			$td->set_data($thisexec_info,"longest_exec");		
		}
		$td->set_data($thisexec_info,"last_exec");
		$history=$td->get_data("short_log");
		if(!is_array($history)){
			$history=array();
		}
		$list=array_reverse($history);
		$histtoadd=$this->stats_short_log_max_num-1;
		$new_list=array();
		$x=1;
		foreach($list as $hisdata){
			if($x>$histtoadd){
				break;	
			}
			$new_list[]=$hisdata;
			$x++;
			
		}
		$list=array_reverse($new_list);
		$x=1;
		$new_list=array();
		foreach($list as $hisdata){
			$new_list[$x]=$hisdata;
			$x++;
			
		}
		$new_list[$x]=$thisexec_info;
		$td->set_data($new_list,"short_log");
		$td->save();
		return true;
		
		
		
			
	}
	function get_treedata_item($code="data",$path=false){
		return $this->jobs_man->get_treedata_item_for_job($this,$code,$path);
	}
	
	function get_debug_data(){
		$r=array(
			"class"=>get_class($this),
			"exexfilefull"=>$this->get_exec_file_full_path(),
			"exexfile"=>$this->get_exec_file_rel_path(),
			"cronjobcmd"=>$this->get_exec_cmd_txt(),
		);
		$url=$this->get_exec_url();
		$r["exec_debug_url"]="<a href='$url' target='_blank'>$url</a>";
		if($td=$this->get_treedata_item("log")){
			$r["log"]=$td->get_data();
		}
		
		return $r;
	}

	function get_exec_url_full(){
		$r="";
		if($_SERVER['HTTPS']){
			$r="https://";	
		}else{
			$r="http://";	
		}
		$r.=$_SERVER['HTTP_HOST'].$this->get_exec_url();
		return $r;
	
	}

	function get_exec_cmd_txt(){
		//$r="lynx -dump '".$this->get_exec_url_full()."' > /dev/null";

		$options="--tries=".$this->tries;
		$options.=" --timeout=".$this->timeout;
		$options.=" --dns-timeout=".$this->dnstimeout;
		$r="wget $options -qO- ".$this->get_exec_url_full()." > /dev/null";
		return $r;
	
	}
	function get_exec_file_rel_path(){
		return $this->jobs_man->cod."/".$this->cod.".php";	
	}
	function get_exec_url(){
		if(!$this->jobs_man->jobs_main_man){
			return false;	
		}
		$r="/".$this->jobs_man->jobs_main_man->get_exec_path();
		$r.="/".$this->get_exec_file_rel_path();
		return $r;
	}
	
	function get_exec_file_full_path(){
		if(!$this->jobs_man->jobs_main_man){
			return false;	
		}
		$r=$this->jobs_man->jobs_main_man->get_exec_full_path();
		return $r;
	}
	
	final function init($cod,$jobs_man,$man=false){
		if(!$man){
			$man=$jobs_man->man;	
		}
		$this->cod=$cod;
		$this->jobs_man=$jobs_man;
		$this->man=$man;
		$this->set_lngmsgsmancod($man->lngmsgsmancod);	
	}
	
	final function __get_priv_cod(){
		return $this->cod;	
	}
	final function __get_priv_jobs_man(){
		return $this->jobs_man;	
	}
	final function __get_priv_man(){
		return $this->man;	
	}
	
	
}

?>