<?php
class mwmod_mw_ui_authentication_aumainui extends mwmod_mw_bootstrap_ui_main{
	function __construct($ap){
		$this->set_mainap($ap);	
		
		$this->url_base_path="/authentication/";
		$this->subinterface_def_code="login";
		//$this->set_lngmsgsmancod("debug");
	}
	function load_all_subinterfases(){
		$si=$this->add_new_subinterface(new mwmod_mw_ui_authentication_uilogin("login",$this));
	}
	function get_subinterface_not_allowed_no_user(){
		$si= new mwmod_mw_ui_authentication_uilogin("login",$this);
		return $si;
	}
	function is_authentication_ui(){
		return true;	
	}
	function exec_page_in_redirect_login(){
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		
		$container=new mwmod_mw_bootstrap_html_grid_container();
		$row=new mwmod_mw_bootstrap_html_grid_row();
		$container->add_cont($row);
		$col= new mwmod_mw_bootstrap_html_grid_col(4);
		$row->add_cont($col);
		$col->set_offset(4);
		
		
		
		
		$args=array("onloginok"=>$_SERVER["REQUEST_URI"]);
		$url=$this->get_url($args);
		$alert= new mwmod_mw_bootstrap_html_specialelem_alert();
		$alert->add_cont("<p>".$msg_man->get_msg_txt("redirecting","Redireccionando")."</p>");
		$alert->add_cont("<p><a href='$url'>".$msg_man->get_msg_txt("continue","Continuar")."</a></p>");
		$col->add_cont($alert);		
		echo $container->get_as_html();
		//echo $url;
			
	}
	

	
	function is_allowed(){
		return true;	
	}
	
	
}
?>