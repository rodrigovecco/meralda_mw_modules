<?php
class mwmod_mw_bootstrap_html_grid_col extends mwmod_mw_bootstrap_html_elem{
	private $_classes_mans;
	
	var $classAtt;
	
	
	
	function __construct($width=12,$cont=false){
		$tagname="div";
		$atts=false;
		$this->set_tagname($tagname);
		$this->init_atts($atts);
		if($cont!==false){
			$this->add_cont($cont);
		}
		if($width!==false){
			$this->set_width($width);
		}
		$this->init_class_att();
		//$this->set_att("class","row");
	}
	
	
	
	function init_class_att(){
		$c=new mwmod_mw_bootstrap_html_grid_colclassatt($this);
		$this->set_att("class",$c);
		$this->classAtt=$c;
		
	}
	function add_class($class){
		if($a=$this->getColClassAtt()){
			return $a->add_class($class);	
		}
		
	}
	function remove_class($class){
		if($a=$this->getColClassAtt()){
			return $a->remove_class($class);	
		}
		
	}
	function getColClassAtt(){
		if(!$this->classAtt){
			$this->init_class_att();
		}
		return $this->classAtt;
	}
	function get_class_name(){
		$r=array();
		if($list=$this->get_classes_mans()){
			foreach($list as $e){
				$e->add_2_class_list($r);
			}
		}
		return implode(" ",$r);
			
	}
	final function init_classes_mans(){
		if(isset($this->_classes_mans)){
			return;
		}
		$this->_classes_mans=array();
		$list=array("md","xs","sm","lg");
		foreach($list as $cod){
			$e=new mwmod_mw_bootstrap_html_grid_colclass($cod);
			$this->_classes_mans[$cod]=$e;
		}
	}
	function set_width($v=12,$cod="def"){
		if($list=$this->get_classes_mans($cod)){
			foreach($list as $e){
				$e->set_width($v);	
			}
		}
		
	}
	function set_offset($v=1,$cod="def"){
		if($list=$this->get_classes_mans($cod)){
			foreach($list as $e){
				$e->set_offset($v);	
			}
		}
		
	}
	function set_pull($v=1,$cod="def"){
		if($list=$this->get_classes_mans($cod)){
			foreach($list as $e){
				$e->set_pull($v);	
			}
		}
		
	}
	function set_push($v=1,$cod="def"){
		if($list=$this->get_classes_mans($cod)){
			foreach($list as $e){
				$e->set_push($v);	
			}
		}
	}
	final function get_class_man($cod="md"){
		$this->init_classes_mans();
		return $this->_classes_mans[$cod];
	}
	
	final function get_classes_mans($cods=false){
		$this->init_classes_mans();
		$r=$this->_classes_mans;
		if($cods=="def"){
			//$cods=array("md","xs","sm","lg");
			$cods=array("md");
		}
		if(!$cods){
			return $r;	
		}
		if(!is_array($cods)){
			$cods=explode(",",$cods);
		}
		$rr=array();
		foreach($cods as $c){
			if($c=trim($c)){
				if($r[$c]){
					$rr[$c]=$r[$c];	
				}
			}
		}
		return $rr;
	}
	
	
}

?>