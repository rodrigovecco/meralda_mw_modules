<?php
class mwmod_mw_ui_authentication_uilogin extends mwmod_mw_bootstrap_ui_sub_abs{
	//no lista
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_def_title($this->get_msg("Iniciar sesión"));
		
	}
	function is_single_mode(){
		return true;	
	}
	
	function do_exec_page_in(){
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		$container=new mwmod_mw_bootstrap_html_grid_container();
		$row=new mwmod_mw_bootstrap_html_grid_row();
		$container->add_cont($row);
		$col= new mwmod_mw_bootstrap_html_grid_col(4);
		$row->add_cont($col);
		$col->set_offset(4);
		$panel=new mwmod_mw_bootstrap_html_def("login-panel panel panel-default");
		$col->add_cont($panel);
		
		
		$panel_head=new mwmod_mw_bootstrap_html_def("panel-heading");
		$panel->add_cont($panel_head);
		
		$panel_title=new mwmod_mw_bootstrap_html_def("panel-title","h3");
		$panel_title->add_cont($msg_man->get_msg_txt("please_login","Por favor, iniciar sesión"));
		$panel_head->add_cont($panel_title);
		
		$panel_body=new mwmod_mw_bootstrap_html_def("panel-body");
		
		$panel->add_cont($panel_body);
		
		
		
		$panel_body->add_cont($this->get_login_frm_html());
		
		
		
		
		
		echo $container->get_as_html();
		return;
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
		if($_REQUEST["onloginok"]){
			$i=$cr->add_item(new mwmod_mw_datafield_hidden("onloginok"));
			$i->set_value($_REQUEST["onloginok"]);
			//$i->
		}
		$frm->set_datafieldcreator($cr);
		return $frm->get_html();
	
	}
	
	function is_allowed(){
		return true;
	}
	
}
?>