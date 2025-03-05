<?php
class mwmod_mw_jsobj_objcoladv_base extends mwmod_mw_jsobj_newobject{
	var $def_js_class;
	
	var $cod;
	
	private $_children;
	function __construct($cod,$objclass=false){
		$this->init_obj($cod,$objclass);
	}
	
	function addEventListener($cod,$fnc=false){
		if(!$fnc){
			$fnc=new mwmod_mw_jsobj_functionext();
		}
		$this->set_prop("on.$cod",$fnc);
		return $fnc;
	}
	function create_new_child($cod,$objclass=false){
		$ch=new mwmod_mw_jsobj_objcoladv_base($cod,$objclass);
		return $ch;	
	}
	
	function add_new_child($cod,$objclass=false){
		if($item=$this->create_new_child($cod,$objclass)){
			return $this->add_child($item);	
		}
	}
	
	final function add_child($child){
		if(!$cod=$child->cod){
			if(!$cod=$child->get_prop("cod")){
				return false;	
			}
		}
		if(!isset($this->_children)){
			$this->_children=array();	
		}
		$this->_children[$cod]=$child;
		$this->add_child_sub($child);
		return $child;
		
	}
	function add_child_sub($child){
		$list=$this->get_array_prop("children");
		$list->add_data($child);
		
	}
	final function get_children(){
		if(isset($this->_children)){
			return $this->_children;	
		}
		return false;
	}
	function get_child_by_dot_cod($cod){
		if(!$cod){
			return false;	
		}
		
		$keys=explode(".",$cod);
		if(!sizeof($keys)){
			return false;	
		}
		$first=array_shift($keys);
		if(!$child=$this->get_child($first)){
			return false;	
		}
		if(!sizeof($keys)){
			return $child;	
		}
		if(!method_exists($child,"get_child_by_dot_cod")){
			return false;
		}
		$cod=implode(".",$keys);
		return $child->get_child_by_dot_cod($cod);
		
		
	}
	final function get_child($cod){
		if(!$cod){
			return false;	
		}
		if(isset($this->_children)){
			return $this->_children[$cod];	
		}
		return false;
	}
	
	function set_cod($cod){
		$this->cod=$cod;
		$this->set_prop("cod",$cod);	
		
	}
	function init_obj($cod,$objclass=false){
		$this->set_js_class($objclass);
		$this->set_cod($cod);
		
	}
	function set_js_class($objclass=false){
		if(!$objclass){
			$objclass=$this->def_js_class;	
		}
		$this->set_fnc_name($objclass);
	}
	
	function get_as_js_val_new_object(){
		$r="new ".$this->get_fnc_name()."(";
		$r.=$this->get_as_js_obj_args_in();
		$r.=")";
		return $r;
	}
	function get_as_js_val_no_new_object(){
		if(!isset($this->props)){
			return "{}";	
		}
		return $this->get_prop_as_js_val($this->props);
	}
	function get_fnc_name(){
		if($this->fnc_name){
			return 	$this->fnc_name;
		}
		
	}

	function get_as_js_val(){
		if($this->get_fnc_name()){
			return $this->get_as_js_val_new_object();	
		}else{
			return $this->get_as_js_val_no_new_object();
		}
	}
	
}
?>