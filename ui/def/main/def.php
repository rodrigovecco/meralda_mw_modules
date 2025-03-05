<?php
abstract class mwmod_mw_ui_def_main_def extends mwmod_mw_uitemplates_sbadmin_main{
	function create_subinterface_welcome(){
		$si= new mwmod_mw_ui_def_welcome("welcome",$this);
		return $si;
	}
	function create_subinterface_system(){
		$si= new mwmod_mw_ui_system_main("system",$this);
		return $si;
	}
	function create_subinterface_uidebug(){
		$si= new mwmod_mw_ui_debug_uidebug("uidebug",$this);
		return $si;
	}
	function create_subinterface_users(){
		$si= new mwmod_mw_users_ui_users("users",$this);
		return $si;
	}
	function create_subinterface_myaccount(){
		$si= new mwmod_mw_users_ui_myaccount("myaccount",$this);
		return $si;
	}
	/*
	function create_subinterface_login(){
		$si= new mwmod_mw_bootstrap_ui_sub_uiloginadv("login",$this);
		return $si;
	}
	*/
	function add_mnu_items_toplinks($mnu){
		if($user=$this->get_admin_current_user()){
			$mnuitem= new mwmod_mw_mnu_items_dropdown_top("user",$this->get_msg("Usuario"),$mnu);
			$mnuitem->add_param("icon_class","fa fa-user fa-fw");
			$mnu->add_item_by_item($mnuitem);
			if($myacc=$this->get_subinterface("myaccount")){
				$item=new mwmod_mw_mnu_mnuitem("myaccount",$this->lng_common_get_msg_txt("my_account","Mi cuenta"),$mnuitem,$myacc->get_url());
				$item->addInnerHTML_icon("fa fa-user fa-fw");
				$mnuitem->add_item_by_item($item);
			}
			
			
			$sub=$mnuitem->add_new_item("logout",$this->get_msg("Cerrar sesión"),$this->get_logout_url());	
			$sub->addInnerHTML_icon("fa fa-sign-out fa-fw");
		}
		
	}
	function new_side_user_mnu_item($cod,$mnu,$user){
		 $item=new mwmod_mw_mnu_items_special_bt_user($cod,$mnu,$user,$this);
		 return $item;
	}

	function add_mnu_items_side($mnu){
		/*
		if($user=$this->get_admin_current_user()){
			if($item=$this->new_side_user_mnu_item("userdisplay",$mnu,$user)){
				$mnu->add_item_by_item($item);	
			}
		}
		*/
		if($subinterfaces=$this->get_subinterfaces_by_code($this->su_cods_for_side,false)){
			foreach($subinterfaces as $cod=>$su){
				$su->add_2_side_mnu($mnu,true);	
			}
		}
		$mnuitem=new mwmod_mw_mnu_items_html("__divider","",$mnu);
		$mnuitem->html_elem->add_cont('<hr class="sidebar-divider d-none d-md-block">');
		$mnu->add_item_by_item($mnuitem);
		$sub=$mnu->add_new_item("logout",$this->get_msg("Cerrar sesión"),$this->get_logout_url());
		$sub->addInnerHTML_icon("fas fa-sign-out-alt");
	}
	
	function create_subinterface_rememberlogindata(){
		$si=new mwmod_mw_subui_rememberlogindata("rememberlogindata",$this);
		return $si;
	}
	

	function get_subinterface_not_allowed_no_user(){
		return $this->get_subinterface("login");
	}

	function admin_user_ok(){
		if($user=$this->get_admin_current_user()){
			//return true;
			return $user->allow("admininterfase");	
		}
	}
}
?>