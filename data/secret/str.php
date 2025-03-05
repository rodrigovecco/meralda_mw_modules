<?php
class mwmod_mw_data_secret_str extends mwmod_mw_data_str_mancuspath{
	function __construct($subpath){
		//warning, doen not valitade root path
		$this->initSpecialPath($subpath);
	}
	function initSpecialPath($subpath){


		$rootPath=$this->mainap->get_sub_path("cfg/secret/","instance");
		$this->initRootMode($subpath,$rootPath);
	}


	
	

}


?>