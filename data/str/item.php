<?php
class mwmod_mw_data_str_item extends mw_apsubbaseobj{
	//sólo debe ser creado por mw_baseobjects_strdata
	private $mainman;
	private $path;
	private $code;
	private $fullcode;
	private $data; //falta data procesada para placeholders
	private $_dataloaded;
	
	var $is_new;
	
	function __construct($mainman,$code,$fullcode,$path){
		$this->init($mainman,$code,$fullcode,$path);	
	}
	function isNew(){
		if(isset($this->is_new)){
			return 	$this->is_new;
		}
		if($this->get_file_full_path_if_exists()){
			return false;	
		}
		return true;
	}
	function set_data_and_save($data,$stripslashes=true){
		$this->set_data($data,$stripslashes);
		$this->save();
	}
	final function _get_data(){
		if(!is_string($this->data)){
			return "";	
		}
		return $this->data;
	
	}
	function get_procdata(){
		return $this->get_data();	
	}
	function save(){
		$this->delete_file();
		if(!$p=$this->get_and_create_path()){
			return false;	
		}
		if(!$f=$this->get_file_full_path()){
			return false;
		}
		$string=$this->_get_data();
		$myFile= fopen($f,'a'); // Open the file for writing
		fputs($myFile, $string); // Write the data ($string) to the text file
		fclose($myFile); // Closing the file after writing data to it
		return true;
	}
	function get_and_create_path(){
		if(!$p=$this->get_full_path()){
			return false;	
		}
		if(is_dir($p)){
			return $p;	
		}
		if(!$fm=$this->get_filemanager()){
			return false;	
		}
		
		if($path=$fm->check_and_create_path($p)){
			return $p;
		}

			
	}
	function get_file_full_path_if_exists(){
		if(!$f=$this->get_file_full_path()){
			return false;
		}
		if(is_file($f)){
			if(file_exists($f)){
				return $f;
			}
		}
		return false;
			
	}
	function delete_file(){
		if(!$f=$this->get_file_full_path_if_exists()){
			return false;
		}
		unlink($f);
		return true;
	}
	function get_file_full_path(){
		if(!$p=$this->get_full_path()){
			return false;	
		}
		if(!$n=$this->get_file_name()){
			return false;	
		}
		return $p."/".$n;
		
	}
	final function get_data(){
		$this->_load_data_once();
		return $this->data;
	}
	final function unset_data(){
		unset($this->data);	
	}
	final function set_data($data,$stripslashes=true){
		$this->is_new=false;
		$this->unset_data();
		if(!is_string($data)){
			$data="";
		}
		if($stripslashes){
			$data=stripcslashes($data);	
		}
		$this->data=$data;
		return true;
	}
	final function load_data(){
		$this->_dataloaded=true;
		$this->data="";
		if($f=$this->get_file_full_path_if_exists()){
			if($string=file_get_contents($f)){
				$this->is_new=false;
				$this->data=$string;
				return true;	
			}
		}
		return false;	
	}
	
	private function _load_data_once(){
		if($this->_dataloaded){
			return true;
		}
		return $this->load_data();
		
	}
	
	
	final function get_file_name(){
		if(!$this->code){
			return false;	
		}
		return $this->code.".".$this->mainman->get_fileext();
	}
	final function get_full_path(){
		if(!$p=$this->mainman->get_path()){
			return false;	
		}
		if(!$this->path){
			return $p;	
		}
		return $p."/".$this->path;
	}
	
	final function get_filemanager(){
		return $this->mainman->get_filemanager();
	}

	final function init($mainman,$code,$fullcode,$path){
		//$subpath: relativa a basepath de $mainman, validado por $mainman
		$this->code=$code;
		$this->fullcode=$fullcode;
		$this->path=$path;
		$this->mainman=$mainman;
		$this->set_mainap($this->mainman->mainap);	
	}
}


?>