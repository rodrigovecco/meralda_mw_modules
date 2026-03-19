<?php
class mwmod_mw_bruteforce_ui_main extends mwmod_mw_ui_base_basesubuia{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		
		$this->set_def_title($this->lng_get_msg_txt("bruteForce","Fuerza bruta"));
		$this->sucods="activity,whitelist,blacklist,cfg";

		
	}
	function do_exec_page_in(){
		$man=$this->mainap->get_submanager("bruteforce");



		$MainContainer=$this->get_ui_dom_elem_container();
		$container=$MainContainer;
		if($this->mainPanelEnabled){
			
			if($mainpanel=$this->createMainPanel()){
				$MainContainer->add_cont($mainpanel);
				$container=$mainpanel->panel_body->add_cont_elem();
			}
		
		}
		
		$sbucontainer=$container->add_cont_elem();
		$sbucontainer1=$sbucontainer->add_cont_elem();
		$e=$sbucontainer1->add_cont_elem();
		$e->addClass("card mb-2");
		$ee=$e->add_cont_elem();
		$ee->addClass("card-body");
		$ee->addCont($this->lng_get_msg_txt("yourIP","Tu IP").": ".$man->getCurrentIP());
		//echo "<div class='card'><div class='card-body'>";
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			$listcontainer=$sbucontainer1->add_cont_elem();
			$listcontainer->addClass("list-group");
			//echo "<div class='list-group'>";
			foreach($subs as $su){
				$listcontainer->add_cont("<a href='".$su->get_url()."' class='list-group-item list-group-item-action'>".$su->get_mnu_lbl()."</a>");	
			}
			//echo "</div>";
			
		}
		echo $MainContainer->get_as_html();


		/*
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
		*/
		
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