<?php
abstract class mwmod_mw_modulesman_dirman_abs extends mw_apsubbaseobj{
	private $cod;
	private $mainman;
	private $subpath;
	private $mode;
	private $pathman;
	private $_info_mans;
	final function get_info_managers(){
		$this->init_info_mans();
		return $this->_info_mans;
	}
	function load_info_mans(){
		if(!$files=$this->get_info_files()){
			return false;	
		}
		//mw_array2list_echo($files);
		$r=array();
		foreach($files as $file){
			if($item=$this->create_info_man_from_info_file($file)){
				$r[$item->cod]=$item;	
			}
		}
		return $r;
	}
	function create_info_man_from_info_file($subpathfile){
		if(!$subpathfile=trim($subpathfile,"/")){
			return false;
		}
		
		if(!$base=basename($subpathfile)){
			return false;
		}
		if(!$dir=dirname($subpathfile)){
			return false;
		}
		if($base=="dir.mwinfo"){
			return $this->create_dir_info_man($dir);
		}
		if(!$filename=$this->mainman->helper->file_man->get_filenamenoext($base)){
			return false;	
		}
		return $this->create_file_info_man($dir,$filename);
	}
	function get_info_files(){
		$list=array();
		if(!$p=$this->get_base_path()){
			return false;	
		}
		$this->add2infofileslist($p,false,$list);
		return $list;
			
	}
	function add2infofileslist($dirbase,$dir,&$list){
		$dirfull=$dirbase;
		if($dir){
			$dirfull.="/".$dir;	
		}
		if (!is_dir($dirfull)){
			return false;	
		}
		if(!$files=scandir($dirfull)){
			return false;	
		}
		foreach($files as $file){
			if (is_dir($dirfull."/".$file)) {
				if(($file!=".")&&($file!="..")){
					$this->add2infofileslist($dirbase,$dir."/".$file,$list);	
				}
			}else{
				$ext=$this->mainman->helper->file_man->get_ext($file);
				if($ext=="mwinfo"){
					$list[]=$dir."/".$file;	
				}
			}
		}

	}
	final function init_info_mans(){
		if(isset($this->_info_mans)){
			return;	
		}
		$this->_info_mans=array();
		if($items=$this->load_info_mans()){
			foreach($items as $item){
				$this->_info_mans[$item->cod]=$item;	
			}
		}
	}
	function get_debug_data(){
		$r=array();
		$r["cod"]=$this->cod;	
		$r["subpath"]=$this->subpath;	
		$r["class"]=get_class($this);
		return $r;	
	}
	function get_cod(){
		return $this->cod;	
	}
	function get_name(){
		return $this->mode." - ".$this->subpath;	
	}
	function get_base_path(){
		if(!$this->mode){
			return false;	
		}
		if(!$this->subpath){
			return false;	
		}
		return $this->mainap->get_sub_path($this->subpath,$this->mode);
			
	}
	function get_info_file_path($subpath){
		if(!$subpath){
			return false;	
		}
		if(!$this->mode){
			return false;	
		}
		if(!$this->subpath){
			return false;	
		}
		return $this->mainap->get_sub_path($this->subpath."/".$subpath,$this->mode);
	}
	function create_file_info_man_for_full_path($subpath){
		if(!$subpath){
			return false;	
		}
		$p=dirname($subpath);
		$f=basename($subpath);
		return $this->create_file_info_man($p,$f);
	}
	
	function create_file_info_man($subpath,$file){
		$man=new mwmod_mw_modulesman_infoman_file($subpath,$file,$this);
		return $man;	
	}
	function create_dir_info_man($subpath){
		$man=new mwmod_mw_modulesman_infoman_dir($subpath,$this);
		return $man;	
	}
	final function __get_priv_cod(){
		return $this->cod; 	
	}
	final function __get_priv_mainman(){
		return $this->mainman; 	
	}
	final function __get_priv_subpath(){
		return $this->subpath; 	
	}
	final function __get_priv_mode(){
		return $this->mode; 	
	}
	final function set_main_man($man){
		$this->mainman=$man;
	}

	final function init($cod,$subpath,$mode,$ap=false){
		$this->cod=$cod;
		$this->subpath=$subpath;
		$this->mode=$mode;
		$this->set_mainap($ap);
	}

	
}
?>