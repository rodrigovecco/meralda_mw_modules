<?php
class mwmod_mw_data_str_mancuspath extends mwmod_mw_data_str_man{
	private $rootPath;
	var $fileext="txt";
	
	function __construct($subpath,$rootpath){
		//warning, doen not valitade root path
		$this->initRootMode($subpath,$rootpath);
	}
	
	function get_main_root_path(){
		return $this->__get_priv_rootPath();
	}
	final function __get_priv_rootPath(){
		return $this->rootPath; 	
	}

	function get_fileext(){
		return $this->fileext;
	}
	
	final function initRootMode($path,$rootpath){
		$this->rootPath=$rootpath;
		$this->set_mainap();
		$this->init($path);	
	}
	

}


?>