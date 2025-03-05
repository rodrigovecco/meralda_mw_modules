<?php

class mwmod_mw_mnu_items_special_bt_user extends mwmod_mw_mnu_items_cus{
	var $ui;
	var $user;
	function __construct($cod,$parent,$user,$ui){
		$this->init($cod,"",$parent,false);
		$this->user=$user;
		$this->ui=$ui;
		$this->create_html_elem();
	}
	function create_html_elem(){
		$urlmyacc=false;
		if($myacc=$this->ui->get_subinterface("myaccount")){
			$urlmyacc=$myacc->get_url();	
		}
		//$this->ui->
		$this->html_elem=new mwmod_mw_bootstrap_html_def("mw-cus-mnu-item clearfix","div");
		$imgcont=new mwmod_mw_bootstrap_html_def("pull-left","span");
		if($imgelem=$this->user->get_img_elem("profiletiny")){
			$imgelem->set_att("class","img-circle mw-user-img");
			
			$imgcont->add_cont($imgelem);
			if($urlmyacc){
				$imgelem->set_style("cursor","pointer");
				$imgelem->set_att("onclick","window.location='".$urlmyacc."'");	
			}
		}
		$this->html_elem->add_cont($imgcont);
		$c=new mwmod_mw_bootstrap_html_def("clearfix","div");
		$h=new mwmod_mw_bootstrap_html_def("header","div"); 
		$h->add_cont($this->user->get_real_name());
		if($urlmyacc){
			$h->set_att("onclick","window.location='".$urlmyacc."'");
			$h->set_style("cursor","pointer");
		}

		$c->add_cont($h);
		$this->html_elem->add_cont($c);
		$logout=new mwmod_mw_bootstrap_html_def("","div");
		$logoutlink=new mwmod_mw_bootstrap_html_def("small","a");
		
		$logoutlink->set_att("href",$this->ui->get_logout_url());
		$logoutlink->add_cont($this->lng_get_msg_txt("logout","Cerrar sesión"));
		$logout->add_cont($logoutlink);
		$c->add_cont($logout);
		
		
		
		//$this->html_elem->set_cont($this->get_alink());	
	}

	
}

?>