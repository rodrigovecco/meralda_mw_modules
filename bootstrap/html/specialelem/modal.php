<?php
class mwmod_mw_bootstrap_html_specialelem_modal extends mwmod_mw_bootstrap_html_specialelem_elemabs{
	var $fade;
	var $small;
	function __construct($id="mymodal",$fade=true,$small=false){
		$this->init_modal($id,$fade,$small);
	}
	function init_modal($id="mymodal",$fade=true,$small=false){
		$this->init_bt_special_elem("modal","div",false);
		$this->fade=$fade;
		$this->small=$small;
		if($id){
			$this->set_id($id);	
		}
			
	}
	function set_fade($val=true){
		$this->fade=$val;
	}
	function set_small($val=true){
		$this->small=$val;
	}
	
	function get_class_names_list(){
		$r=array();
		$r[]="modal";
		if($this->fade){
			$r[]="fade";	
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