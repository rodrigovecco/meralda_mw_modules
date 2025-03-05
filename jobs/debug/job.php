<?php
class  mwmod_mw_jobs_debug_job extends mwmod_mw_jobs_job{
	function __construct($jobs_man){
		$this->init("job",$jobs_man);
	}
	function exec_job(){
		echo "jsjs";
		return true;	
	}
	
}

?>