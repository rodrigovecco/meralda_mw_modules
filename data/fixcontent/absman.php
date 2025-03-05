<?php
abstract class mwmod_mw_data_fixcontent_absman extends mw_apsubbaseobj{
	private $pathMode;
	private $relPath;
	private $pathMan;
	private $_submans=array();
	private $cod="_main";
	private $parentMan;
	
	public $showFilePath=false;
	
	final function init_fix_content_man($relPath="content",$pathMode="instance"){
		$this->setPathMode($pathMode);
		$this->setRelPath($relPath);
	
	}
	function newContentItem($file){
		return new mwmod_mw_data_fixcontent_item($file,$this);
	}
	
	function getFullPath($subpath=false){
		return $this->__get_priv_pathMan()->get_sub_path($subpath);	
	}
	function showContentPathEnabled(){
		return $this->showFilePath;	
	}
	function getContentHTML($file){
		$r="";
		if($this->showContentPathEnabled()){
			$r.=$this->getHTMLPathComment($file);	
		}
		
		if($file=$this->getFileFullPath($file,true)){
			//""
			$r.=file_get_contents($file);	
		}
		return $r;
	}
	function getHTMLPathComment($file){
		$p=$this->getSiteRootRelPath($file);
		return "\n<!--{$p}-->\n";
	}
	function getSiteRootRelPath($relFileNameAndPath=false){
		
		$filename="";
		$subpath="";
		$this->getFilePathInfo($relFileNameAndPath,$filename,$subpath);
		if(!$pm=$this->__get_priv_pathMan()){
			return false;	
		}
		$p=$this->__get_priv_pathMan()->getSiteRootRelPath($subpath);
		if($filename){
			$p.="/".$filename;	
		}
		return $p;
	}
	function getContent($file){
		if($file=$this->getFileFullPath($file,true)){
			return file_get_contents($file);	
		}
		return "";
	}
	function setContent($relFileNameAndPath,$content,$rewrite=false){
		$filename="";
		$subpath="";
		$this->getFilePathInfo($relFileNameAndPath,$filename,$subpath);
		if(!$filename){
			return false;	
		}
		//echo $filename." f<br>";
		//echo $subpath." sp<br>";
		if($existing=$this->__get_priv_pathMan()->file_exists($filename,$subpath)){
			//echo $existing."  ex<br>";
			if(!$rewrite){
				return false;	
			}
			unlink($existing);
		}
		if(!$myFile=$this->__get_priv_pathMan()->openNewFile($filename,$subpath)){
			return false;
		}
		fputs($myFile, $content.""); // Write the data ($string) to the text file
		fclose($myFile); // Closing the file after writing data to it
		return true;
	}
	function getFilePathInfo($relFileNameAndPath,&$filename,&$subpath){
		
		//se podra usar en otros
		$filename=false;
		$subpath=false;
		if(!$relFileNameAndPath){
			return false;	
		}
			
		$issub=false;
		if(strpos($relFileNameAndPath,"/")!==false){
			$issub=true;
		}
		if(strpos($relFileNameAndPath,"\\")!==false){
			$issub=true;
		}
		if($issub){
			$filename=basename($relFileNameAndPath);
			$subpath=trim(dirname($relFileNameAndPath),"/\\");
				
		}else{
			$filename=basename($relFileNameAndPath);
			$subpath=false;
		}
		$r=array(
			"filename"=>$filename,
			"subpath"=>$subpath,
		);
		return $r;
		
	}
	
	function getFileFullPath($relFileNameAndPath,$checkExists=true){
		
		$filename="";
		$subpath="";
		$this->getFilePathInfo($relFileNameAndPath,$filename,$subpath);
		
		
		
		if($checkExists){
			return $this->__get_priv_pathMan()->file_exists($filename,$subpath);	
		}else{
			return $this->__get_priv_pathMan()->get_sub_path($subpath)."/".$filename;	
		}
	}
	
	
	
	final function init_fix_content_subman($subRelPath,$parentMan){
		$this->setParentMan($parentMan);
		$this->setCod($subRelPath);
		$this->setPathMode($parentMan->pathMode);
		$this->setRelPath($parentMan->getStrRelPath($subRelPath));
		
		
	}
	
	final function setParentMan($parentMan){
		$this->parentMan=$parentMan;
	}
	final function __get_priv_parentMan(){
		return $this->parentMan;
	}
	final function setCod($cod){
		$this->cod=$cod;
	}
	final function __get_priv_cod(){
		return $this->cod;
	}
	function getSubMan($path){
		return $this->getSubManOrCreate($path);
	}
	final function getExistingSubMan($path){
		if(!$path){
			return false;	
		}
		if($this->_submans[$path]??null){
			return $this->_submans[$path];	
		}
		if(!$path1=$this->check_sub_path($path)){
			return false;	
		}
		if($path==$path1){
			return false;	
		}
		return $this->_submans[$path1];	
	}
	final function getSubManOrCreate($path){
		if($sub=$this->getExistingSubMan($path)){
			return $sub;	
		}
		if($sub=$this->createSubMan($path)){
			return $this->addSubMan($sub);	
		}
		
		
	}
	final function addSubMan($subMan){
		if(!$cod=$subMan->cod){
			return false;	
		}
		if($this->_submans[$cod]??null){
			return false;	
		}
		$this->_submans[$cod]=$subMan;
		return $subMan;
	}
	final function getSubMans(){
		return $this->_submans;	
	}
	function createSubMan($path){
		if(!$path=$this->check_sub_path($path)){
			return false;
		}
		$sub=new mwmod_mw_data_fixcontent_sub($path,$this);
		return $sub;
		
		
	}
	
	
	
	
	
	final function setPathMode($pathMode="instance"){
		if(!$pathMode){
			$pathMode="instance";
		}
			
		$this->pathMode=$pathMode;
	}
	
	function create_pathMan(){
		if(!$p=$this->getStrRelPath()){
			return false;	
		}
		return $this->mainap->get_sub_path_man($p,$this->pathMode);
	}
	function check_sub_path($subpath){
		return $this->__get_priv_pathMan()->pathman->check_sub_path($subpath);	
	}
	final function __get_priv_pathMan(){
		if(!isset($this->pathMan)){
			if(!$this->pathMan=$this->create_pathMan()){
				$this->pathMan=false;	
			}
		}
		return $this->pathMan;
	}
	
	
	final function setRelPath($str){
		$this->relPath=$str;
	}
	
	
	final function getStrRelPath($sub=false){
		if(!$this->relPath){
			return false;	
		}
		$r=$this->relPath;
		if($sub){
			$r.="/".$sub;	
		}
		return $r;
	}
	final function __get_priv_relPath(){
		return $this->relPath; 	
	}
	
	function get_debug_data(){
		$r=array();
		$r["cod"]=$this->cod;
		$r["relpath"]=$this->getStrRelPath();
		$r["relpath1"]=$this->getStrRelPath("xxx");
		$r["path"]=$this->getFullPath();
		$r["pathxxx"]=$this->getFullPath("xxx");
		$r["file"]=$this->getFileFullPath("hello.html",false);
		$r["file1"]=$this->getFileFullPath("sub/hello.html",false);
		$r["file2"]=$this->getFileFullPath("sub/hello.html",true);
		$r["file3"]=$this->getFileFullPath("hello.html",true);
		if($this->pathMan){
			$r["path_man"]=$this->pathMan->get_debug_data();	
		}
		if($items=$this->getSubMans()){
			$r["submans"]=array();
			foreach($items as $id=>$item){
				$r["submans"][$id]=$item->get_debug_data();	
			}
		}
		
		
		return $r;
		
	}
	
	
	
}

?>