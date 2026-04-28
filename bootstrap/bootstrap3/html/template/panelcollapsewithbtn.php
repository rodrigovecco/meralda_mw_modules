<?php
/**
 * Bootstrap 3 collapsible panel with an `<a class="mw_collaps_btn">` toggle in the header,
 * separate from the title element.
 *
 * Versioned copy of the legacy `mwmod_mw_bootstrap_html_template_panelcollapsewithbtn`.
 *
 * @property-read mwmod_mw_bootstrap_html_def $panel_heading
 * @property-read mwmod_mw_bootstrap_html_def $panel_collapse
 * @property-read mwmod_mw_bootstrap_html_def $panel_body
 * @property-read mwmod_mw_bootstrap_html_def $panel_footer
 * @property-read mwmod_mw_bootstrap_html_def $collapsbtn
 */
class mwmod_mw_bootstrap_bootstrap3_html_template_panelcollapsewithbtn extends mwmod_mw_bootstrap_bootstrap3_html_template_panelcollapse{
	function __construct(){
		$num=func_num_args();
		$args=func_get_args();
		if($main=$this->create_panel_from_contructor($num,$args)){
			$this->create_cont($main);
		}
	}
	function create_cont($main){
		$this->set_main_elem($main);
		$head=new mwmod_mw_bootstrap_html_def("panel-heading");
		$this->panel_heading=$head;

		$collaps_btn=new mwmod_mw_bootstrap_html_elem("a");
		$collaps_btn->set_att("class","mw_collaps_btn");
		$collaps_btn->set_att("data-toggle","collapse");
		if($this->collapse_dataparent_id){
			$collaps_btn->set_att("data-parent","#".$this->collapse_dataparent_id);
		}
		if($this->collapse_body_id){
			$collaps_btn->set_att("data-target","#".$this->collapse_body_id);
		}else{
			$collaps_btn->set_att("data-auto-target",".panel-collapse");
			$collaps_btn->set_att("data-auto-target-parent",".panel");
			$collaps_btn->set_att("href","#");
		}
		$collaps_btn->set_att("aria-expanded","true");
		$collaps_btn->set_style("cursor","pointer");
		$collaps_btn->add_cont("<span class='fa arrow'> </span>");
		$this->set_key_cont("collapsbtn",$collaps_btn);
		$this->collapsbtn=$collaps_btn;

		$title=new mwmod_mw_bootstrap_html_def("panel-title");

		$head->add_cont($collaps_btn);
		$head->add_cont($title);
		$this->set_title_elem($title);
		$main->add_cont($head);

		$bodycontainer=new mwmod_mw_bootstrap_html_def("panel-collapse collapse in");
		$this->panel_collapse=$bodycontainer;
		$bodycontainer->set_att("aria-expanded","true");
		if($this->collapse_body_id){
			$bodycontainer->set_att("id",$this->collapse_body_id);
		}
		$this->set_key_cont("bodycontainer",$bodycontainer);

		$body=new mwmod_mw_bootstrap_html_def("panel-body");
		$this->panel_body=$body;
		$this->set_cont_elem($body);
		$bodycontainer->add_cont($body);
		$main->add_cont($bodycontainer);

		$footer=new mwmod_mw_bootstrap_html_def("panel-footer");
		$this->set_key_cont("footer",$footer);
		$footer->only_visible_when_has_cont=true;
		$this->panel_footer=$footer;
		if($this->collapse_footer){
			$bodycontainer->add_cont($footer);
		}else{
			$main->add_cont($footer);
		}
	}
}
?>
