<?php
class mwmod_mw_data_str_man extends mw_apsubbaseobj{
	private $filemanager;
	private $_path;
	private $datamanagers=array();
	function __construct($path){
		$this->init($path);
	}
	
	private function _create_datamanager($code,$path){
		if(!$fullcode=$this->get_datamanager_fullcode($code,$path)){
			return false;	
		}
		$man= new mwmod_mw_data_str_item($this,$code,$fullcode,$path);
		return $man;	
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
	function get_main_root_path(){
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
		return "txt";
	}
	final function get_filemanager(){
		if(!isset($this->filemanager)){
			$this->filemanager=$this->mainap->get_submanager("fileman");
		}
		return $this->filemanager;
	}
	
	

}


?>