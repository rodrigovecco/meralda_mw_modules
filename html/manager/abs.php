<?php
//para manejadores de javascripts, css, etc
abstract class mwmod_mw_html_manager_abs extends mw_apsubbaseobj{
	private $_items=array();
	private $_new_items=array();
	private $def_path="/";
	final function init_man(){
		$this->set_mainap();
	}
	function isIE8orLower(){
		$s=$_SERVER['HTTP_USER_AGENT'];
		$pos=strpos($s,"MSIE");
		if($pos===false){
			return false;
		}
		$rest=substr($s,$pos+4)."";
		$parts=explode(" ",$rest);
		$v=$parts[0]+0;
		if($v<9){
			return true;	
		}
		return false;
	
	}
	
	function declare_new_items($echo=false){
		$html=$this->get_new_items_declaration();
		$this->unset_new_items();
		if($echo){
			echo $html;	
		}
		return $html;
	}
	function get_new_items_declaration(){
		$html="";
		if($items=$this->get_new_items_for_top()){
			foreach($items as $item){
				$html.=$item->get_html_declaration();	
			}
		}
		return $html;
	}
	function get_bottom_items_declaration(){
		$html="";
		if($items=$this->get_items_for_bottom()){
			foreach($items as $item){
				$html.=$item->get_html_declaration();	
			}
		}
		return $html;
	}
	
	function get_items_for_bottom(){
		$r=array();
		if($items=$this->get_items()){
			foreach($items as $cod=>$item){
				if($item->is_bottom()){
					$r[$cod]=$item;	
				}
			}
		}
		return $r;
			
	}
	
	function get_new_items_for_top(){
		$r=array();
		if($items=$this->get_new_items()){
			foreach($items as $cod=>$item){
				if(!$item->is_bottom()){
					$r[$cod]=$item;	
				}
			}
		}
		return $r;
			
	}
	final function get_new_items(){
		return $this->_new_items;	
	}
	final function unset_new_items(){
		$this->_new_items=array();	
	}

	
	
	final function set_def_path($val){
		$this->def_path=$val;
	}
	function create_item($cod){
		return false;	
	}
	function add_item_by_cod_def_path($cod){
		if(!$cod=$this->check_str_key($cod)){
			return false;	
		}
		$cod=$this->def_path.$cod;
		return $this->add_item_by_cod($cod);
	
	}
	function add_item_by_cod($cod){
		if(!$cod=$this->check_str_key($cod)){
			return false;	
		}
		if($item=$this->get_item($cod)){
			return $item;	
		}
		if($item=$this->create_item($cod)){
			return $this->add_item($cod,$item);
			
		}
	
	
	}
	final function get_items(){
		return $this->_items;	
	}
	final function remove_item($cod){
		if(!$cod=$this->check_str_key($cod)){
			return false;	
		}
		if($this->_items[$cod]){
			unset($this->_items[$cod]);	
			unset($this->_new_items[$cod]);	
			return true;
		}
	
	}
	function add_item_by_item($item){
		$cod=$item->cod;
		return $this->add_item($cod,$item);

	}
	function item_exists($cod){
		return $this->get_item($cod);
	}
	final function add_item($cod,$item){
		if(!$cod=$this->check_str_key($cod)){
			return false;	
		}
		if(isset($this->_items[$cod])and $this->_items[$cod]){
			return 	$this->_items[$cod];
		}
		
		$this->_items[$cod]=$item;
		$this->_new_items[$cod]=$item;
		$item->setMan($this);
		
		return $item;
	}
	final function get_item($cod){
		if(!$cod=$this->check_str_key($cod)){
			return false;	
		}
		if(isset($this->_items[$cod])){
			return $this->_items[$cod];
		}
		
	}
	final function __get_priv_def_path(){
		return $this->def_path;	
	}
}
?>