<?php
abstract class  mwmod_mw_manager_subitemfree extends mwmod_mw_manager_itemabs{
	//puede ser inicializado sin su parent, pero lo requiere para allow y rutas
	//falta subir
	private $parent_item;//debe ser un mwmod_mw_manager_item
	private $itempath="subitem";
	final function __get_parent_item(){
		return $this->parent_item; 	
	}
	function allow_admin(){
		if($this->parent_item){
			return 	$this->parent_item->allow_admin();
		}
		return $this->man->allow_admin();
	}
	
	function get_public_url_path(){
		if(!$this->parent_item){
			return false;	
		}
		if(!$p=$this->parent_item->get_public_url_path()){
			return false;	
		}
		if(!$sp=$this->itempath){
			return false;	
		}
	
		return $p."/$sp/".$this->get_id()."/";
		
	}
	
	final function __get_items_path(){
		if(!$this->parent_item){
			return false;	
		}
		
		if(!$p=$this->parent_item->__get_items_path()){
			return false;	
		}
		if(!$sp=$this->itempath){
			return false;	
		}
	
		return $p."/$sp/".$this->get_id()."/";
	}
	final function __get_priv_parent_item(){
		return $this->parent_item; 	
	}
	final function __get_priv_itempath(){
		return $this->itempath; 	
	}

	final function set_parent($item){
		$this->parent_item=$item;	
	}
	final function set_subpath($itempath){
		if($p=$this->check_str_key_alnum_underscore($itempath)){
			$this->itempath=$p;
		}
		
	}

}
?>