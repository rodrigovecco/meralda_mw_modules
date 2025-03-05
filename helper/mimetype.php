<?php
//to use, get it from mwmod_mw_ap_helper
class mwmod_mw_helper_mimetype extends mw_apsubbaseobj{
	private $_mime_types;
	private $_mime_types_full_list;
	function __construct(){
		$this->set_mainap();
	}
	function get_mime_types($from_full=false){
		if($from_full){
			return $this->get_mime_types_from_full_list();	
		}
		return $this->get_mime_types_from_short_list();
	}
	function get_ext_mime_type($cod,$allowfromfulllist=true){
		if(!$cod){
			return false;	
		}
		if($r=$this->get_ext_mime_type_from_short_list($cod)){
			return $r;	
		}
		if($allowfromfulllist){
			return $this->get_ext_mime_type_from_full_list($cod);	
		}
	}

	final function get_mime_types_from_full_list(){
		$this->init_mime_types_full();
		return $this->_mime_types_full_list;
	}
	final function get_ext_mime_type_from_full_list($cod){
		if(!$cod){
			return false;	
		}
		$this->init_mime_types_full();
		return $this->_mime_types_full_list[$cod];
	}
	function load_mime_types_full(){
		if(!$pathman=$this->mainap->get_path_man("system")){
			return false;	
		}
		$file=$pathman->get_file_path_if_exists("fulllist.txt","data/mimetypes");
		return $this->get_mime_types_from_file($file);
		
	}
	
	final function init_mime_types_full(){
		if(isset($this->_mime_types_full_list)){
			return;	
		}
		$this->_mime_types_full_list=array();
		if($l=$this->load_mime_types_full()){
			if(is_array($l)){
				$this->_mime_types_full_list=$l;	
			}
		}
	}

	
	final function get_mime_types_from_short_list(){
		$this->init_mime_types();
		return $this->_mime_types;
	}
	final function get_ext_mime_type_from_short_list($cod){
		if(!$cod){
			return false;	
		}
		$this->init_mime_types();
		return $this->_mime_types[$cod];
	}
	function load_mime_types(){
		if(!$pathman=$this->mainap->get_path_man("system")){
			return false;	
		}
		$file=$pathman->get_file_path_if_exists("shortlist.txt","data/mimetypes");
		return $this->get_mime_types_from_file($file);
		
	}
	
	final function init_mime_types(){
		if(isset($this->_mime_types)){
			return;	
		}
		$this->_mime_types=array();
		if($l=$this->load_mime_types()){
			if(is_array($l)){
				$this->_mime_types=$l;	
			}
		}
	}
	
	function get_mime_types_list($ext_list,&$fail_list=false,$allowfromfulllist=true){
		
		if(!$ext_list){
			return false;	
		}
		
		if(!is_array($ext_list)){
			$e=$ext_list;
			$ext_list=explode(",",$e);	
		}
		if(!is_array($ext_list)){
			return false;	
		}
		$log_fail=false;
		if(is_array($fail_list)){
			$log_fail=true;
		}
		
		$r=array();
		foreach($ext_list as $cod){
			if($type=$this->get_ext_mime_type($cod,$allowfromfulllist)){
				$r[$cod]=$type;	
			}else{
				if($log_fail){
					$fail_list[$cod]=$cod;	
				}
			}
		}
		return $r;
	}
	function get_mime_types_from_file($file){
		if(!$file){
			return false;
		}
		if(!$handle = fopen($file, "r")){
			return false;	
		}
		$r=array();
		while (($line = fgets($handle)) !== false) {
			if($line=trim($line)){
				$a=explode(" ",$line,2);
				if($cod=trim($a[0])){
					if($type=trim($a[1])){
						$cod=strtolower($cod);
						$r[$cod]=$type;	
					}
				}
					
			}
		}

		fclose($handle);
		return $r;
		
	}
	
	
}
?>