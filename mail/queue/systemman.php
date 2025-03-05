<?php
class mwmod_mw_mail_queue_systemman extends mwmod_mw_mail_queue_manabs{
	function __construct($ap){
		$this->init("mail_queue",$ap);
	}
	function add_cron_jobs($man){
		//
		//
		$man->add_item(new mwmod_mw_mail_queue_cronjobs_sendnext($man));	
	}
	
	function load_mailer_man(){
		if($mailerman=$this->mainap->get_submanager("sysmail")){
			return $mailerman;
		}
	}
	
	
}

?>