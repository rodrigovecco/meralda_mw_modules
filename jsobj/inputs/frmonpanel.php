<?php

//
class mwmod_mw_jsobj_inputs_frmonpanel extends mwmod_mw_jsobj_inputs_input{
	var $footer_gr;
	function __construct($cod="frm",$objclass=false){
		$this->def_js_class="mw_datainput_item_frmonpanel";
		$this->init_js_input($cod,$objclass);
	}
	function add_data_main_gr($cod="data",$inputs_name=NULL){
		$gr=new mwmod_mw_jsobj_inputs_input($cod,"mw_datainput_item_group");
		if(!$inputs_name){
			if(is_null($inputs_name)){
				$inputs_name=$cod;	
			}
		}
		if($inputs_name){
			$gr->set_prop("input_name",$inputs_name);
		}
		
		return $this->add_child($gr);
	}
	
	
	function add_submit($lbl,$cod="_submit"){
		$submit=new mwmod_mw_jsobj_inputs_input($cod,"mw_datainput_item_submit");
		if($lbl){
			$submit->set_prop("lbl",$lbl);	
		}
		if($gr=$this->get_footer_gr()){
			return $gr->add_child($submit);	
		}
	}
	function get_footer_gr(){
		if(isset($this->footer_gr)){
			return $this->footer_gr;	
		}
		$this->footer_gr=new mwmod_mw_jsobj_inputs_input("footer_gr","mw_datainput_item_btnsgroup");
		$this->footer_gr->set_prop("onFooter",true);
		return $this->add_child($this->footer_gr);
	}
	
}
?>