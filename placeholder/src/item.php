<?php
class mwmod_mw_placeholder_src_item extends mwmod_mw_placeholder_src_abs{
	function __construct($cod,$value){
		$this->init($cod);
		$this->set_value($value);
	}
	
}
?>