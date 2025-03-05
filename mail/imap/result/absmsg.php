<?php
abstract class mwmod_mw_mail_imap_result_absmsg extends mw_apsubbaseobj{
	private $conn;
	private $resultset;
	
	var $overview;
	var $uid;
	var $date;
	var $subject;
	var $from;
	var $to;
	var $message_id;
	
	var $body;
	var $bodyHTML;
	var $structure;
	var $parts;
	var $plain_section;
	var $html_section;
	
	function delete($expunge=false){
		if(!$this->uid){
			return false;	
		}
		if(!$mbox=$this->get_imap_stream()){
			return false;
		}
		if(!imap_delete($mbox, $this->uid,FT_UID )){
			return false;	
		}
		if($expunge){
			imap_expunge($mbox);	
		}
		return true;
		
	}
	function get_attachments_data(){
		if(!$items=$this->get_attachments()){
			return false;	
		}
		$r=array();
		foreach($items as $id=>$item){
			$r[$id]=$item->get_attachment_data();	
		}
		return $r;	
			
	}
	function get_attachments(){
		if(!$items=$this->get_parts()){
			return false;	
		}
		$r=array();
		foreach($items as $id=>$item){
			if($item->is_attachment()){
				$r[$id]=$item;	
			}
		}
		if(sizeof($r)){
			return $r;	
		}
	}
	function get_parts(){
		$this->init_structure();
		return $this->parts;
	}
	function get_attachment($index){
		if(!$item=$this->get_part($index)){
			return false;	
		}
		if($item->is_attachment()){
			return $item;	
		}
	}
	function get_part($index){
		if(!$index=$index+0){
			return false;	
		}
		$this->init_structure();
		if($this->parts){
			return $this->parts[$index];
		}
	}
	function get_structure(){
		$this->init_structure();
		return $this->structure;
	}
	function init_structure(){
		if(isset($this->structure)){
			return;	
		}
		$this->load_structure();
	}
	function load_structure(){
		$this->structure=false;
		$this->parts=false;
		
		if(!$this->uid){
			return false;	
		}
		if(!$mbox=$this->get_imap_stream()){
			return false;
		}
		if(!$s=imap_fetchstructure ( $mbox , $this->uid ,FT_UID )){
			return false;
			//$this->bodyHTML=$b;	
		}
		$this->structure=$s;
		if(isset($s->parts) && count($s->parts)) {
			$this->parts=array();
			$i=0;
			foreach($s->parts as $p){
				$i++;
				$pp=new mwmod_mw_mail_imap_result_msgpart($i,$p,$this);
				$this->parts[$i]=$pp;	
			}
			
		}
	}

	
	
	
	function get_body_html(){
		$this->init_body_html();
		return $this->bodyHTML;
	}
	function init_body_html(){
		if(isset($this->bodyHTML)){
			return;	
		}
		$this->load_body_html();
	}
	function load_body_html(){
		//falta
		$this->bodyHTML="";
		if(!$this->uid){
			return false;	
		}
		if(!$mbox=$this->get_imap_stream()){
			return false;
		}
		if($b=imap_fetchbody ( $mbox , $this->uid ,1.2 ,FT_UID )){
			$this->bodyHTML=$b;	
		}
	}
	
	function get_body(){
		$this->init_body();
		return $this->body;
	}
	function init_body(){
		if(isset($this->body)){
			return;	
		}
		$this->load_body();
	}
	function get_plain_section(){
		if(isset($this->plain_section)){
			return $this->plain_section;
		}
		$this->plain_section=false;
		if(!$s=$this->get_structure()){
			return false;	
		}
		if(!$s->parts){
			return false;	
		}
		if (!sizeof($s->parts)) {
			return false;	
		}
		reset($s->parts);
		foreach($s->parts as $i=>$part){
			if($part->type==1){
				$this->plain_section=$i+1;
				if(sizeof($part->parts)){
					$this->plain_section.=".1";	
				}
				break;
			}
			if($part->type==0){
				$this->plain_section=$i+1;
				if(sizeof($part->parts)){
					$this->plain_section.=".1";	
				}
				break;
			}
		}
		
		
		return $this->plain_section;
	}
	function load_body(){
		$this->body="";
		if(!$this->uid){
			return false;	
		}
		if(!$mbox=$this->get_imap_stream()){
			return false;
		}
		if(!$seccion=$this->get_plain_section()){
			if($b=imap_body ( $mbox , $this->uid , FT_UID )){
				$this->body=strip_tags($b);	
			}
			return;
				
		}
		
		if($b=imap_fetchbody ( $mbox , $this->uid ,$seccion ,FT_UID )){
			$this->body=$b;
			return;
		}
	}
	function set_overview($obj){
		if(!$obj){
			return false;
		}
		if(!is_object($obj)){
			return false;
		}
		$this->overview=$obj;
		$this->uid=$obj->uid;
		$this->date=date("Y-m-d H:i:s",strtotime($obj->date));
		$this->subject=iconv_mime_decode($obj->subject,0,'UTF-8');
		
		$this->from=imap_utf8($obj->from);
		$this->to=imap_utf8($obj->to);
		$this->message_id=imap_utf8($obj->message_id);
		

	}
	final function set_resultset($resultset){
		$this->resultset=$resultset;
		$this->set_conn($resultset->conn);
	}
	final function set_conn($conn){
		$this->conn=$conn;
	}
	function get_message_info(){
		$r=array();
		$r["uid"]=$this->uid;
		$r["date"]=$this->date;
		$r["subject"]=$this->subject;
		$r["from"]=$this->from;
		$r["to"]=$this->to;
		$r["message_id"]=$this->message_id;
		//$r["overview"]=$item->obj2array($item->overview);
		return $r;

	
	}
	
	function get_debug_info(){
		$r=array();
		$r["uid"]=$this->uid;
		$r["date"]=$this->date;
		$r["overview"]=$this->obj2array($this->overview);
		if($this->body){
			$r["body"]=$this->body;	
		}
		if($this->bodyHTML){
			$r["bodyHTML"]=$this->bodyHTML;	
		}
		if($this->structure){
			$s=$this->obj2array($this->structure);
			unset($s["parts"]);
			$r["structure"]=$s;
		}
		if($this->parts){
			reset($this->parts);
			$r["parts"]=array();
			foreach($this->parts as $i=>$p){
				$r["parts"][$i]=$p->get_debug_info();	
			}
			//
		}
		return $r;
			
	}
	function obj2array($obj){
		return $this->resultset->obj2array($obj);
	}
	final function __get_priv_resultset(){
		return $this->resultset; 	
	}
	final function __get_priv_conn(){
		return $this->conn; 	
	}
	function get_imap_stream(){
		return $this->conn->get_imap_stream();	
	}

	
}
?>