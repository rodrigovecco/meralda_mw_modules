<?php
class mwmod_mw_users_ui_user_pass extends mwmod_mw_users_ui_user_abs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("change_password","Cambiar contraseña"));
		
	}

	function do_exec_page_in(){
		if(!$user=$this->get_current_item()){
			return false;
		}
		if(!$user->allowadmin_admin()){
			return false;	
		}
		$frm=$this->new_frm();
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$dm=$user->get_user_data_man();
		$msg="";
		$dm->savefromfrm_user_changepassfromadmin(new mwmod_mw_helper_inputvalidator_request("nduser.usernd"),$user,$msg);
		//mw_array2list_echo($_REQUEST);
		$dm->set_user_changepassfromadmin_cr($user,$cr);
		$cr->items_pref="nduser";
		$cr->add_submit($this->lng_get_msg_txt("change_password","Cambiar contraseña"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html();
		if($msg){
			$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg);
			echo $alert->get_as_html();
		}

		
		return;
		

		
	}
	
}
?>