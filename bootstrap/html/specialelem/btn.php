<?php
class mwmod_mw_bootstrap_html_specialelem_btn extends mwmod_mw_bootstrap_html_specialelem_elemabs{
	var $outline=false;
	function __construct($cont=false,$display_mode="default"){
		$this->init_bt_special_elem("btn","button",$display_mode);
		$this->avaible_display_modes="default,primary,success,info,warning,danger,link";
		if($cont!==false){
			$this->add_cont($cont);
		}
		$this->set_att("type","button");
	
	}
	function add_other_class_names_to_list(&$list){
		
	}
	function get_class_names_list(){
		$r=array();
		if($this->main_class_name){
			$r[]=$this->main_class_name;
			if($this->outline){
				$r[]="btn-outline";	
			}
			
			if($this->display_mode){
				$r[]=$this->main_class_name."-".$this->display_mode;	
			}
			
		}
		if($list=$this->get_addicional_classes()){
			foreach($list as $c){
				$r[]=$c;	
			}
		}
		$this->add_other_class_names_to_list($r);
		return $r;
		
	}
	
}

?>