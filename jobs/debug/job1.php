<?php
class  mwmod_mw_jobs_debug_job1 extends mwmod_mw_jobs_job{
	function mwmod_mw_jobs_debug_job1($jobs_man){
		$this->init("job1",$jobs_man);
		$this->log_on_exec_enabled=true;
	}
	function exec_job(){
		echo "qqq";
		return true;	
	}
	
}

?>