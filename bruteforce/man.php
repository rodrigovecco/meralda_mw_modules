<?php
//
class mwmod_mw_bruteforce_man extends mwmod_mw_bruteforce_manbase{

	function isEnabled(){
		return true;
	}
	
	function getFailAttemptsNumOnLock(){
		return 4;
	}
	function getNextLockUntillDatetime(){
		$time=strtotime(date("Y-m-d H:i:s")." + 5 minutes");
		return date("Y-m-d H:i:s",$time);
	}
	function registerCurrentIPfailedLogin($username=null){
		if($item=$this->getCurrentIPActivityItem()){
			$nd=array();
			if($username){
				$nd["last_username_attempted"]=$username;
			}
			$nd["last_attempt"]=date("Y-m-d H:i:s");
			if($item->get_data("failed_attempts")>=$this->getFailAttemptsNumOnLock()){
				$nd["lock_until"]=$this->getNextLockUntillDatetime();
			}
			$nd["failed_attempts"]=$item->get_data("failed_attempts")+1;
			$item->do_save_data($nd);
			return $item;
		}
		$nd=array();
		if($username){
			$nd["last_username_attempted"]=$username;
		}
		$nd["last_attempt"]=date("Y-m-d H:i:s");
		$nd["ip_address"]=$this->getCurrentIP();
		$nd["lock_until"]=null;
		$nd["failed_attempts"]=1;
		if($item=$this->activity->create_new_item($nd)){
			return $item;
		}


	}
	function registerCurrentIPsuccessfullLogin($username=null){
		if($item=$this->getCurrentIPActivityItem()){
			$nd=array();
			if($username){
				$nd["last_username_attempted"]=$username;
			}
			$nd["last_attempt"]=date("Y-m-d H:i:s");
			$nd["lock_until"]=null;
			$nd["failed_attempts"]=0;
			$nd["historical_failed_attempts"]=$item->get_data("historical_failed_attempts")+$item->get_data("failed_attempts");
			$nd["historical_successful_attempts"]=$item->get_data("historical_successful_attempts")+1;
			$item->do_save_data($nd);
			return $item;
		}
		$nd=array();
		if($username){
			$nd["last_username_attempted"]=$username;
		}
		$nd["last_attempt"]=date("Y-m-d H:i:s");
		$nd["ip_address"]=$this->getCurrentIP();
		$nd["lock_until"]=null;
		$nd["historical_successful_attempts"]=1;
		if($item=$this->activity->create_new_item($nd)){
			return $item;
		}


	}
	function getCurrentIP(){
		return $_SERVER['REMOTE_ADDR'];
	}
	function getCurrentIPWhitelistItem(){
		return $this->whitelist->getItemByIP($this->getCurrentIP());	
	}
	function getCurrentIPBlacklistItem(){
		return $this->blacklist->getItemByIP($this->getCurrentIP());	
	}
	function getCurrentIPActivityItem(){
		return $this->activity->getItemByIP($this->getCurrentIP());	
	}

}
?>