<?php
//
class mwmod_mw_bruteforce_activity_man extends mwmod_mw_manager_man{
	private $mainMan;
	function __construct($mainMan){
		$code="bruteforce_ip_activity";
		$tblname=$code;
		$this->setMainMan($mainMan);
		$this->init($code,$mainMan->mainap,$tblname);
	}
	function create_item($tblitem){
		
		$item=new mwmod_mw_bruteforce_activity_item($tblitem,$this);
		return $item;
	}
	function get_item_name($item){
		return $item->get_data("ip_address");
	}
	function getItemByIP($ip){
		$k=array("ip_address"=>$ip);
		return $this->get_item_by_keys($k);
	}
	final function setMainMan($mainMan){
		$this->mainMan=$mainMan;
	}
	final function __get_priv_mainMan(){
		return $this->mainMan;
	}

}
?>