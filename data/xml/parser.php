<?php
class mwmod_mw_data_xml_parser extends mw_apsubbaseobj{
	var $root_node_name="root";
	var $data_node_name="data";
	var $doc;
	function __construct(){
	}
	function get_data_from_xml_str($cont){
		if(!$this->create_doc_from_string($cont)){
			return false;	
		}
		return $this->load_data();
	}
	
	function get_node_item_value($node){
		if(!$id=$this->get_item_node_id($node)){
			return false;	
		}
		$type=$node->getAttribute("dataType");
		if($type=="Object"){
			return $this->get_data_from_node($node);	
		}
		$val=$node->textContent;
		if($type=="Bool"){
			if($val=$val+0){
				return true;	
			}else{
				return false;	
			}
		}
		if($type=="Int"){
			return $val+0;
		}
		if($type=="Numeric"){
			return $val+0;
		}
		return $val."";
	}
	
	function get_item_node_id($node){
		if(!$node){
			return false;	
		}
		if($node->nodeType!=XML_ELEMENT_NODE){
			return false;	
		}
		if(!method_exists($node,"getAttribute")){
			return false;	
		}
		if($node->nodeName!="item"){
			return false;
		}
		return $node->getAttribute("id");
	}
	function append_data_node_to_data(&$data,$node){
		if(!$node){
			return false;	
		}
		if(!method_exists($node,"hasChildNodes")){
			return false;	
		}
		if(!$node->hasChildNodes()){
			return false;	
		}
		foreach ($node->childNodes as $child) {
			if($id=$this->get_item_node_id($child)){
				$data[$id]=$this->get_node_item_value($child);	
			}
		}
	
	}
	function get_data_from_node($node){
		$data=array();
		
		$this->append_data_node_to_data($data,$node);
		return $data;
		
	}
	
	function load_data_from_data_node($node){
		if(!$node){
			return false;	
		}
		if(!method_exists($node,"hasChildNodes")){
			return false;	
		}
		if(!$node->hasChildNodes()){
			return false;	
		}
		foreach ($node->childNodes as $child) {
			if($child->nodeType==XML_ELEMENT_NODE){
				if($child->nodeName=="data"){
					return $this->get_data_from_node($child);
				}
				
			}
		}
		
	}
	
	function load_data_from_root_node($node){
		if(!$node){
			return false;	
		}
		if(!method_exists($node,"hasChildNodes")){
			return false;	
		}
		if(!$node->hasChildNodes()){
			return false;	
		}
		foreach ($node->childNodes as $child) {
			if($child->nodeType==XML_ELEMENT_NODE){
				if($child->nodeName==$this->data_node_name){
					return $this->load_data_from_data_node($child);
				}
			}
			
		}

		
	}
	function load_data(){
		if(!$this->doc){
			return false;	
		}
		$doc=$this->doc;
		if(!$nodes=$doc->getElementsByTagName($this->root_node_name)){
			return false;	
		}
		if(!$l=$nodes->length){
			return false;	
		}
		
		return $this->load_data_from_root_node($nodes->item(0));


	}
	function create_doc_from_string($cont){
		$doc  =new DOMDocument('1.0', 'utf8');
		$ok=false;
		if(@$doc->loadXML($cont)){
			$ok=true;
			$this->doc=$doc;
			return true;
		}

	}
	

}
?>