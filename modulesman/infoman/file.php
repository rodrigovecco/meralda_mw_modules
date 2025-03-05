<?php
class mwmod_mw_modulesman_infoman_file extends mwmod_mw_modulesman_infoman_abs{
	function __construct($subpath,$filename,$dirman){
		$cod=$subpath."/".$filename;
		$this->init($cod,$dirman,$subpath,$filename);
		$this->type="file";
		
	}
	function get_info_file_name(){
		if(!$this->filename){
			return false;	
		}
		
		return $this->filename.".mwinfo";	
	}

	
}
?>