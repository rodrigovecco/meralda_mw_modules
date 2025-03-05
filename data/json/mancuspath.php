<?php
class mwmod_mw_data_json_mancuspath extends mwmod_mw_data_json_man{
	private $rootPath;
	
	
	function __construct($subpath,$rootpath){
		//warning, does not valitade root path
		$this->initRootMode($subpath,$rootpath);
	}
	
	function get_main_root_path(){
		return $this->__get_priv_rootPath();
	}
	final function __get_priv_rootPath(){
		return $this->rootPath; 	
	}

	
	final function initRootMode($path,$rootpath){
		$this->rootPath=$rootpath;
		$this->set_mainap();
		$this->init($path);	
	}
	

}


?>