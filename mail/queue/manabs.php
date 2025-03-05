<?php
abstract class mwmod_mw_mail_queue_manabs extends mwmod_mw_manager_man  implements mwmod_mw_jobs_apsubmaninterface{
	private $mailer_man;
	var $sending_msgs_ids=array();
	var $sending_msgs=array();
	var $numMsgToSend=1;
	var $numMsgSentAndTry=0;
	var $maxFails=3;
	var $debug_mode=false;
	var $dontMarkMsgAsSent=false;
	
	private $jobs_man;
	var $jsDebugResponse;
	
	final function __get_priv_jobs_man(){
		if(!isset($this->jobs_man)){
			$this->jobs_man=false;
			if($man=$this->create_jobs_man()){
				$this->jobs_man=$man;	
			}
		}
		return $this->jobs_man;
	}
	function add_cron_jobs($man){
		//	
	}

	function create_jobs_man(){
		$man=new mwmod_mw_mail_queue_cronjobs_man($this);
		$this->add_cron_jobs($man);
		/*
		$man->add_item(new mwmod_mw_jobs_debug_job($man));
		$man->add_item(new mwmod_mw_jobs_debug_job1($man));
		
		*/
		
		return $man;
	}

	function send_queue(){
		$this->reset_sending_msgs();
		$num=$this->numMsgToSend;
		$this->numMsgSentAndTry=0;
		
		while(($msg=$this->get_next_msg_to_send())and($num>0)){
			$this->send_msg($msg);
			$this->numMsgSentAndTry++;
			$num--;
			if($num<=0){
				break;	
			}
		}
		if($this->jsDebugResponse){
			$this->jsDebugResponse->set_prop("msgssent",$this->numMsgSentAndTry);	
		}
		
		return $this->sending_msgs;
			
	}
	function get_next_msg_to_send(){
		$query=$this->tblman->new_query();
		if(sizeof($this->sending_msgs_ids)){
			$idsnot=$query->where->add_where_num_list("id",$this->sending_msgs_ids);
			$idsnot->not=true;
		}
		$query->where->add_where($this->tblman->tbl.".status=2");
		$query->order->add_order_desc($this->tblman->tbl.".priority");
		$query->order->add_order($this->tblman->tbl.".fail_num");
		$query->order->add_order($this->tblman->tbl.".id");
		
		
		$query->limit->set_limit(1);
		//echo $query->get_sql()."<br>";
		if($items=$this->get_items_by_query($query)){
			foreach($items as $item){
				return $item;	
			}
		}
		
	}
	function send_msg($msg){
		$id=$msg->get_id();
		$this->sending_msgs_ids[]=$id;	
		$this->sending_msgs[$id]=$msg;
		if($msg->send()){
			if(!$this->dontMarkMsgAsSent){
				$msg->do_delete();	
			}
		}
		if($this->jsDebugResponse){
			if($d=$msg->sending_debug_data){
				$d["htmldebugdata"]=nl2br($d["infostr"]);
				$js=$this->jsDebugResponse->get_array_prop("msgssentinfo");
				$js->add_data($d);
			}
		}
		
			
	}
	function get_max_fails(){
		return $this->maxFails;
	}
	
	function reset_sending_msgs(){
		$this->sending_msgs_ids=array();	
		$this->sending_msgs=array();	
	}
	function cron_enabled(){
		return true;	
	}
	function send_queue_as_cronjob($job=false){
		if(!$this->cron_enabled()){
			return false;	
		}
		return $this->send_queue();
	}
	
	function load_mailer_man(){
		/*
		extender
		if($mailerman=$this->mainap->get_submanager("sysmail")){
			return $mailerman;
		}
		*/
	}
	function load_msgs_to_send_num(){
		$query=$this->tblman->new_query();
		$query->where->add_where($this->tblman->tbl.".status=2");
		return $query->get_total_regs_num();
			
	}
	
	function queue_msg($temp_msg){
		if(!$man=$this->get_tblman()){
			return false;	
		}
		if(!$temp_msg->check()){
			return false;	
		}
		
		$data=array();
		$data["date"]=$man->format_time();
		$data["status"]=1;
		$data["priority"]=$temp_msg->priority;
		
		if(!$dbitem=$man->insert_item($data)){
			return false;	
		}
		if(!$item=$this->get_or_create_item($dbitem)){
			return false;	
		}
		if($temp_msg->prepare_queue_msg($item)){
			$item->set_ready_to_send();
			return $item;	
		}
		
	}
	function create_item($tblitem){
		
		$item=new mwmod_mw_mail_queue_item($tblitem,$this);
		return $item;
	}
	function new_phpmailer(){
		if(!$man=$this->get_mailer_man()){
			return false;	
		}
		return $man->new_phpmailer();
			
	}
	function new_temp_msg(){
		if(!$man=$this->get_mailer_man()){
			return false;	
		}
		return $man->new_temp_msg();
			
	}

	
	final function __get_priv_mailer_man(){
		if(isset($this->mailer_man)){
			return $this->mailer_man;	
		}
		if(!$this->mailer_man=$this->load_mailer_man()){
			$this->mailer_man=false;	
		}
		return $this->mailer_man; 	
	}
	
	function get_mailer_man(){
		return $this->__get_priv_mailer_man();	
	}

	
	
}

?>