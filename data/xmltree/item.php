<?php
class mwmod_mw_data_xmltree_item extends mwmod_mw_data_tree_item{
	
	function __construct($mainman,$code,$fullcode,$path){
		$this->init($mainman,$code,$fullcode,$path);	
	}
	function get_sub_item($key){
		if(!$key){
			return false;	
		}
		if(!is_string($key)){
			if(!is_numeric($key)){
				return false;
			}
		}
		$this->load_data_once();
		$item=new mwmod_mw_data_xmltree_subitem($key,$this);
		return $item;
	
	}
	function save(){
		$this->delete_file();
		if(!$p=$this->get_and_create_path()){
			return false;	
		}
		if(!$f=$this->get_file_full_path()){
			return false;
		}
		$xmlroot=new mwmod_mw_data_xml_root();
		$xml=$xmlroot->get_sub_root();
		if(!$data=$this->_get_data()){
			$data=array();	
		}
		$xml->set_value($data);
		//$xml->set_prop("ok",1);
		//$xml->set_prop("msg","hola");
		$string=$xmlroot->get_output_for_file();

		
		//$string=serialize($this->_get_data());
		$myFile= fopen($f,'a'); // Open the file for writing
		fputs($myFile, $string); // Write the data ($string) to the text file
		fclose($myFile); // Closing the file after writing data to it
		return true;
	}
	function get_data_to_load(){
		if($f=$this->get_file_full_path_if_exists()){
			
			if($string=file_get_contents($f)){
				$parser= new mwmod_mw_data_xml_parser();
				if($data=$parser->get_data_from_xml_str($string)){
					if(is_array($data)){
						return $data;
					}
				}
			}
			
		}
			
	}

}


?>