<?php
class mwmod_mw_bootstrap_ui_sub_uilogin extends mwmod_mw_bootstrap_ui_sub_abs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		if($msg_man=$this->mainap->get_msgs_man_common()){
			$this->set_def_title($msg_man->get_msg_txt("login","Iniciar sesión"));
		}

		
		
	}
	function is_single_mode(){
		return true;	
	}
	
	function prepare_before_exec_no_sub_interface(){
		$p=new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
	}
	function do_exec_page_in(){
		
		/*
		if($authentication_ui=$this->maininterface->get_main_authentication_ui()){
			return $authentication_ui->exec_page_in_redirect_login();
		}
		*/
		
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		$container=new mwmod_mw_bootstrap_html_grid_container();
		$row=new mwmod_mw_bootstrap_html_grid_row();
		$container->add_cont($row);
		$col= new mwmod_mw_bootstrap_html_grid_col(4);
		$row->add_cont($col);
		$col->set_offset(4);
		
		
		
		$panel=new mwmod_mw_bootstrap_html_def("login-panel card card-default");
		$col->add_cont($panel);
		
		
		$panel_head=new mwmod_mw_bootstrap_html_def("card-header");
		$panel->add_cont($panel_head);
		
		$panel_title=new mwmod_mw_bootstrap_html_def("card-title","h3");
		$panel_title->add_cont($msg_man->get_msg_txt("please_login","Por favor, iniciar sesión"));
		$panel_head->add_cont($panel_title);
		
		$panel_body=new mwmod_mw_bootstrap_html_def("card-body");
		
		$panel->add_cont($panel_body);
		
		
		
		$panel_body->add_cont($this->get_login_frm_html());
		
		if($msg=$this->get_login_fail_msg()){
			$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"danger");
			$panel_body->add_cont($alert);

		}
		if($uiremember=$this->maininterface->get_subinterface("rememberlogindata")){
			if($uiremember->is_rememberlogindata()){
				if($uiremember->is_enabled()){
					if($html=$uiremember->get_html_link_on_login()){
						//$alert=new mwmod_mw_bootstrap_html_specialelem_alert($html,"info");
						$alert=new mwmod_mw_bootstrap_html_elem("div",false,$html);
						$panel_body->add_cont($alert);
							
					}
				}
			}
		}
		
		
		
		echo $container->get_as_html();
		return;
	}
	function get_login_fail_msg(){
		if(!$man=$this->maininterface->get_admin_user_manager()){
			return false;	
		}
		return $man->login_fail_msg;
	}
	function exec_log_frm(){
		echo $this->get_login_frm_html();
		
	
	}
	function get_login_frm_html(){
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		
		$frm=$this->new_frm();
		$frm->action="index.php";
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$i=$cr->add_item(new mwmod_mw_datafield_input("login_userid",$msg_man->get_msg_txt("user","Usuario")));
		$i->set_placeholder_from_lbl();
		$i->set_required();

		$i=$cr->add_item(new mwmod_mw_datafield_password("login_pass",$msg_man->get_msg_txt("password","Contraseña")));
		$i->set_placeholder_from_lbl();
		$i->set_required();
		$submit=$cr->add_submit($msg_man->get_msg_txt("login","Iniciar sesión"));
		$params=$submit->get_bootstrap_params();
		$params->set_prop("btn.class","btn btn-lg btn-success btn-block");
		$frm->set_datafieldcreator($cr);
		return $frm->get_html();
	
	}
	
	function is_allowed(){
		return true;
	}
	
}
?>