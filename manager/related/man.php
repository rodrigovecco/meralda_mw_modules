<?php
class  mwmod_mw_manager_related_man extends mw_apsubbaseobj{
	private $mainitem;
	private $rel_objects_num;
	private $rel_objects_msgs;
	
	private $_relations=array();
	
	function __construct($mainitem){
		$this->init($mainitem);	
	}
	function get_debug_data(){
		$r=array();
		if($items=$this->get_relations()){
			foreach($items as $id=>$item){
				$r[$id]=$item->get_debug_data();	
			}
		}
		$rr=array(
			"items"=>$r,
			"num"=>$this->get_rel_objects_num(),
			"msgplain"=>$this->get_relations_msg_plain(),
			"html"=>$this->get_relations_msg_html(),
		);
		return $rr;
		
	}
	function get_relations_msg_html(){
		if(!$msgs=$this->get_relations_msg_plain()){
			return "";	
		}
		return nl2br($msgs);
	}

	function get_relations_msg_plain(){
		if(!$msgs=$this->__get_priv_rel_objects_msgs()){
			return "";
		}
		if(!sizeof($msgs)){
			return "";	
		}
		return implode("\n",$msgs);
	}
	function get_rel_objects_num(){
		return $this->__get_priv_rel_objects_num();	
	}
	final function init_rel_objects_data(){
		if(isset($this->rel_objects_num)){
			return;	
		}
		$this->rel_objects_num=0;
		$this->rel_objects_msgs=array();
		if($items=$this->get_relations()){
			foreach($items as $id=>$item){
				if($n=$item->related_items_num){
					$this->rel_objects_num+=$n;
					if($msg=$item->get_relations_msg()){
						$this->rel_objects_msgs[]=$msg;	
					}
				}
			}
		}
		
		
		
	}
	final function __get_priv_rel_objects_num(){
		$this->init_rel_objects_data();
		return $this->rel_objects_num; 	
	}
	final function __get_priv_rel_objects_msgs(){
		$this->init_rel_objects_data();
		return $this->rel_objects_msgs; 	
	}
	
	function init($mainitem){
		$this->_init($mainitem);
		//$this->set_mainap();	
		$this->set_lngmsgsmancod($mainitem->lngmsgsmancod);
	}
	final function _init($mainitem){
		$this->mainitem=$mainitem;
		//$this->set_mainap();	
		//$this->set_lngmsgsmancod($mainitem->lngmsgsmancod);
	}
	
	final function __get_priv_mainitem(){
		return $this->mainitem; 	
	}
	final function get_relations(){
		return $this->_relations;	
	}
	final function get_relation($index){
		if(!$index=$index+0){
			return false;	
		}
		return $this->_relations[$index];
	}
	final function add_relation($rel){
		$index=sizeof($this->_relations)+1;
		$rel->add2man($index,$this);
		$this->_relations[$index]=$rel;
		return $rel;	
	}

}
?>