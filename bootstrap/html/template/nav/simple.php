<?php
class mwmod_mw_bootstrap_html_template_nav_simple extends mwmod_mw_bootstrap_html_template_nav{
	function __construct($display_mode="default"){
		$main=new mwmod_mw_bootstrap_html_specialelem_nav($display_mode);
		$this->create_cont($main);
		
	}
	
	function create_cont($main){
		$this->set_main_elem($main);
		$this->navOutterContainer=new mwmod_mw_bootstrap_html_def("container-fluid");
		$main->add_cont($this->navOutterContainer);
		/*
		$this->navHeader=new mwmod_mw_bootstrap_html_def("navbar-header");
		$this->navOutterContainer->add_cont($this->navHeader);
		if($this->toggleBtn=$this->createToggleBtn()){
			$this->navHeader->add_cont($this->toggleBtn);
		}
		*/
		$this->navContainer=new mwmod_mw_html_cont_varcont();
		$this->navOutterContainer->add_cont($this->navContainer);
		$this->navLeft=new mwmod_mw_bootstrap_html_specialelem_nav_ul();
		$this->navs["left"]=$this->navLeft;
		$this->navContainer->add_cont($this->navLeft);
		$this->navRight=new mwmod_mw_bootstrap_html_specialelem_nav_ul();
		$this->navRight->add_class("navbar-right");
		$this->navContainer->add_cont($this->navRight);
		$this->navs["right"]=$this->navRight;
		
	}
	

}

?>