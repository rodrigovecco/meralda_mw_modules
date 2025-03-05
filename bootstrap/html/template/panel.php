<?php
class mwmod_mw_bootstrap_html_template_panel extends mwmod_mw_bootstrap_html_template_abs{
	var $panel_heading;
	var $panel_body;
	var $panel_footer;

	
	function __construct($display_mode="default"){
		
		$main=new mwmod_mw_bootstrap_html_specialelem_panel($display_mode);
		$this->create_cont($main);
		
	}
	function noPanelMode(){
		if($this->panel_heading){
			$this->panel_heading->remove_class("card-header");	
		}
		if($this->panel_body){
			$this->panel_body->remove_class("card-body");	
		}
		if($this->panel_footer){
			$this->panel_footer->remove_class("card-footer");	
		}
		if($this->main_elem){
			if(is_a($this->main_elem,"mwmod_mw_bootstrap_html_specialelem_elemabs")){
				$this->main_elem->set_main_class_name("");		
				$this->main_elem->set_display_mode("");		
			}
			
		}
		
	}
		
	function create_cont($main){
		$this->set_main_elem($main);
		$head=new mwmod_mw_bootstrap_html_def("card-header");
		$this->set_title_elem($head);
		$this->panel_heading=$head;
		$main->add_cont($head);
		$body=new mwmod_mw_bootstrap_html_def("card-body");
		$this->set_cont_elem($body);
		$main->add_cont($body);
		$this->panel_body=$body;
		$footer=new mwmod_mw_bootstrap_html_def("card-footer");
		$this->set_key_cont("footer",$footer);
		$footer->only_visible_when_has_cont=true;
		$main->add_cont($footer);
		$this->panel_footer=$footer;
		
		
		//extender
	}
	

}

?>