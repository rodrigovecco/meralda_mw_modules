<?php
class mwmod_mw_jsobj_inputs_tagbox extends mwmod_mw_jsobj_inputs_select{
	
	function __construct($cod,$objclass=false){
		$this->def_js_class="mw_datainput_dx_tagBox";
		$this->init_js_input($cod,$objclass);
		
	}
	
	
	function setSearchEnabled(){
		$this->set_prop("DXOptions.searchEnabled",true);	
		
		
	}
	
	
}
?>