<?php
abstract class  mwmod_mw_lng_msg_lngmanabs extends mw_apsubbaseobj{
	private $lngcod;
	private $man;//mwmod_mw_lng_msg_man
	private $_items;
	private $_path;
	
	function __construct($lngcod,$man){
		$this->init($lngcod,$man);	
	}
	function create_item($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		$item=new mwmod_mw_lng_msg_item($cod,$this);
		return $item;	
	}
	function create_item_by_line($str){
		if(!$str=trim($str)){
			return false;	
		}
		$a=explode(" ",$str,2);
		if(!$cod=trim($a[0]??"")){
			return false;
		}
		//todo unescape
		if(!$msg=trim($a[1]??"")){
			return false;
		}
		if(!$item=$this->create_item($cod)){
			return false;
		}
		$item->set_msg($msg);
		return $item;
		
	}
	function load_items(){
		if(!$p=$this->get_path()){
			return false;	
		}
		if(!$f=$this->get_file_name()){
			return false;	
		}
		if(!is_dir($p)){
			return false;
		}
		$r=array();
		if(file_exists($p."/".$f)){
			if(!$fh = fopen($p."/".$f, "r")){
				return false;		
			}
			while (($buffer = fgets($fh, 4096)) !== false) {
				if($item=$this->create_item_by_line($buffer)){
					$r[]=$item;
				}
			}
			fclose($fh);
		}
		return $r;
	}
	function get_file_name(){
		return $this->man->cod.".txt";	
	}
	final function get_path(){
		if(isset($this->_path)){
			return $this->_path;	
		}
		if(!$p=$this->mainap->get_sub_path("lngmsgs/".$this->lngcod."","system")){
			return false;	
		}
		$this->_path=$p;
		return $this->_path;	
	}

	
	function re_write_msgs_if_needed(){
			
	}
	final function add_item($item){
		$cod=$item->cod;
		$this->_items[$cod]=$item;
	}
	final function get_item($cod){
		if(!$cod){
			return false;	
		}
		$this->init_items();
		if(isset($this->_items[$cod])){
			if($this->_items[$cod]){
				return 	$this->_items[$cod];
			}
		}
		
		
	}
	final function get_items(){
		$this->init_items();
		return $this->_items;
	}
	final function init_items(){
		if(isset($this->_items)){
			return;	
		}
		$this->_items=array();
		if($items=$this->load_items()){
			foreach($items as $item){
				$cod=$item->cod;
				$this->_items[$cod]=$item;	
			}
		}
	}
	final function __get_priv_lngcod(){
		return $this->lngcod; 	
	}
	final function __get_priv_man(){
		return $this->man; 	
	}

	final function init($lngcod,$man){
		$ap=$man->mainap;
		$this->man=$man;
		$this->lngcod=basename($lngcod);
		$this->set_mainap($ap);	
		
	}

}
?>