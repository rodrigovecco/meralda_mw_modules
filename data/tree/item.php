<?php
class mwmod_mw_data_tree_item extends mw_apsubbaseobj{
	//sólo debe ser creado por mw_baseobjects_treedata
	private $mainman;
	private $path;
	private $code;
	private $fullcode;
	private $data;
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
	
	
	function get_sub_item($key){
		if(!$key){
			return false;	
		}
		if(!is_string($key)){
			if(!is_numeric($key)){
				return false;
			}
		}
		$this->load_data_once();
		$item=new mwmod_mw_data_tree_subitem($key,$this);
		return $item;
	
	}
	function set_def_data($data,$key=false){
		if($this->is_data_defined($key)){
			return false;	
		}
		$this->set_data($data,$key);
		return true;
	}
	
	final function set_data($data,$key=false){
		$this->is_new=false;
		if(!$key){
			return $this->set_data_all($data);
		}
		$this->_load_data_once();
		mw_array_set_sub_key($key,$data,$this->data);
		return true;
	}
	function get_data_as_list($key=false,$falseonfail=false){
		if($data=$this->get_data($key)){
			if(is_array($data)){
				return $data;	
			}
		}
		if($falseonfail){
			return false;	
		}
		$r=array();
		return $r;
	}
	final function get_data($key=false){
		$this->_load_data_once();
		if(!$key){
			return $this->data;
		}
		//return $this->_get_sub_data($key);
		return mw_array_get_sub_key($this->data,$key);	
	}
	function get_sub_data_debug_info($key=false){
		$r=array();
		$r["key"]=$key;
		$r["data"]=$this->get_data($key);
		$r["defined"]=$this->is_data_defined($key);
		return $r;
		
		
		
	}
	final function is_data_defined($key=false){
		if(!$key){
			if($this->isNew()){
				return false;	
			}else{
				return true;	
			}
		}
		$this->_load_data_once();
		$isdefined=false;
		$r=$this->_priv_get_sub_data($this->data,$key,$isdefined);
		return $isdefined;
		
		
	}
	/*
	
	private function _get_sub_data($key,&$isdefined=false){
		$isdefined=false;
		$r=$this->_priv_get_sub_data($this->data,$key,$isdefined);
		return $r;
		//$this->_load_data_once();
		
		
			
	}
	*/
	private function _priv_get_sub_data($a,$key,&$isdefined=false){
		//sólo probado para is_data_defined
		if(!$key){
			$isdefined=true;
			return $a;	
		}
	
	
		$val=$a;
		$keys1=explode(".",$key);
		$keys=array();
		foreach ($keys1 as $k){
			if($k){
				$keys[]=$k;	
				//$keysleft[]=$k;	
			}
		}
		if(sizeof($keys)==0){
			$isdefined=true;
			return $val;
		}
		foreach ($keys as $k){
			if(!is_array($val)){
				$isdefined=false;
				return false;
			}else{
				if(!isset($val[$k])){
					$isdefined=false;
					return false;
						
				}else{
					$isdefined=true;
					$val=$val[$k];
						
				}
			}
			//array_shift($keysleft);
			//$val=$val[$k];
		}
		return $val;

		
		/*
		
		$klist=explode(".",$key,2);
		$cod=$klist[0];
		//$subcod=$klist[1];
		$restcods=$klist[1];
		if(!is_array($src)){
			$isdefined=false;
			return false;	
		}
		if(!$cod){
			$isdefined=true;
			return $src;	
		}
		if(!isset($src[$cod])){
			$isdefined=false;
			return false;	
		}
		if(!$restcods){
			$isdefined=true;
			return $src[$cod];	
		}
		if(!is_array($src[$cod])){
			$isdefined=false;
			return false;	
		}
		$subisdefined=false;
		$r=$this->_priv_get_sub_data($src[$subcod],$restcods,$subisdefined);
		$isdefined=$subisdefined;
		return $r;
		*/
		
		
		
		
			
		
	}
	/*
	private function _get_sub_data($src,$key,&$isdefined=false){
			
		
	}
	*/
	function set_data_and_save($data,$key=false){
		$this->set_data($data,$key);
		$this->save();
	}
	final function _get_data(){
		if(!is_array($this->data)){
			return array();	
		}
		return $this->data;
	
	}
	function save(){
		$this->delete_file();
		if(!$p=$this->get_and_create_path()){
			return false;	
		}
		if(!$f=$this->get_file_full_path()){
			return false;
		}
		$string=serialize($this->_get_data());
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
	final function set_data_all($data){
		$this->_dataloaded=true;
		if(!is_array($data)){
			$data=array();	
		}
		$this->data=$data;
		$this->is_new=false;
		return true;
			
	}
	final function load_data(){
		$this->_dataloaded=true;
		$this->data=array();
		if($data=$this->get_data_to_load()){
			$this->is_new=false;
			$this->data=$data;
			return true;	
				
		}
		return false;	
	}
	function get_debug_info(){
		$r=array(
			"cod"=>$this->code,
			"fullpath"=>$this->get_file_full_path(),
			"path"=>$this->path,
		);
		return $r;	
	}
	function get_data_to_load(){
		if($f=$this->get_file_full_path_if_exists()){
			if($string=file_get_contents($f)){
				if($data=unserialize($string)){
					if(is_array($data)){
						
						return $data;
						//return true;	
					}
				}
			}
		}
			
	}
	final function load_data_once(){
		$this->_load_data_once();	
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