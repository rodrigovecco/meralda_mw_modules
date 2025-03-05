<?php
class mwmod_mw_users_ui_myaccount_pass extends mwmod_mw_users_ui_myaccount_abs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("change_password","Cambiar contraseña"));
		
	}

	function do_exec_page_in(){
		if(!$user=$this->get_admin_current_user()){
			return false;
		}
		$frm=$this->new_frm();
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$cr->items_pref="nduser";
		$dm=$user->get_user_data_man();
		$msg="";
		$msgs=new mwmod_mw_html_elem();
		$msgs->only_visible_when_has_cont=true;

		$dm->savefromfrm_user_changepass(new mwmod_mw_helper_inputvalidator_request("nduser.usernd"),$user,$msgs);
		//mw_array2list_echo($_REQUEST);
		$dm->set_user_changepass_cr($user,$cr);
		$cr->add_cancel("index.php");
		$cr->add_submit($this->lng_get_msg_txt("change_password","Cambiar contraseña"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html();
		
		if($msgs){
			echo $msgs->get_as_html();
		}

		
		return;
		

		
	}
	
}
?>