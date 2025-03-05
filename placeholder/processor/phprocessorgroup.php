<?php
class mwmod_mw_placeholder_processor_phprocessorgroup extends mw_apsubbaseobj{
	private $_items=array();
	private $ph_src;//
	
	
	
	
	function __construct(){
	}
	
	function get_debug_data_for_mail(){
		$r=array();
		if($items=$this->get_items()){
			$r["items"]=array();
			foreach($items as $cod=>$item){
				$r["items"][$cod]=$item->get_debug_data_for_mail();	
			}
		}
		return $r;
	}
	
	function get_debug_data(){
		$r=array();
		$r["class"]=get_class($this);
		if($items=$this->get_items()){
			$r["items"]=array();
			foreach($items as $cod=>$item){
				$r["items"][$cod]=$item->get_debug_data();	
			}
		}
		return $r;
	}
	function new_item($cod){
		$item= new mwmod_mw_placeholder_processor_phprocessor($cod);
		return $this->add_item($item);
	}
	function get_text_final($cod){
		if($item=$this->get_item($cod)){
			return $item->get_text_final();	
		}
		return "";
	}
	final function get_items(){
		return $this->_items;
	}
	
	final function get_item($cod){
		if(!$cod){
			return false;	
		}
		return $this->_items[$cod]??null;
	}
	final function add_item($item){
		$cod=$item->cod;
		$this->_items[$cod]=$item;
		if($src=$this->get_or_create_ph_src()){
			$item->set_ph_src($src);	
		}
		return $item;
	}
	
	function add_ph_sub_src($src){
		if($root=$this->get_or_create_ph_src()){
			return $root->add_item_by_cod($src);	
		}
	}
	function get_or_create_ph_src(){
		if($this->ph_src){
			return $this->ph_src;	
		}
		$src=new mwmod_mw_placeholder_src_root();
		return $this->set_ph_src($src);
	}
	
	final function set_ph_src($src){
		$this->reset_proc();
		$this->ph_src=$src;
		return $this->ph_src;
			
	}
	final function reset_proc(){
		//unset($this->_txt);	
	}
	final function __get_priv_ph_src(){
		return $this->ph_src; 	
	}

	/*
	final function get_debug_info($cod=false){
		if($cod){
			return mw_array_get_sub_key($this->_debug_info,$cod);
		}
		return $this->_debug_info;
	}
	final function reset_proc(){
		$this->_original_txt="";
		unset($this->_txt_elems);
			
	}
	final function get_original_txt(){
		return $this->_original_txt;	
	}
	final function set_text($txt=false){
		$this->reset_proc();
		if($txt){
			$txt=$txt."";
			if(is_string($txt)){
				$this->_original_txt=$txt;
				return true;
			}
		}
		
		
	}
	*/
	
}
?>