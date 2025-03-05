<?php
abstract class  mwmod_mw_manager_related_relation_abs extends mw_apsubbaseobj{
	private $relman;
	private $index=0;
	var $rel_objects_name;
	private $mainitem;
	private $related_items_num;
	private $related_items_ids;//strlist
	private $related_items_data;//array, not always used
	function get_debug_data(){
		$r=array(
			"num"=>$this->__get_priv_related_items_num(),
			"ids"=>$this->__get_priv_related_items_ids(),
			
		);
		return $r;	
	}
	function get_rel_objects_name(){
		if($this->rel_objects_name){
			return $this->rel_objects_name;	
		}
		return $this->lng_common_get_msg_txt("objects","objetos");
	}
	function get_relations_msg(){
		if(!$num=$this->__get_priv_related_items_num()){
			return false;	
		}
		$p=array(
			"num"=>$num,
			"objs"=>$this->get_rel_objects_name(),
		);
		return $this->lng_common_get_msg_txt("there_are_X_OBJS_related","Existen %num% %objs% relacionados.",$p);
	}
	
	function load_related_items_data(){
		//	
	}
	function load_related_items_num(){
		if($d=$this->__get_priv_related_items_data()){
			return $d["num"]+0;	
		}
		return 0;
	}
	
	function load_related_items_ids(){
		if($d=$this->__get_priv_related_items_data()){
			return $d["ids"]."";	
		}
		return "";
	}
	
	final function __get_priv_related_items_data(){
		if(!isset($this->related_items_data)){
			$this->related_items_data=$this->load_related_items_data();
			if(!is_array($this->related_items_data)){
				$this->related_items_data=array();	
			}
		}
		return $this->related_items_data;
	}
	
	final function __get_priv_related_items_num(){
		if(!isset($this->related_items_num)){
			if(!$this->related_items_num=$this->load_related_items_num()){
				$this->related_items_num=0;	
			}
		}
		return $this->related_items_num;
	}
	final function __get_priv_related_items_ids(){
		if(!isset($this->related_items_ids)){
			if(!$this->related_items_ids=$this->load_related_items_ids()){
				$this->related_items_ids="";	
			}
		}
		return $this->related_items_ids;
	}
	
	
	
	function add2man($index,$relman){
		$this->set_rel_man($relman);
		$this->set_index($index);
	}
	function set_rel_man($relman){
		if(!$m=$this->_set_rel_man($relman)){
			return false;	
		}
		
		$this->set_lngmsgsmancod($m->lngmsgsmancod);
		
		return $m;
	}
	final function set_index($index){
		$this->index=$index;
		return $this->index;
	}
	final function _set_rel_man($relman){
		$this->relman=$relman;
		return $this->relman;
	}
	
	final function __get_priv_relman(){
		return $this->relman; 	
	}
	final function __get_priv_index(){
		return $this->index; 	
	}
	function get_mainitem_id(){
		if($item=$this->__get_priv_mainitem()){
			return $item->get_id();	
		}
	}
	function load_mainitem(){
		if($this->relman){
			return $this->relman->mainitem;	
		}
	}
	
	final function __get_priv_mainitem(){
		if(!isset($this->mainitem)){
			if(!$this->mainitem=$this->load_mainitem()){
				$this->mainitem=false;	
			}
		}
		return $this->mainitem;
	}

}
?>