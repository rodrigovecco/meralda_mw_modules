<?php
/**
 * Bootstrap 3 collapsible panel that starts in the collapsed state.
 *
 * Versioned copy of the legacy `mwmod_mw_bootstrap_html_template_panelcollapsed`.
 * Same constructor signatures as `mwmod_mw_bootstrap_bootstrap3_html_template_panelcollapse`.
 */
class mwmod_mw_bootstrap_bootstrap3_html_template_panelcollapsed extends mwmod_mw_bootstrap_bootstrap3_html_template_panelcollapse{
	function __construct(){
		$this->collapsed=true;
		$num=func_num_args();
		$args=func_get_args();
		if($main=$this->create_panel_from_contructor($num,$args)){
			$this->create_cont($main);
			$this->update_elems_on_collaps();
		}
	}
}
?>
