<?php
class mwmod_mw_bootstrap_html_template_panelcollapse extends mwmod_mw_bootstrap_html_template_abs{
	var $collapse_dataparent_id;
	var $collapse_body_id;
	var $display_mode="default";
	var $collapse_footer=true;
	var $panel_heading;
	var $panel_collapse;
	var $panel_body;
	var $panel_footer;
	var $collapsbtn;
	var $autocreate_panel=true;
	
	var $collapsed=false;
	
	
	//var $collpsed=true;
	function __construct(){
		//args: $collapse_dataparent_id,$collapse_body_id,$display_mode="default"
		//args: $display_mode="default"
		//args: $autocreate_panel=true
		$num=func_num_args();
		$args=func_get_args();
		
		if($main=$this->create_panel_from_contructor($num,$args)){
			$this->create_cont($main);
		}
	
		/*
		$this->collapse_dataparent_id=$collapse_dataparent_id;
		$this->collapse_body_id=$collapse_body_id;
		$main=new mwmod_mw_bootstrap_html_specialelem_panel($display_mode);
		$this->create_cont($main);
		*/
		
	}
	function set_collapsed($val=true){
		if($val){
			$this->collapsed=true;	
		}else{
			$this->collapsed=false;	
		}
		$this->update_elems_on_collaps();
	}
	function update_elems_on_collaps(){
		
		$this->update_elems_collaps_btn($this->collapsbtn);
		$this->update_elems_collaps_elem($this->panel_collapse);
		
		
	}
	function update_elems_collaps_elem($elem){
		if(!$elem){
			return false;	
		}
		if($this->collapsed){
			$elem->remove_class("show");
			//$elem->set_att("class","panel-collapse collapse");
			$elem->set_att("aria-expanded","false");	

		}else{
			$elem->add_class("show");
			//$elem->set_att("class","panel-collapse collapse in");	
			$elem->set_att("aria-expanded","true");	
		}
		
	}
	function update_elems_collaps_btn($elem){
		if(!$elem){
			return false;	
		}
		if($this->collapsed){
			$elem->add_class("collapsed");
			//$elem->set_att("class","panel-collapse collapse");
			$elem->set_att("aria-expanded","false");	

		}else{
			$elem->remove_class("collapsed");
			$elem->set_att("aria-expanded","true");	
		}
		
	}
	
	
	function create_panel_from_contructor($num,$args){
		$this->set_params_from_contructor($num,$args);
		if($this->autocreate_panel){
			return $this->new_panel();		
		}
		
		
	}
	function create_panel(){
		$main=$this->new_panel();
		$this->create_cont($main);
		return $main;
			
	}
	
	
	function new_panel(){
		$main=new mwmod_mw_bootstrap_html_specialelem_panel($this->display_mode);
		return $main;
			
	}
	function set_params_from_contructor($num,$args){
		if($num<1){
			return;	
		}
		if($num==1){
			if(is_bool($args[0])){
				$this->autocreate_panel=$args[0];
				return;
			}
			$this->display_mode=$args[0];
			return;
		}
		if($num==2){
			$this->collapse_dataparent_id=$args[0];
			$this->collapse_body_id=$args[1];
			return;
		}
		if($num>2){
			$this->collapse_dataparent_id=$args[0];
			$this->collapse_body_id=$args[1];
			$this->display_mode=$args[2];
			
		}	
		
	}
	
	function create_cont($main){
		$this->set_main_elem($main);
		$head=new mwmod_mw_bootstrap_html_def("card-header");
		$this->panel_heading=$head;
		$collaps_btn=new mwmod_mw_bootstrap_html_elem("div");
		$collaps_btn->set_att("data-toggle","collapse");
		if($this->collapse_dataparent_id){
			$collaps_btn->set_att("data-parent","#".$this->collapse_dataparent_id);
		}

		if($this->collapse_body_id){
			$collaps_btn->set_att("data-target","#".$this->collapse_body_id);
		}else{
			$collaps_btn->set_att("data-auto-target",".card-collapse");
			$collaps_btn->set_att("data-auto-target-parent",".card");
			$collaps_btn->set_att("href","#");
		}
		
		//$collaps_btn->set_att("href","#".$this->collapse_body_id);
		$collaps_btn->set_att("aria-expanded","true");
		$collaps_btn->set_style("cursor","pointer");
		$this->set_key_cont("collapsbtn",$collaps_btn);
		$head->add_cont($collaps_btn);
		$this->set_title_elem($collaps_btn);
		$main->add_cont($head);
		$bodycontainer=new mwmod_mw_bootstrap_html_def("card-collapse collapse in");
		$bodycontainer->set_att("aria-expanded","true");
		$this->panel_collapse=$bodycontainer;
		if($this->collapse_body_id){
			$bodycontainer->set_att("id",$this->collapse_body_id);
		}
		$this->collapsbtn=$collaps_btn;
		//$bodycontainer->set_att("id",$this->collapse_body_id);
		$this->set_key_cont("bodycontainer",$bodycontainer);
		$body=new mwmod_mw_bootstrap_html_def("card-body");
		$this->set_cont_elem($body);
		$this->panel_body=$body;
		$bodycontainer->add_cont($body);
		$main->add_cont($bodycontainer);
		$footer=new mwmod_mw_bootstrap_html_def("card-footer");
		$this->panel_footer=$footer;
		$this->set_key_cont("footer",$footer);
		$footer->only_visible_when_has_cont=true;
		//$main->add_cont($footer);
		if($this->collapse_footer){
			$bodycontainer->add_cont($footer);	
			
		}else{
			$main->add_cont($footer);
			
		}
		//$this->update_elems_on_collaps();
	}

}

?>