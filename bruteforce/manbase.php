<?php
//
abstract class mwmod_mw_bruteforce_manbase extends mwmod_mw_manager_basemanabs{
	private $whitelist;
	private $blacklist;
	private $activity;
	function __construct($app){
		$this->setManCode("bruteforce");
		$this->set_mainap($app);	
		$this->enable_jsondata();
	}
	final function __get_priv_whitelist(){
		if(!isset($this->whitelist)){
			$this->whitelist=$this->loadWhitelist();
		}
		return $this->whitelist;
	}
	function loadWhitelist(){
		return new mwmod_mw_bruteforce_whitelist_man($this);
	}
	final function __get_priv_blacklist(){
		if(!isset($this->blacklist)){
			$this->blacklist=$this->loadBlacklist();
		}
		return $this->blacklist;
	}
	function loadBlacklist(){
		return new mwmod_mw_bruteforce_blacklist_man($this);
	}
	final function __get_priv_activity(){
		if(!isset($this->activity)){
			$this->activity=$this->loadActivity();
		}
		return $this->activity;
	}
	function loadActivity(){
		return new mwmod_mw_bruteforce_activity_man($this);
	}
	
	

}
?>