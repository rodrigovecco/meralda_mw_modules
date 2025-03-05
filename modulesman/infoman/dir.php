<?php
class mwmod_mw_modulesman_infoman_dir extends mwmod_mw_modulesman_infoman_abs{
	function __construct($subpath,$dirman){
		$cod=$subpath;
		$this->init($cod,$dirman,$subpath,false);
		$this->type="dir";
	}
	function get_info_file_name(){
		return "dir.mwinfo";	
	}

	
}
?>