<?php
//
class mwmod_mw_modulesman_dirman_autoload extends mwmod_mw_modulesman_dirman_abs{
	private $autoloadclassman;
	function __construct($cod,$subpath,$autoloadclassman){
		$this->init($cod,$subpath,"system");
		$this->set_autoloadclassman($autoloadclassman);
		
	}
	function create_single_file_info_man_for_class($class){
		if(!$subpath=$this->autoloadclassman->get_class_sub_path($class)){
			return false;
		}
		if($file=$this->autoloadclassman->get_class_file_basename($class)){
			return $this->create_file_info_man($subpath,$file);
		}
			
	}
	function create_dir_info_man_for_class($class){
		if($subpath=$this->autoloadclassman->get_class_sub_path($class)){
			return $this->create_dir_info_man($subpath);
		}
	}
	final function set_autoloadclassman($autoloadclassman){
		$this->autoloadclassman=$autoloadclassman;	
	}
	final function __get_priv_autoloadclassman(){
		return $this->autoloadclassman; 	
	}

	
}
?>