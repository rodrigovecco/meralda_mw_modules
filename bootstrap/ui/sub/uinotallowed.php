<?php
class mwmod_mw_bootstrap_ui_sub_uinotallowed extends mwmod_mw_bootstrap_ui_sub_abs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		if($msg_man=$this->mainap->get_msgs_man_common()){
			$this->set_def_title($msg_man->get_msg_txt("not_allowed","No permitido"));
		}

		
		
	}
	function omit_header(){
		return true;	
	}
	
	function do_exec_no_sub_interface(){
		//echo "ttttt";	
	}
	/*
	function do_exec_page_in(){
		//echo "<p>".$this->get_msg("No permitido.")."</p>";
	}
	*/

	function is_allowed(){
		return true;
	}
	function do_exec_page_in(){
		/*
		if($authentication_ui=$this->maininterface->get_main_authentication_ui()){
			return $authentication_ui->exec_page_in_redirect_login();
		}
		*/
		echo "<br><br>";
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		$cont=$msg_man->get_msg_txt("not_allowed","No permitido");
		$alert=new mwmod_mw_bootstrap_html_specialelem_alert($cont,"danger");
		echo $alert->get_as_html();
		//mwmod_mw_bootstrap_html_specialelem_alert
		
		
		return;
	}
	
}
?>