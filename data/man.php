<?php
class mwmod_mw_data_man extends mwmod_mw_data_manabs{
	function __construct($relPath=false){
		if($relPath){
			$this->set_rel_path($relPath);	
		}
	}
	
	function get_debug_data(){
		$r=array();
		$r["relpath"]=$this->getStrRelPath();
		$r["relpath1"]=$this->getStrRelPath("xxx");
		$r["path"]=$this->get_path();
		$r["pathxxx"]=$this->get_path("xxx");
		if($this->path_man){
			$r["path_man"]=$this->path_man->get_debug_data();	
		}
		
		
		$r["public_path"]=$this->get_public_path();
		$r["public_path_xxx"]=$this->get_public_path("xxx");
		if($this->public_path_man){
			$r["public_path_man"]=$this->public_path_man->get_debug_data();	
		}
		if($this->dataman_tree){
			$r["dataman_tree"]=$this->dataman_tree->get_path();	
		}
		if($m=$this->getItemTree()){
			$r["getItemTree"]=$m->get_file_full_path();		
		}
		if($m=$this->getItemStr()){
			$r["getItemStr"]=$m->get_file_full_path();		
		}
		if($m=$this->getItemJson()){
			$r["getItemJson"]=$m->get_file_full_path();		
		}
		if($m=$this->getItemXML()){
			$r["getItemXML"]=$m->get_file_full_path();		
		}
		
		
		return $r;
		
	}
	
	
	
}

?>