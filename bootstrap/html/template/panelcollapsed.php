<?php
class mwmod_mw_bootstrap_html_template_panelcollapsed extends mwmod_mw_bootstrap_html_template_panelcollapse{
	function __construct(){
		//args: $collapse_dataparent_id,$collapse_body_id,$display_mode="default"
		//args: $display_mode="default"
		//args: $autocreate_panel=true
		$this->collapsed=true;
		$num=func_num_args();
		$args=func_get_args();
		
		if($main=$this->create_panel_from_contructor($num,$args)){
			$this->create_cont($main);
			$this->update_elems_on_collaps();
		}

		/*
		$this->collapse_dataparent_id=$collapse_dataparent_id;
		$this->collapse_body_id=$collapse_body_id;
		$main=new mwmod_mw_bootstrap_html_specialelem_panel($display_mode);
		$this->create_cont($main);
		
		*/
		
	}

}

?>