<?php
class mwmod_mw_mail_imap_result_overview extends mwmod_mw_mail_imap_result_abs{
	var $lim=false;
	var $start=1;
	var $last;
	var $messages;
	function __construct($lim=false,$start=1,$conn){
		$this->lim=$lim;
		$this->start=$start;
		$this->set_conn($conn);
	}
	
	function get_messages_info(){
		if(!$items=$this->get_messages()){
			return false;	
		}
		$r=array();
		foreach($items as $id=>$item){
			$r[$id]=$this->get_message_info($item);	
		}
		return $r;
	}
	function get_message_info($item){
		$r=array();
		$r["uid"]=$item->uid;
		$r["date"]=$item->date;
		$r["subject"]=$item->subject;
		$r["from"]=$item->from;
		$r["to"]=$item->to;
		$r["message_id"]=$item->message_id;
		//$r["overview"]=$item->obj2array($item->overview);
		return $r;

	
	}
	function get_msgs_num(){
		$this->init_result();
		if($this->mailbox_info){
			return 	$this->mailbox_info->Nmsgs;
		}
		return 0;
	}
	function get_debug_info(){
		$r=array();
		$r["ok"]=$this->result_ok();
		$r["lim"]=$this->lim;
		$r["start"]=$this->start;
		$r["last"]=$this->last;
		$r["msgnum"]=$this->get_msgs_num();
		
		$r["mailbox_info"]=$this->obj2array($this->mailbox_info);
		if($items=$this->get_messages()){
			$r["messages"]=array();				
			foreach($items as $id=>$item){
				$r["messages"][$id]=$item->get_debug_info();	
			}
		}
		
		return $r;
			
	}
	function get_messages(){
		if(isset($this->messages)){
			return $this->messages;	
		}
		$this->messages=false;
		$this->init_result();
		if(!is_array($this->raw_result)){
			return false;
		}
		if(!sizeof($this->raw_result)){
			return false;	
		}
		reset($this->raw_result);
		$this->messages=array();
		foreach($this->raw_result as $o){
			$msg=new mwmod_mw_mail_imap_result_msg($this);
			$msg->set_overview($o);
			if($id=$msg->uid){
				$this->messages[$id]=$msg;	
			}
		}
		return $this->messages;	
		
			
	}
	function load_result(){
		$start=$this->start;
		$lim=$this->lim;
		
		$start=round($start+0);
		if($start<1){
			$start=1;	
		}
		if(!$mbox=$this->get_imap_stream()){
			return false;
		}
		if(!$MC = imap_check($mbox)){
			return false;	
		}
		$this->mailbox_info=$MC;
		
		if(!$num=$MC->Nmsgs){
			return false;
		}
		$lim=round($lim+0);
		if($lim>0){
			$last=$start+($lim-1);
		}else{
			$last=$num;
			$lim=false;
		}
		if($last>$num){
			$last=$num;
		}
		if($start>$last){
			$last=$start;
		}
		$this->start=$start;
		$this->lim=$lim;
		$this->last=$last;
		
		if($this->raw_result = imap_fetch_overview($mbox,"{$start}:{$last}")){
			return true;
		}
		return false;	
	}
	
	
}
?>