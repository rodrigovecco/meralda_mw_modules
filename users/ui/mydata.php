<?php
//20240304
class mwmod_mw_users_ui_mydata extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_def_title($this->lng_get_msg_txt("myAccount","Mi cuenta"));
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		if(!$user=$this->get_admin_current_user()){
			return false;
		}
		if(is_array($_REQUEST["nd"])){
			$user->save_user_data($_REQUEST["nd"]["data"]);
			//save_user_data
		}
		
		$frm=$this->new_frm();
		//$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		
		
		
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("name",$this->lng_get_msg_txt("user","Usuario")));
		$input->set_value($user->get_idname());	
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("complete_name",$this->lng_get_msg_txt("fullName","Nombre completo")),"data");
		$input->set_value($user->get_real_name());	
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("email",$this->lng_get_msg_txt("email","Email")),"data");
		$input->set_value($user->get_email());
		
		
		
		
		$cr->items_pref="nd";
		$cr->add_submit($this->get_msg("Guardar"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html();

		
	}
	function is_allowed(){
		return $this->allow("editmydata");	
	}
	
}
?>