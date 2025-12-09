<?php
class mwmod_mw_jsobj_inputs_color extends mwmod_mw_jsobj_inputs_select{
	
	function __construct($cod,$objclass=false){
		$this->def_js_class="mw_datainput_dx_colorBox";
		$this->init_js_input($cod,$objclass);

		
	}
	function setEditAlphaChannel($bool){
		$this->set_prop("editAlphaChannel",$bool);
	}
	
	
	
	
	
}
?>