<?php
class mwmod_mw_data_secret extends mwmod_mw_data_manabs{
	
	function __construct($relPath){
	
		$this->set_rel_path($relPath);	
	}
	
	function create_dataman_string(){
		if(!$this->strEnabled){
			return false;	
		}
		if(!$p=$this->getStrRelPath()){
			return false;		
		}
		
		$m= new mwmod_mw_data_secret_str($p);
		return $m;
	}
	


	function create_dataman_xml(){
		//to be implemented
		return false;		
	}
	

	
	function create_dataman_json(){
		//to be implemented
		return false;		
		
	}
	
	
	function create_dataman_tree(){
		return false;	
	
	}
	
	
	
	
	function get_path($sub=false,$mode="userfiles"){
		
		return false;
		
	}
	function get_public_path($sub=false){
		return false;
	}

	function create_public_path_man(){
		return false;
	}
	
	
	
	function create_path_man(){
		return false;
	}
	
	
	
	
	
}

?>