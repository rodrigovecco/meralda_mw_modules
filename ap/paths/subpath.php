<?php
//rvh 2023-02-26 v 2
class  mwmod_mw_ap_paths_subpath extends mw_apsubbaseobj{
	private $pathman;//mwmod_mw_ap_paths
	private $dir;//sub dir de $pathman
	private $_is_ok;
	function __construct($dir,$pathman){
		$this->init($dir,$pathman);
		
	}
	function openNewFile($filename,$subpath=false,$mode="a"){
		//does not delete, if exists return false
		//creates path
		if(!$filename=$this->checkFileName($filename)){
			return false;	
		}
		if($existing=$this->fileOrDir_exists($filename,$subpath)){
			return false;	
		}
		if(!$p=$this->check_and_create_path($subpath)){
			return false;	
		}
		$full=$p."/".$filename;
		$myFile= fopen($full,$mode); // Open the file for writing
		return $myFile;
		
		
			
	}
	function download_file($filename,$subpath=false,$fakename=false){
		if(!$f=$this->file_exists($filename,$subpath)){
			return false;	
		}
		if(!$fakename){
			$fakename=basename($filename);	
		}
		ob_end_clean();
		$download_rate = 20.5;
		header('Cache-control: private');
		header('Content-Type: application/octet-stream');
		header('Content-Length: '.filesize($f));
		header('Content-Disposition: filename='.$fakename);
		flush();
		$file = fopen($f, "r");
		while(!feof($file)){
			print fread($file, round($download_rate * 1024));
			flush();
			//sleep(1);
		}
		fclose($file);
		return $f;
		
	
	}
	function get_file_path_if_exists($filename,$subpath=false){
		if($full=$this->get_full_path_filename($filename,$subpath)){
			if(file_exists($full)){
				if(is_file($full)){
					return realpath($full);
				}
			}
		}
	}
	function get_full_path_filename($filename,$subpath=false){
		if(!$filename=$this->checkFileName($filename)){
			return false;	
		}
		if(!$p=$this->get_sub_path($subpath)){
			return false;
		}
		return $p."/".$filename;
	}
	function checkFileName($filename){
		if(!$filename=trim($filename)){
			return false;	
		}
		
		if(strpos($filename,"/")!==false){
			return false;	
		}
		if(strpos($filename,"\\")!==false){
			return false;	
		}
		return $filename;

	}
	function fileOrDir_exists($filename,$subpath=false){
		if(!$p=$this->get_sub_path($subpath)){
			return false;	
		}
		if(file_exists($p."/".$filename)){
			return $p."/".$filename;	
		}
	}
	
	function file_exists($filename,$subpath=false){
		$filename=basename($filename);
		if(!$p=$this->get_sub_path($subpath)){
			return false;	
		}
		if(is_file($p."/".$filename)){
			if(file_exists($p."/".$filename)){
				
				return $p."/".$filename;	
			}
		}
	}
	function delete(){
		if(!$path=$this->get_path()){
			return false;	
		}
		if(!$fm=$this->get_file_man()){
			return false;	
		}
		return $fm->delete_path($path);
	}

	
	function check_and_create_path($subpath=false){
		if(!$p=$this->get_sub_path($subpath)){
			return false;	
		}
		if($fm=$this->get_file_man()){
			return $fm->check_and_create_path($p);	
		}
		return false;
	}
	function get_file_man(){
		return $this->pathman->get_file_man();	
	}
	
	function get_rel_sub_path($subpath=false){
		if(!$dir=$this->get_rel_dir()){
			return false;	
		}
		if($subpath===false){
			return $dir;	
		}
		if($subpath=$this->pathman->check_sub_path($subpath)){
			return $dir."/".$subpath;
		}
		return false;	
	}
	function get_sub_path($subpath=false){
		if($p=$this->get_rel_sub_path($subpath)){
			return $this->pathman->get_sub_path($p);	
		}
		return false;
		
	}

	function get_path(){
		return $this->get_sub_path();
	}
	function get_debug_data(){
		$r=array(
			"get_rel_dir"=>$this->get_rel_dir(),
			"get_rel_sub_path"=>$this->get_rel_sub_path(),
			"get_rel_sub_path_test_hello"=>$this->get_rel_sub_path("test/hello"),
			"get_sub_path"=>$this->get_sub_path(),
			"get_sub_path_test_hello"=>$this->get_sub_path("test/hello"),
			"getSiteRootRelPath"=>$this->getSiteRootRelPath(),
			"getSiteRootRelPath_test_hello"=>$this->getSiteRootRelPath("test/hello"),
			
			
			"mode"=>$this->pathman->mode
			
		);
		return $r;
	}
	function getSiteRootRelPath($subpath=false){
		if(!$sub=$this->get_rel_sub_path($subpath)){
			return false;	
		}
		return $this->pathman->getSiteRootRelSubPath($sub);
	
	}

	function get_sub_path_man($subpath){
		if(!$subpath){
			return false;	
		}
		if(!$sub=$this->get_rel_sub_path($subpath)){
			return false;	
		}
		return $this->pathman->get_sub_path_man($sub);
	}
	
	final function get_rel_dir(){
		if(!$this->check()){
			return false;	
		}
		return $this->dir;
		
	}
	final function init($dir,$pathman){
		$ap=$pathman->mainap;
		$this->set_mainap($ap);
		$this->pathman=$pathman;	
		$this->dir=$dir;	
	}
	final function check(){
		if(isset($this->_is_ok)){
			return $this->_is_ok;	
		}
		$this->_is_ok=false;
		if($p=$this->pathman->check_sub_path($this->dir)){
			$this->dir=$p;
			$this->_is_ok=true;	
		}
		return $this->_is_ok;

	}
	final function __get_priv_dir(){
		return $this->dir; 	
	}
	final function __get_priv_pathman(){
		return $this->pathman; 	
	}
	
}

?>