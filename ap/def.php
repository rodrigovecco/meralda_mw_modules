<?php
class mwmod_mw_ap_def  extends mwmod_mw_ap_apbase{
	function allow_submancmd(){
		return true;	
	}
	
	function create_submanager_fixcontent(){
		$man=new mwmod_mw_data_fixcontent_main();
		return $man;	
	}
	function create_submanager_db(){
		$man=new mwmod_mw_db_mysqli_dbman($this);
		return $man;	
	}
	
	function create_submanager_mailqueue(){
		
		$man=new mwmod_mw_mail_queue_systemman($this);
		return $man;	
	}
	function create_submanager_sysmail(){
		
		$man=new mwmod_mw_mail_mailer_man_systemwithqueue($this);
		return $man;	
	}
	function on_shutdown(){
		if($this->cfg->get_value_boolean("register_lng_msg")){
			if($man=$this->get_submanager("lng")){
				$man->re_write_def_msgs_mans_if_needed();
			}
		}
			
	}

	
}

?>