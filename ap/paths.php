<?php
//rvh 2015-01-12 v 1
class  mwmod_mw_ap_paths extends mw_apsubbaseobj{
	private $mode;
	
	private $_file_man;
	
	private $_siteRootRelPath;
	function __construct($ap,$mode){
		$this->init($ap,$mode);
	}
	function getSiteRootRelSubPath($subpath=false){
		$p=$this->getSiteRootRelPath();
		if($subpath){
			$p.="/".$subpath;	
		}
		return $p;
	}

	function loadSiteRootRelPath(){
		if(!$root=$this->mainap->get_path("root")){
			return false;	
		}
		$rootlen=strlen($root);
		$p=$this->get_path();
		if(!$sub=substr($p,0,$rootlen)){
			return false;
		}
		$sp=trim(substr($p,$rootlen)."","/");
		return $sp."";
		
		
	}
	
	final function getSiteRootRelPath(){
		if(!isset($this->_siteRootRelPath)){
			$this->_siteRootRelPath=$this->loadSiteRootRelPath()."";
		}
		return $this->_siteRootRelPath;
	}
	
	function get_file_path_if_exists($filename,$subpath){
		return $this->mainap->get_file_path_if_exists($filename,$subpath,$this->mode);
	}
	function get_file_path($filename,$subpath){
		return $this->mainap->get_file_path($filename,$subpath,$this->mode);
			
	}
	function get_sub_path($subpath){
		return $this->mainap->get_sub_path($subpath,$this->mode);
		
	}

	function get_path(){
		return $this->mainap->get_path($this->mode);
	}
	function get_sub_path_man($subpath){
		if(!$dir=$this->check_sub_path($subpath)){
			return false;	
		}
		$m=new mwmod_mw_ap_paths_subpath($dir,$this);
		return $m;
	}
	function check_sub_path($subpath){
		if(!$subpath){
			return false;	
		}
		if(!is_string($subpath)){
			return false;	
		}
		$subpath=trim($subpath);
		$subpath=trim($subpath,"/");
		$subpath=trim($subpath);
		
		if(!$subpath){
			return false;	
		}
		if(strpos($subpath,".")!==false){
			return false;	
		}
		return $subpath;
		
	}
	final function get_file_man(){
		if(isset($this->_file_man)){
			return $this->_file_man;	
		}
		$this->_file_man=false;
		if($fm=$this->mainap->get_submanager("fileman")){
			$this->_file_man=$fm;
		}
		return $this->_file_man;	
	
	}
	function get_debug_info(){
		$r=array();
		$r["mode"]=$this->__get_priv_mode();
		$r["path"]=$this->get_path();
		$r["siteRootRel"]=$this->getSiteRootRelPath();
		$sub=$this->get_sub_path_man("test");
		$r["test_sub_man"]=$sub->get_debug_data();
		return $r;
	}

	
	
	final function init($ap,$mode){
		$this->set_mainap($ap);
		$this->mode=$mode;	
	}
		
	final function __get_priv_mode(){
		return $this->mode; 	
	}
	
}

?>