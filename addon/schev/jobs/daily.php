<?php
class  mwmod_mw_addon_schev_jobs_daily extends mwmod_mw_jobs_job{
	function __construct($jobs_man){
		$this->init("daily",$jobs_man);
		$this->timeout=120;
	}
	
	function exec_job(){
		//echo get_class($this->man);
		
		//echo "xxx";
		$r=$this->man->exec_items_from_cron();
		echo $r;
		return $r;
		/*
		$man=$this->man->daily_log_man;
		return $man->do_daily_log();
		*/
	}
	
}

?>