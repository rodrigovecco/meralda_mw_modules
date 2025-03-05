<?php
class mwmod_mw_data_xml extends mw_object_as_array{
	var $id;
	
	private $_items=array();
	
	var $deep=0;
	var $parent_item;
	var $value;
	private $_value_mode=false;
	var $xml_self_output=false;
	var $use_tabs=false;
	function __construct($id){
		$this->id=$id;	
	}
	function root_do_all_output(){
		if($root=$this->get_root()){
			$root->xml_output_start();
			$root->output_all();
			return true;
		}
	}
	
	function get_root(){
		if($this->parent_item){
			return $this->parent_item->get_root();	
		}
		return $this;
	}
	function xml_output_start(){
		ob_end_clean();
		header('Content-Type: text/xml; charset=utf-8');
		echo '<?xml version="1.0" encoding="utf-8"?>';
	
	}
	function get_output_for_file(){
		$html='<?xml version="1.0" encoding="utf-8"?>'."\n";
		$html.=$this->get_xml_full();
		return $html;	
	}
	function output_all(){
		
		echo $this->get_xml_open();
		
		$this->output_xml_cont();
		echo $this->get_xml_close();
			
	}
	function get_xml_full(){
		$r="";
		$r.=$this->get_xml_open();
		$r.=$this->get_xml_cont();
		$r.= $this->get_xml_close();
		return $r;
		
	}

	function output_xml_cont(){
		if($this->is_value_mode()){
			$val=$this->get_value();
			echo mw_array2xml_parse_node_string_value($val);
		}else{
			if(!$items=$this->get_items()){
				echo "";
				return;	
			}
			
			foreach($items as $item){
				$item->output_all();	
			}
			
		}
			
	}
	
	function get_xml_cont(){
		if($this->is_value_mode()){
			$val=$this->get_value();
			return mw_array2xml_parse_node_string_value($val);
		}else{
			if(!$items=$this->get_items()){
				return "";	
			}
			$r="";
			foreach($items as $item){
				$r.=$item->get_xml_full();	
			}
			return $r;
		}
			
	}
	
	function get_xml_open(){
		$r=$this->get_margin_tabs();
		if($this->is_value_mode()){
			$val=$this->get_value();
			$dtype=mw_array2xml_str_data_type($val);
			$r.="<item id='".$this->id."' dataType='$dtype'>";
		}else{
			$r.="<item id='".$this->id."' dataType='Object'>\n";
		}
		return $r;
	}
	function get_xml_close(){
		$r="";
		if(!$this->is_value_mode()){
			$r.=$this->get_margin_tabs();	
		}
		$r.="</item>\n";
		return $r;
	}
	function is_value_mode(){
		return $this->_is_value_mode();	
	}
	final function _is_value_mode(){
		return $this->_value_mode;
	}
	final function set_value_mode($val=true){
		$this->_value_mode=$val;	
	}
	
	function __mw_array_allow_write(){
		return false;	
	}
	function get_value(){
		return $this->value;	
	}
	final function get_items(){
		return $this->_items;	
	}
	function get_props_as_array_or_str(){
		if($this->is_value_mode()){
			$val=$this->get_value();
			return mw_array_get_obj_as_array_or_str($val);
		}
		$r=array();
		if(!$items=$this->get_items()){
			return $r;
		}
		foreach($items as $cod=>$item){
			$r[$cod]=$item->get_props_as_array_or_str();	
		}
		return $r;
	}

	function set_value($value){
		if(is_array($value)){
			$this->set_value_mode(false);
			foreach($value as $cod=>$v){
				$this->set_value_from_dot_cod($cod,$v);
			}
		}else{
			$this->set_value_mode(true);
			$this->value=$value;	
		}
	}
	function set_value_from_dot_cod($cod,$value){
		if(!$item=$this->get_subitem_from_dot_cod($cod)){
			return false;
		}
		$this->set_value_mode(false);
		$item->set_value($value);
		return $item;
	}
	function get_subitem_from_dot_cod($cod,$create_non_existing=true){
		if(!$cod){
			return false;	
		}
		$list=explode(".",$cod,2);
		if(!$item=$this->get_item($list[0]??null,$create_non_existing)){
			return false;
		}

		if(!isset($list[1])or!$list[1]??null){
			return $item;	
		}else{
			return $item->get_subitem_from_dot_cod($list[1],$create_non_existing);	
		}
	}
	
	function set_parent($parent){
		$this->parent_item=$parent;
		$this->deep=$parent->get_deep()+1;
	}
	function get_deep(){
		return $this->deep;	
	}
	function get_parent(){
		return $this->parent_item;
	}
	function create_sub_item($cod){
		$item=new mwmod_mw_data_xml($cod);
		return $item;	
	}
	final function add_sub_item($item){
		$id=$item->id;
		$this->_items[$id]=$item;
		$item->set_parent($this);
		return $item;
	}
	final function get_items_num(){
		return sizeof($this->_items);		
	}
	function add_sub_item_auto_cod($item=false){
		if($item){
			return $this->add_sub_item($item);	
		}
		$cod=$this->get_new_item_auto_id();
		if($item=$this->create_sub_item($cod)){
			return $this->add_sub_item($item);	
		}
		
	}
	function get_new_item_auto_id(){
		return $this->get_items_num()+1;
	}
	final function get_existing_item($cod){
		if(!$cod){
			return false;	
		}
		if($this->_items[$cod]??null){
			return $this->_items[$cod];	
		}
		return false;	
	}
	function htmlItem($cod,$replace=true,$create_non_existing_parent=true){
		//no produce error si no existe, puede devolver un item no vinculado!!!
		if($item=$this->get_html_item($cod,$replace,$create_non_existing_parent)){
			return $item;	
		}
		return  new mwmod_mw_html_cont_varcont();
	}
	function get_html_item($cod,$replace=false,$create_non_existing_parent=true){
		if(!$cod){
			return false;	
		}
		$a=explode(".",$cod);
		if(sizeof($a)>1){
			$c=array_pop($a);
			$parentcod=implode(".",$a);
			if($parent=$this->get_subitem_from_dot_cod($parentcod,$create_non_existing_parent)){
				return $parent->_get_html_item($c,$replace);
			}
		}else{
			return $this->_get_html_item($cod,$replace);	
		}
		
	}
	function _get_html_item($cod,$replace=false){
		if(!$cod){
			return false;	
		}
		if($item=$this->_get_subitemByClass_html($cod,$replace)){
			if(method_exists($item,"get_html_elem")){
				if($html=$item->get_html_elem()){
					return $html;	
				}
			}
				
		}
		
		///se modificó 20180310
		
		/*
		if($item=$this->get_item($cod,false)){
			if(method_exists($item,"get_html_elem")){
				if($html=$item->get_html_elem()){
					return $html;	
				}
			}
			if(!$replace){
				return false;	
			}
		}else{
			
			$item=new mwmod_mw_data_xml_html($cod);
			if($this->add_sub_item($item)){
				return $item->get_html_elem();	
			}
			
		}
		*/
	}
	function _get_subitemByClass_html($cod,$replace=false){
		if(!$cod){
			return false;	
		}
		if($item=$this->get_item($cod,false)){
			if(method_exists($item,"get_html_elem")){
				return $item;	
			}
			if(!$replace){
				return false;	
			}
		}
		$item=new mwmod_mw_data_xml_html($cod);
		if($this->add_sub_item($item)){
			return $item;	
		}
		
	}
	
	function jsItem($cod,$replace=true,$create_non_existing_parent=true){
		//no produce error si no existe, puede devolver un item no vinculado!!!
		if($item=$this->get_js_item($cod,$replace,$create_non_existing_parent)){
			return $item;	
		}
		return new mwmod_mw_jsobj_obj(); 
	}
	
	function get_js_item($cod,$replace=false,$create_non_existing_parent=true){
		if(!$cod){
			return false;	
		}
		$a=explode(".",$cod);
		if(sizeof($a)>1){
			$c=array_pop($a);
			$parentcod=implode(".",$a);
			if($parent=$this->get_subitem_from_dot_cod($parentcod,$create_non_existing_parent)){
				return $parent->_get_js_item($c,$replace);
			}
		}else{
			return $this->_get_js_item($cod,$replace);	
		}
		
	}
	function _get_js_item($cod,$replace=false){
		if(!$cod){
			return false;	
		}
		if($item=$this->_get_subitemByClass_js($cod,$replace)){
			if(method_exists($item,"get_js")){
				if($js=$item->get_js()){
					return $js;	
				}
			}
				
		}
		
		///se modificó 20180310
		/*
		if($item=$this->get_item($cod,false)){
			if(method_exists($item,"get_js")){
				if($js=$item->get_js()){
					return $js;	
				}
			}
			if(!$replace){
				return false;	
			}
		}else{
			
			$item=new mwmod_mw_data_xml_js($cod);
			if($this->add_sub_item($item)){
				return $item->get_js();	
			}
			
		}
		*/
		
	}
	function _get_subitemByClass_js($cod,$replace=false){
		if(!$cod){
			return false;	
		}
		if($item=$this->get_item($cod,false)){
			if(method_exists($item,"get_js")){
				return $item;	
			}
			if(!$replace){
				return false;	
			}
		}
		$item=new mwmod_mw_data_xml_js($cod);
		if($this->add_sub_item($item)){
			return $item;	
		}
		
	}
	
////////////////
	function arrayItem($cod,$replace=true,$create_non_existing_parent=true){
		//no produce error si no existe, puede devolver un item no vinculado!!!
		if($item=$this->get_array_item($cod,$replace,$create_non_existing_parent)){
			return $item;	
		}
		return new mwmod_mw_data_xml_array($cod); 
	}

	function get_array_item($cod,$replace=false,$create_non_existing_parent=true){
		if(!$cod){
			return false;	
		}
		$a=explode(".",$cod);
		if(sizeof($a)>1){
			$c=array_pop($a);
			$parentcod=implode(".",$a);
			if($parent=$this->get_subitem_from_dot_cod($parentcod,$create_non_existing_parent)){
				return $parent->_get_array_item($c,$replace);
			}
		}else{
			return $this->_get_array_item($cod,$replace);	
		}
		
	}

	function _get_array_item($cod,$replace=false){
		return $this->_get_subitemByClass_array($cod,$replace);
	}
	function _get_subitemByClass_array($cod,$replace=false){
		if(!$cod){
			return false;	
		}
		if($item=$this->get_item($cod,false)){
			if(is_a($item,"mwmod_mw_data_xml_array")){
				return $item;	
			}
			if(!$replace){
				return false;	
			}
		}
		$item=new mwmod_mw_data_xml_array($cod);
		if($this->add_sub_item($item)){
			return $item;	
		}
		
	}




///////////	
	
	
	final function get_item($cod,$create_non_existing=true){
		if(!$create_non_existing){
			return $this->get_existing_item($cod);	
		}
		return $this->_get_item($cod);
	}
	private function _get_item($cod){
		if(!$cod){
			return false;	
		}
		if($this->_items[$cod]??null){
			return $this->_items[$cod];	
		}
		if(!$item=$this->create_sub_item($cod)){
			return false;
		}
		return $this->add_sub_item($item);
	}
	
	function get_margin_tabs(){
		if(!$this->use_tabs){
			return "";	
		}
		if(!$d=$this->get_deep()){
			return "";	
		}
		return str_repeat ( "\t" , $d );
	}
	
	
	function __mw_array_allow_use_this_object(){
		return true;	
	}
	
	function get_prop_from_key_dot($key=false){
		if(!$key){
			return $this->get_props_as_array_or_str();
		}else{
			if($item=$this->get_subitem_from_dot_cod($key,false)){
				return $item->get_props_as_array_or_str();
			}
		}
		return false;
	}

	function get_prop($cod=false){
		return $this->get_prop_from_key_dot($cod);	
	}
	function set_prop($key,$val){
		return $this->set_value_from_dot_cod($key,$val);
	}

	
	
}

?>