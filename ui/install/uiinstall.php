<?php
class mwmod_mw_ui_install_uiinstall extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_def_title($this->get_msg("Instalación"));
		
	}
	function do_exec_no_sub_interface(){
		
	}
	
	function do_exec_page_in_credentials_login(){
		$frm=$this->new_frm();
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$cr->add_item(new mwmod_mw_datafield_password("login_token",$this->get_msg("Clave")));
		$cr->add_submit($this->get_msg("Enviar"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html();
		
	}
	function do_exec_page_in_credentials_ok(){
		mw_array2list_echo($this->mainap->get_debug_info());
		//phpinfo();
	}
	function do_exec_page_in(){
		if(!$this->maininterface->install_ui_enabled()){
			echo "<p>".$this->get_msg("No permitido")."</p>";
		}elseif($this->maininterface->install_credentials_ok()){
			$this->do_exec_page_in_credentials_ok();	
		}else{
			$this->do_exec_page_in_credentials_login();		
		}
		echo "<p>IP: ".$_SERVER['REMOTE_ADDR']."</p>";

	}
	function is_allowed(){
		return true;
	}
	
}
?>