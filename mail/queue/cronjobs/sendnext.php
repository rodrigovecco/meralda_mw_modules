<?php
class mwmod_mw_mail_queue_cronjobs_sendnext extends mwmod_mw_jobs_job{
	
	function __construct($jobs_man){
		$this->init("sendnext",$jobs_man);
		$this->log_on_exec_enabled=false;
	}
	function exec_job(){
		$num=0;
		if($msgs=$this->man->send_queue_as_cronjob($this)){
			$num=sizeof($msgs);	
		}
		ob_end_clean();
		echo "Messages sent: $num\n";
		return $num;
	}
	
}
?>