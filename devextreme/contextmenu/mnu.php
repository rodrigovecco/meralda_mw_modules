<?php
class mwmod_mw_devextreme_contextmenu_mnu extends mwmod_mw_jsobj_objcoladv_base{
	function __construct($cod="contextMnu",$objclass="mw_dx_context_menu"){
		$this->def_js_class="mw_dx_context_menu";
		$this->init_obj($cod,$objclass);
		
	}
	function add_mnu_item($cod,$lbl=false,$objclass=false){
		if(!$ch=$this->add_new_child($cod,$objclass)){
			return false;
		}
		if($lbl){
			$ch->set_prop("options.text",$lbl);	
		}
		return $ch;
	}
	function create_new_child($cod,$objclass=false){
		$ch=new mwmod_mw_devextreme_contextmenu_mnuitem($cod,$objclass);
		return $ch;	
	}

	
}
?>