<?php
class mwmod_mw_data_xmltree_man extends mwmod_mw_data_tree_man{
	function __construct($path){
		$this->init($path);
	}
	
	
	
	function _create_datamanager($code,$path){
		if(!$fullcode=$this->get_datamanager_fullcode($code,$path)){
			return false;	
		}
		$man= new mwmod_mw_data_xmltree_item($this,$code,$fullcode,$path);
		return $man;	
	}

	function get_fileext(){
		return "xml";
	}

}


?>