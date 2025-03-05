<?php
abstract class mwmod_mw_bootstrap_html_specialelem_elemabs extends mwmod_mw_bootstrap_html_elem{
	var $avaible_display_modes="default,primary,success,info,warning,danger";
	private $main_class_name;
	private $display_mode="";
	private $_additional_classes=array();
	var $id;
	function get_avaible_display_modes(){
		return explode(",",$this->avaible_display_modes);
	}
	
	function set_id($id){
		$this->id=$id;
		$this->set_att("id",$id);
	}
	final function init_bt_special_elem($main_class_name,$tagname="div",$display_mode="default"){
		$this->set_tagname($tagname);
		$this->set_main_class_name($main_class_name);
		$this->set_display_mode($display_mode);
		
		$this->init_atts();
		$this->init_class_att();
		
	}
	function init_class_att(){
		$c=new mwmod_mw_bootstrap_html_specialelem_classatt($this);
		$this->set_att("class",$c);
		
	}
	
	function get_class_name(){
		if(!$list=$this->get_class_names_list()){
			return "";	
		}
		return implode(" ",$list);
	}
	function get_class_names_list(){
		$r=array();
		if($this->main_class_name){
			$r[]=$this->main_class_name;
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
	function add_other_class_names_to_list(&$list){
		
	}
	final function get_addicional_classes(){
		return 	$this->_additional_classes;
	}
	final function add_additional_class($class){
		if($class){
			$this->_additional_classes[$class]=$class;		
		}
		//
	}
	final function unset_additional_classes(){
		$this->_additional_classes=array();
	}
	final function set_main_class_name($main_class_name){
		$this->main_class_name=$main_class_name;
	}
	final function set_display_mode($mode="default"){
		$this->display_mode=$mode;
	}
	final function __get_priv_display_mode(){
		return $this->display_mode;	
	}
	final function __get_priv_main_class_name(){
		return $this->main_class_name;	
	}

	
	
}

?>