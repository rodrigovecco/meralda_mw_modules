<?php
/**
 * Bootstrap 3 panel template (non-collapsible).
 *
 * Versioned copy of the legacy `mwmod_mw_bootstrap_html_template_panel`.
 * The current parent class generates Bootstrap 4/5 markup (`card`/`card-header`/...);
 * this versioned copy emits the Bootstrap 3 markup (`panel`/`panel-heading`/...).
 *
 * @property-read mwmod_mw_bootstrap_bootstrap3_html_specialelem_panel $main_elem
 * @property mwmod_mw_bootstrap_html_def $panel_heading
 * @property mwmod_mw_bootstrap_html_def $panel_body
 * @property mwmod_mw_bootstrap_html_def $panel_footer
 */
class mwmod_mw_bootstrap_bootstrap3_html_template_panel extends mwmod_mw_bootstrap_html_template_abs{
	var $panel_heading;
	var $panel_body;
	var $panel_footer;

	function __construct($display_mode="default"){
		$main=new mwmod_mw_bootstrap_bootstrap3_html_specialelem_panel($display_mode);
		$this->create_cont($main);
	}
	function noPanelMode(){
		if($this->panel_heading){
			$this->panel_heading->remove_class("panel-heading");
		}
		if($this->panel_body){
			$this->panel_body->remove_class("panel-body");
		}
		if($this->panel_footer){
			$this->panel_footer->remove_class("panel-footer");
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
		$head=new mwmod_mw_bootstrap_html_def("panel-heading");
		$this->set_title_elem($head);
		$this->panel_heading=$head;
		$main->add_cont($head);
		$body=new mwmod_mw_bootstrap_html_def("panel-body");
		$this->set_cont_elem($body);
		$main->add_cont($body);
		$this->panel_body=$body;
		$footer=new mwmod_mw_bootstrap_html_def("panel-footer");
		$this->set_key_cont("footer",$footer);
		$footer->only_visible_when_has_cont=true;
		$main->add_cont($footer);
		$this->panel_footer=$footer;
	}
}
?>
