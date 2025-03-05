<?php
class mwmod_mw_extmods_phpspreadsheet extends mw_apsubbaseobj{
	public $autoLoaderRegistered;
	function isInstalled(){
		
		$subpathman=$this->mainap->get_sub_path_man("modulesext/phpoffice/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet","system");
		//echo $subpathman->get_full_path_filename("Spreadsheet.php");
		if($fullpath=$subpathman->get_file_path_if_exists("Spreadsheet.php")){
			return true;

		}
		return false;
	
	}
	function registerAutoLoader(){
		if(isset($this->autoLoaderRegistered)){
			return $this->autoLoaderRegistered;
		}
		$this->autoLoaderRegistered=false;

		$subpathman=$this->mainap->get_sub_path_man("modulesext/phpoffice/vendor","system");
		$autoloader=mw_get_autoload_manager();
		$autoloader->setSilentMode();
		//echo $subpathman->get_full_path_filename("autoload.php");
		if($fullpath=$subpathman->get_file_path_if_exists("autoload.php")){
			
		
			require_once $fullpath;
			$this->autoLoaderRegistered=true;

		}
		return $this->autoLoaderRegistered;
	}

}

?>