<?php
class mwmod_mw_bootstrap_icons_icon extends mw_apsubbaseobj{
	var $cod;
	var $iconClass;
	var $libCod;
	var $name;
	var $cusName;
	var $filecod;
	public $classPref;
	function __construct(){
		
	}
	function get_data_for_dg(){
		$r=array(
			"id"=>$this->cod,
			"lib"=>$this->libCod,
			"name"=>$this->get_name(),
			//"cusname"=>$this->cusName,
			"iconclass"=>$this->get_iconClass(),
			
		
		);
		return $r;	
	}
	function get_info_html(){
		$elem=$this->get_info_html_elem();
		return $elem->get_as_html();	
	}
	function get_info_html_elem(){
		$elem= new mwmod_mw_bootstrap_html_def("","div");
		$elem->add_cont($this->new_icon_elem());
		$elem->add_cont($this->get_name());
		return $elem;
		
	}
	function new_icon_elem(){
		$elem= new mwmod_mw_bootstrap_html_def("","span");
		$elem->set_att("class",$this->get_iconClass());
		$elem->set_att("aria-hidden","true");
		return $elem;
		
	}
	function setInfo($libCod,$subCod,$name=false){
		$this->cusName=$name;
		if(!$name){
			$name=$subCod;	
		}
		
		$this->cod=$libCod."-".$subCod;
		$this->libCod=$libCod;

		if(!$classPref=$this->classPref){
			$classPref=$libCod;
		}

		$this->iconClass=$libCod." ".$classPref."-".$subCod;
		$this->name=$name;
	}
	function get_fullCod(){
		return 	$this->iconClass;
	}
	function get_name(){
		if($this->name){
			return $this->name;	
		}
		return $this->cod;	
	}
	function get_iconClass(){
		return $this->iconClass;	
	}
}
?>