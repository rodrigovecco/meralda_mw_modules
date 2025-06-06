<?php
class mwmod_mw_users_ui_newuser extends mwmod_mw_ui_sub_withfrm{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_common_get_msg_txt("new_user","Nuevo usuario"));
		
	}
	function do_exec_no_sub_interface(){
	}
	function getUman(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->getUman();
		}
		
	}

	function do_exec_page_in(){
		if(!$uman=$this->getUman()){
			return false;
			
		}
		$MainContainer=$this->get_ui_dom_elem_container();
		$container=$MainContainer;
		if($this->mainPanelEnabled){
			if($mainpanel=$this->createMainPanel()){
				$MainContainer->add_cont($mainpanel);
				$container=$mainpanel->panel_body->add_cont_elem();
			}
		}

		$dm=$uman->get_user_data_man();
		$dm->create_new_user_from_admin_ui(new mwmod_mw_helper_inputvalidator_request("nduser.usernd"),$this);
		
		$frm=$this->new_frm();
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$cr->items_pref="nduser";
		//mw_array2list_echo($_REQUEST);
		$dm->set_new_user_cr($cr);
		
		$cr->add_submit($this->lng_common_get_msg_txt("create","Crear"));
		$frm->set_datafieldcreator($cr);
		$frm->disable_on_submit=true;
		if($this->bottom_alert_msg){
			$container->add_cont($this->bottom_alert_msg);
		}
		
		$container->add_cont($frm->get_html());

		echo $MainContainer->get_as_html();

		
	}
	function is_allowed(){
		return $this->allow("adminusers");	
	}
	
}
?>