<?php
class mwmod_mw_jsobj_inputs_select extends mwmod_mw_jsobj_inputs_input{
	var $multiple_elems_choise_mode=false;
	public $DXMode=false;
	function __construct($cod,$objclass=false){
		$this->def_js_class="mw_datainput_item_select";
		$this->init_js_input($cod,$objclass);
	}
	function setDXMode(){
		//under construction
		$this->DXMode=true;
		$this->set_js_class("mw_datainput_dx_selectBox");
	}
	function set_multiple_elems_choise_mode(){
		$this->multiple_elems_choise_mode=true;
		$this->set_js_class("mw_datainput_item_elemschoise");
		
	}
	function addSelectOptions($list,$advMode=true){
		if(!$list){
			return false;	
		}
		if(is_object($list)){
			if(is_a($list,"mwmod_mw_listmanager_listman")){
				return $this->addSelectOptionsByListMan($list);	
			}
		}
		
		if($advMode){
			$lm=new mwmod_mw_listmanager_listman($list);
			return $this->addSelectOptionsByListMan($lm);		
		}
		return $this->addSelectOptionsSimple($list);
		
	}
	function addSelectOptionsByListMan($listman){
		$list=$this->get_array_prop("optionslist");
		return $listman->add_items_to_js_array($list);
	}
	function addSelectOptionsSimple($list){
		if(!is_array($list)){
			return false;
		}
		$r=array();
		foreach($list as $c=>$n){
			if($o=$this->add_select_option($c,$n)){
				$r[]=$o;	
			}
		}
		return $r;
			
		
	}
	
	function add_select_option($cod,$name){
		$list=$this->get_array_prop("optionslist");
		return $list->add_data(array("cod"=>$cod,"name"=>$name));
		
	}
}
?>