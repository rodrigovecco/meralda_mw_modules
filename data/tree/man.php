<?php
class mwmod_mw_data_tree_man extends mw_apsubbaseobj{
	private $filemanager;
	private $mainRootPath;
	private $_path;
	private $datamanagers=array();
	function __construct($path){
		$this->init($path);
	}
	
	function delete_sub_path($subpath){
		if(!$subpath){
			return false;	
		}
		
		if(!$subpath=basename($subpath)){
			return false;	
		}
		
		if(!$subpath=$this->check_path($subpath)){
			return false;	
		}
		if(!$path=$this->get_path()){
			return false;	
		}
		$path.="/".$subpath;
		$fm=$this->get_filemanager();
		$fm->delete_path($path);
		
	}
	
	
	function _create_datamanager($code,$path){
		if(!$fullcode=$this->get_datamanager_fullcode($code,$path)){
			return false;	
		}
		$man= new mwmod_mw_data_tree_item($this,$code,$fullcode,$path);
		return $man;	
	}
	private function _add_file2datamanagers_list(&$fileslist,$file,$subpath=false){
		if(!$file){
			return false;	
		}
		if($file=="."){
			return false;	
		}
		if($file==".."){
			return false;	
		}
		if(!$path=$this->get_path()){
			return false;	
		}
		if($subpath){
			$path.="/".$subpath;	
		}
		//echo $path."/".$file."<br>";
		if(is_dir($path."/".$file)){
			if($list=scandir($path."/".$file)){
				foreach($list as $sfile){
					$this->_add_file2datamanagers_list($fileslist,$sfile,$subpath.$file."/");	
				}
			}
			
			
			return; 	
		}
		if(is_link($path."/".$file)){
			return false;
		}
		if(!is_file($path."/".$file)){
			return false;
		}
		$fm=$this->get_filemanager();
		if(!$ext=$fm->get_ext($file)){
			return false;
		}
		if($ext!==$this->get_fileext()){
			return false;	
		}
		$fcod=$fm->get_filenamenoext($file);
		$fileslist[]=array("cod"=>$fcod,"subpath"=>$subpath);
		
		
		
	}
	function get_all_datamanagers(){
		if(!$path=$this->get_path()){
			return false;	
		}
		
		
		if(!is_dir($path)){
			return false;	
		}
		if(!$list=scandir($path)){
			
			return false;	
		}
		//mw_array2list_echo($list);
		$fileslist=array();
		foreach($list as $file){
			$this->_add_file2datamanagers_list($fileslist,$file);	
		}
		//mw_array2list_echo($fileslist);
		reset($fileslist);
		$r=array();
		foreach($fileslist as $d){
			if($cod=$this->get_datamanager_fullcode($d["cod"],$d["subpath"])){
				if($a=$this->get_datamanager($d["cod"],$d["subpath"])){
					$r[$cod]=$a;
				}
			}
		}
		
		return $r;

	}
	
	function get_datamanager_fullcode(&$code,&$path=false){
		if(!$code=$this->check_path($code)){
			return false;	
		}
		if(strpos($code,"/")!==false){
			return false;
		}
		if(!$code=basename($code)){
			return false;	
		}
		$code=strtolower($code);
		if($path===false){
			return 	$code;
		}
		
		if(!$path=$this->check_path($path)){
			return false;	
		}
		$path=strtolower($path);
		return $path."/".$code;
		
			
	}
	final function get_datamanager($code="data",$path=false){
		
		if(!$fullcode=$this->get_datamanager_fullcode($code,$path)){
			return false;	
		}
		if($this->datamanagers[$fullcode]??null){
			return $this->datamanagers[$fullcode];	
		}
		if(!$sm=$this->_create_datamanager($code,$path)){
			return false;	
		}
		$this->datamanagers[$fullcode]=$sm;
		return $this->datamanagers[$fullcode];	
		
	}
	final function init($path){
		$this->set_mainap();
		$this->_set_path($path);	
		
	}
	final function get_sub_path(){
		return 	$this->_path;
	}
	final function get_path(){
		if(!$m=$this->get_main_root_path()){
			return false;	
		}
		if(!$p=$this->get_sub_path()){
			return false;	
		}
		return $m."/".$p;
		
	}
	private function _set_path($path){
		if(!$path=$this->check_path($path)){
			return false;	
		}
		$this->_path=$path;
		return true;
	}
	final function __get_priv_mainRootPath(){
		if(isset($this->mainRootPath)){
			return $this->mainRootPath;
		}
	}
	final function setMainRootPath($path){
		$this->mainRootPath=$path;
	}
	function get_main_root_path(){
		if($p=$this->__get_priv_mainRootPath()){
			return $p;
		}
		return $this->mainap->get_path("userfiles");	
	}
	
	function check_path($path){
		if(!$path){
			return false;	
		}
		if(!is_string($path)){
			return false;	
		}
		if(strpos($path," ")!==false){
			return false;	
		}
		$path=str_replace("\\","/",$path);
		$path=str_replace("//","/",$path);
		$path=str_replace("//","/",$path);
		$path=str_replace("//","/",$path);
		$path=trim($path,"/");
		if(!$path){
			return false;	
		}
		if($path=="/"){
			return false;	
		}
		if(strpos($path,".")!==false){
			return false;	
		}
		return $path;

			
	}


	function get_fileext(){
		return "mwtreedata";
	}
	final function get_filemanager(){
		if(!isset($this->filemanager)){
			$this->filemanager=$this->mainap->get_submanager("fileman");
		}
		return $this->filemanager;
	}
	

}


?>