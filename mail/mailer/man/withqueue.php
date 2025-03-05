<?php
abstract class mwmod_mw_mail_mailer_man_withqueue extends mwmod_mw_mail_mailer_man_abs{
	function load_mail_queue_man(){
		if($man=$this->mainap->get_submanager("mailqueue")){
			return $man;	
		}
	}
	
}
?>