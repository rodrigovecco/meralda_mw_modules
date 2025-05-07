<?php

/**
 * Represents a form input container rendered as a panel.
 * 
 * This class extends the base input object and provides methods to add
 * input groups, submit buttons, and footer button groups for UI components.
 *
 */
class mwmod_mw_jsobj_inputs_frmonpanel extends mwmod_mw_jsobj_inputs_input{
	var $footer_gr;
	/**
	 * Constructor.
	 *
	 * @param string $cod The code identifier for the form (default: "frm").
	 * @param string|false $objclass Optional custom JavaScript class for the object.
	 */
	function __construct($cod="frm",$objclass=false){
		$this->def_js_class="mw_datainput_item_frmonpanel";
		$this->init_js_input($cod,$objclass);
	}
	/**
	 * Adds a main input group to the form.
	 *
	 * @param string $cod The code for the group (default: "data").
	 * @param string|null $inputs_name Optional input name. If null, defaults to $cod.
	 * @return mwmod_mw_jsobj_inputs_input The created group input object.
	 */
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
	
	/**
	 * Adds a submit button to the footer button group.
	 *
	 * @param string $lbl The label for the submit button.
	 * @param string $cod The code for the button (default: "_submit").
	 * @return mwmod_mw_jsobj_inputs_input|null The created submit button, or null if the footer group is not available.
	 */
	function add_submit($lbl,$cod="_submit"){
		$submit=new mwmod_mw_jsobj_inputs_input($cod,"mw_datainput_item_submit");
		if($lbl){
			$submit->set_prop("lbl",$lbl);	
		}
		if($gr=$this->get_footer_gr()){
			return $gr->add_child($submit);	
		}
	}
	/**
	 * Retrieves or creates the footer button group for the form.
	 *
	 * @return mwmod_mw_jsobj_inputs_input The footer group object.
	 */
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