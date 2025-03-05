<?php
abstract class mwmod_mw_mail_queue_itemabs extends mwmod_mw_manager_item{
	var $sending_debug_data=array();
	function init_queue_item($tblitem,$man){
		$this->init($tblitem,$man);	
		$this->enable_strdata();
		$this->enable_treedata();

	}
	function send(){
		$this->sending_debug_data=array();
		$this->sending_debug_data["id"]=$this->get_id();
		$this->sending_debug_data["sent"]=false;
		if(!$phpmailer=$this->new_phpmailer()){
			return false;	
		}
		if(!$temp_msg=$this->create_new_temp_msg()){
			return false;	
		}
		if(!$temp_msg->prepare_mailer($phpmailer)){
			return false;	
		}
		$this->sending_debug_data["infostr"]=$temp_msg->get_short_debug_info_str();
		$nd=array();
		$nd["status"]=3;
		$nd["date_sent"]=$this->tblitem->format_time();;
		if(!$this->man->dontMarkMsgAsSent){
			$this->tblitem->update($nd);
		}
		if($this->man->debug_mode){
			$phpmailer->SMTPDebug=4;	
		}
		//$phpmailer->SMTPDebug=1;	
		if($phpmailer->send()){
			$this->sending_debug_data["sent"]=true;
			$nd=array();
			$nd["status"]=5;
			if(!$this->man->dontMarkMsgAsSent){
				$this->tblitem->update($nd);
			}
			return true;	
		}else{
			
			$this->setErrorInfo($phpmailer->ErrorInfo);
			$this->on_send_fail();
			return false;	
		}
		

		
		
		
	}
	function prepare_temp_msg($temp_msg){
		if(!$td=$this->get_treedata_item("msgdata")){
			return false;
		}
		if($from=$td->get_data("data.From")){
			$temp_msg->setFrom($from,$td->get_data("data.FromName"));	
		}
		$temp_msg->Subject=$td->get_data("data.Subject");
		if($list=$td->get_data_as_list("adds.to",false)){
			foreach($list as $d){
				$temp_msg->addAddress($d["address"],$d["name"]);
			}
		}
		if($list=$td->get_data_as_list("adds.cc",false)){
			foreach($list as $d){
				$temp_msg->addCC($d["address"],$d["name"]);
			}
		}
		if($list=$td->get_data_as_list("adds.bcc",false)){
			foreach($list as $d){
				$temp_msg->addBCC($d["address"],$d["name"]);
			}
		}
		if($list=$td->get_data_as_list("adds.ReplyTo",false)){
			foreach($list as $d){
				$temp_msg->addReplyTo($d["address"],$d["name"]);
			}
		}
		$htmlBasedir=$td->get_data("htmlBasedir");
		if(!is_string($htmlBasedir)){
			$htmlBasedir="";	
		}
		if($td->get_data("isHtml")){
			if(!$strData=$this->get_strdata_item("html")){
				return false;	
			}
			$temp_msg->msgHTML($strData->get_data(),$htmlBasedir);
		}
		if($strDataplain=$this->get_strdata_item("plain")){
			
			$temp_msg->AltBody=$strDataplain->get_data();
		}
		$this->prepare_temp_msg_atts($temp_msg);
		return $temp_msg;
		
		
		
		
			
	}
	function prepare_temp_msg_atts($temp_msg){
		if(!$td=$this->get_treedata_item("msgdata")){
			return false;
		}
		if(!$list=$td->get_data_as_list("atts",false)){
			return false;
		}
		$num=0;
		foreach($list as $d){
			if(is_array($d)){
				if($id=$d["id"]+0){
					if($basename=basename($d["basename"])){
						if($file_path=$this->get_path("att/$id",false)){
							if($temp_msg->addAttachment(
								$file_path."/".$basename,
								$d["name"],
								$d["encoding"],
								$d["type"],
								$d["disposition"]
							)){
								$num++;	
							}
						}
					}
					
						
				}
			}
		}
		return $num;
		

	}
	function create_new_temp_msg(){
		if(!$temp_msg=$this->new_temp_msg()){
			return false;	
		}
		if($this->prepare_temp_msg($temp_msg)){
			return $temp_msg;	
		}
	}
	function on_send_fail(){
		$nd=array();
		$fails=$this->get_data("fail_num")+1;
		if($fails>=$this->get_max_fails()){
			$nd["status"]=4;
		}else{
			$nd["status"]=2;	
		}
		
		$nd["fail_num"]=$fails;
		return $this->tblitem->update($nd);
	}
	function new_phpmailer(){
		return $this->man->new_phpmailer();	
			
	}
	function new_temp_msg(){
		return $this->man->new_temp_msg();	
			
	}
	
	function get_max_fails(){
		return $this->man->get_max_fails();	
	}
	
	function set_ready_to_send(){
		$nd=array("status"=>2);
		return $this->tblitem->update($nd);
		
			
	}
	function setErrorInfo($msg){
		if(!$msg){
			return false;	
		}
		if(!$strData=$this->get_strdata_item("error")){
			return false;	
		}
		$strData->set_data($msg);
		$strData->save();
		return true;
			
	}
	function set_data_from_temp_msg($temp_msg){
		if(!$td=$this->get_treedata_item("msgdata")){
			return false;
		}
		$isHtml=0;
		if($temp_msg->isHtml){
			$isHtml=1;	
		}
		$td->set_data($temp_msg->From,"data.From");
		$td->set_data($temp_msg->FromName,"data.FromName");
		$td->set_data($temp_msg->Subject,"data.Subject");
		$td->set_data($temp_msg->htmlBasedir,"htmlBasedir");
		$td->set_data($isHtml,"isHtml");
		if($list=$temp_msg->getToAddresses()){
			$td->set_data($list,"adds.to");	
		}
		if($list=$temp_msg->getCcAddresses()){
			$td->set_data($list,"adds.cc");	
		}
		if($list=$temp_msg->getBccAddresses()){
			$td->set_data($list,"adds.bcc");	
		}
		if($list=$temp_msg->getReplyToAddresses()){
			$td->set_data($list,"adds.ReplyTo");	
		}
		$attnum=0;
		if(!$file_man=$this->get_filemanager()){
			return false;	
		}
		
		if($atts=$temp_msg->getAttachments()){
			foreach($atts as $attdata){
				if($file=$attdata["path"]){
					if (@is_file($file)) {
						if($basename=basename($file)){
							$attnum++;
							if($file_path=$this->get_path("att/$attnum",false)){
								if($file_man->check_and_create_path($file_path)){
									if(copy($file,$file_path."/".$basename)){
										$attdata["id"]=$attnum;
										$attdata["basename"]=$basename;
										$td->set_data($attdata,"atts.$attnum");
											
									}
								}
							}
						}
					}
						
				}
			}
		}
		$td->save();
		//mw_array2list_echo($td->get_data());
		/*
		*/
		
		if($isHtml){
			if(!$strData=$this->get_strdata_item("html")){
				return false;	
			}
			$strData->set_data($temp_msg->get_html_body());
			$strData->save();
		}
		if($temp_msg->AltBody){
			if(!$strPlain=$this->get_strdata_item("plain")){
				return false;	
			}
			$strPlain->set_data($temp_msg->AltBody);
			$strPlain->save();
		}
		
		return true;
		
		
	}
	
	
}

?>