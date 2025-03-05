<?php
class mwmod_mw_bruteforce_ui_main extends mwmod_mw_ui_base_basesubuia{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		
		$this->set_def_title($this->lng_get_msg_txt("bruteForce","Fuerza bruta"));
		$this->sucods="activity,whitelist,blacklist,cfg";

		
	}
	function do_exec_page_in(){
		$man=$this->mainap->get_submanager("bruteforce");

		echo "<div class='card'><div class='card-body'>";
		echo "<div>".$this->lng_get_msg_txt("yourIP","Tu IP").": ".$man->getCurrentIP()."</div>";
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			echo "<ul>";
			foreach($subs as $su){
				echo "<li><a href='".$su->get_url()."'>".$su->get_mnu_lbl()."</a></li>";	
			}
			echo "</ul>";
			
		}
		echo "</div></div>";
		
	}
	function _do_create_subinterface_child_activity($cod){
		$ui=new mwmod_mw_bruteforce_ui_activityui($cod,$this);
		return $ui;	
	}
	function _do_create_subinterface_child_whitelist($cod){
		$ui=new mwmod_mw_bruteforce_ui_whitelistui($cod,$this);
		return $ui;	
	}
	function _do_create_subinterface_child_blacklist($cod){
		$ui=new mwmod_mw_bruteforce_ui_blacklistui($cod,$this);
		return $ui;	
	}


	function is_allowed(){
		return $this->allow("admin");
	}
	
}
?>