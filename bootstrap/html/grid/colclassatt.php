<?php
class mwmod_mw_bootstrap_html_grid_colclassatt{
	var $col;
	var $otherClases=array();
	function __construct($col){
		$this->col=$col;
	}
	function get_as_att($cod){
		//return "class='".$this->col->get_class_name()."'";
		return "class='".$this->getAllClassesStr()."'";
		//$list=new array();
		
	}
	function getAllClassesStr(){
		if(!$list=$this->getAllClasses()){
			return "";	
		}
		return implode(" ",$list );
	}
	function getAllClasses(){
		$r=array();
		$r[]=$this->col->get_class_name();
		if($list=$this->getOtherClasses()){
			foreach($list as $c){
				$r[]=$c;	
			}
		}
		return $r;
	}
	function getOtherClasses(){
		return $this->otherClases;	
	}
	function remove_class($class){
		if(!$class){
			return;	
		}
		unset($this->otherClases[$class]);
		return true;
		
	}
	
	function add_class($class){
		if(!$class){
			return;	
		}
		$list=explode(" ",$class);
		$r=0;
		foreach($list as $c){
			if($this->_add_class($c)){
				$r++;	
			}
		}
		return $r;
	}
	function _add_class($class){
		if(!$class){
			return;	
		}
		$this->otherClases[$class]=$class;
		return true;
	}

	
}

?>