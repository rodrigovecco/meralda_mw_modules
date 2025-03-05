<?php

class mwmod_mw_jsobj_array extends mwmod_mw_jsobj_obj{
	var $array_data=array();
	var $array_data_assoc=array();
	function __construct($data=false){
		if(is_array($data)){
			$this->array_data=$data;	
		}
	}
	
	function clearData(){
		$this->array_data=array();	
		$this->array_data_assoc=array();	
	}
	function setData($data=false,$assoc=false){
		$this->clearData();
		$this->addArrayData($data,$assoc);
	}
	function addArrayData($data,$assoc=false){
		if(!$data){
			return false;
		}
		if(!is_array($data)){
			return false;	
		}
		
		foreach($data as $c=>$d){
			if($assoc){
				$this->add_data_with_cod($c,$d);	
			}else{
					
				$this->add_data($d);	
			}
		}
	}
	
	
	
	function add_data_obj($val=false){
		if(!$val){
			$val= new mwmod_mw_jsobj_obj();	
		}
		return $this->add_data($val);
	}
	function add_data($val){
		$this->array_data[]=$val;
		return $val;
	}
	function get_data_assoc_by_cod($cod){
		if($cod){
			return $this->array_data_assoc[$cod];
		}
	}
	function add_data_with_cod($cod,$data){
		if($cod){
			$this->array_data_assoc[$cod]=$data;	
		}
		return $this->add_data($data);
	}
	function get_data(){
		return $this->array_data;	
	}
	function get_data_elem_by_cod($cod,$propkey="cod",$onlyobjs=false){
		if(!$list=$this->get_data_elems_by_cod($cod,$propkey,$onlyobjs)){
			return false;	
		}
		return $list[0];
	}

	function get_data_elems_by_cod($cod,$propkey="cod",$onlyobjs=false){
		if(!$items=$this->get_data()){
			return false;	
		}
		$r=array();
		foreach($items as $item){
			if(is_array($item)){
				if(!$onlyobjs){
					if($item[$propkey]==$cod){
						$r[]=$item; 	
					}
				}
			}
			if(is_object($item)){
				if(is_a($item,"mwmod_mw_jsobj_obj")){
					if($item->get_prop($propkey)==$cod){
						$r[]=$item;	
					}
				}
			}
			
		}
		return $r;
	}
	function get_as_js_val(){
		$r="[";
		if($data=$this->get_data()){
			$n=sizeof($data);
			$x=0;
			foreach($data as $d){
				$x++;
				$r.=$this->get_prop_as_js_val($d);
				if($x<$n){
					$r.=",";	
				}
			}
		}
		$r.="]";
		return $r;
	}

}
?>