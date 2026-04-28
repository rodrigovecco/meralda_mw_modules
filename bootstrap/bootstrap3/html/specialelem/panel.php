<?php
/**
 * Bootstrap 3 panel special element (`<div class="panel panel-default">`).
 *
 * Equivalent to the legacy `mwmod_mw_bootstrap_html_specialelem_panel` from the
 * Bootstrap 3 era. The current top-level class generates `card` (Bootstrap 4/5),
 * so this versioned copy is provided for UIs that still load Bootstrap 3.
 */
class mwmod_mw_bootstrap_bootstrap3_html_specialelem_panel extends mwmod_mw_bootstrap_html_specialelem_elemabs{
	function __construct($display_mode="default"){
		$this->init_bt_special_elem("panel","div",$display_mode);
		$this->avaible_display_modes="default,primary,success,info,warning,danger,green,yellow,red";
	}
}
?>
