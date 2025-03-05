<?php
class mwmod_mw_modulesman_mainman extends mwmod_mw_modulesman_mainmanabs{
	
	function __construct($ap=false){
		$this->init($ap);		
	}
	function create_def_dir_managers(){
		if($autoloadmans=$this->autoloadman->get_managers()){
			foreach($autoloadmans as $prefman){
				if($subprefmans=$prefman->get_managers()){
					foreach($subprefmans as $subprefman){
						if($dirman=$this->new_autoload_dir_man($subprefman)){
							$this->add_dirman($dirman);
						}
					}
				}
			}
		}
		$dirman=new mwmod_mw_modulesman_dirman_public("res","res");
		$this->add_dirman($dirman);
	}
	function get_autoload_dirman_for_class($class){
		if(!$subprefman=$this->autoloadman->get_sub_pref_man_for_class($class)){
			return false;
		}
		if($cod=$this->get_dirman_cod_for_autoload_subprefman($subprefman)){
			return $this->get_dirman($cod);
		}
	}

	
}
?>