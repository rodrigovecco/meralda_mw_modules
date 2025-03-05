<?php
class mwmod_mw_bootstrap_html_specialelem_modaldialog extends mwmod_mw_bootstrap_html_specialelem_elemabs{
	var $small;
	function __construct($small=false){
		$this->init_modal($small);
	}
	function init_modal($small=false){
		$this->init_bt_special_elem("modal-dialog","div",false);
		$this->small=$small;
			
	}
	function set_small($val=true){
		$this->small=$val;
	}
	
	function get_class_names_list(){
		$r=array();
		$r[]="modal-dialog";
		if($this->small){
			$r[]="modal-sm";	
		}else{
			$r[]="modal-xl";		
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