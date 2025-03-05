<?php
class mwmod_mw_mnu_items_dropdown_side1 extends mwmod_mw_mnu_items_dropdown_side{
	
	function __construct($cod,$etq,$parent,$url=false){
		$this->init($cod,$etq,$parent,$url);
		$this->side_css_sub_class="nav-third-level";
	}
	
}

?>