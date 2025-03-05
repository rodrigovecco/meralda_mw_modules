<?php
class mwmod_mw_ui_sub_uilogin extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_def_title($this->get_msg("Iniciar sesión"));
		
	}
	function do_exec_no_sub_interface(){
		
	}
	
	function do_exec_page_in(){
		$frm=$this->new_frm();
		$frm->action="index.php";
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$cr->add_item(new mwmod_mw_datafield_input("login_userid",$this->get_msg("Usuario")));
		$cr->add_item(new mwmod_mw_datafield_password("login_pass",$this->get_msg("Contraseña")));
		$cr->add_submit($this->get_msg("Enviar"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html();
	}
	function is_single_mode(){
		return true;
	}

	function is_allowed(){
		return true;
	}
	
}
?>