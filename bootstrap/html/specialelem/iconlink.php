<?php
class mwmod_mw_bootstrap_html_specialelem_iconlink extends mwmod_mw_bootstrap_html_specialelem_elemabs{
	var $icon_class;
	var $icon_container;
	function __construct($icon_class){
		$this->init_bt_special_elem(false,"a");
		$this->set_att("href","#");
		$this->icon_container= new mwmod_mw_bootstrap_html_def("","span");
		$this->add_cont($this->icon_container);
		$this->set_icon_class($icon_class);
	
	}
	function set_icon_class($icon_class){
		$this->icon_class;
		$this->icon_container->set_att("class",$icon_class);
	}
	function get_class_names_list(){
		$r=array();
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