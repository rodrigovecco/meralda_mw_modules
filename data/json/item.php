<?php
class mwmod_mw_data_json_item extends mwmod_mw_data_tree_item{
	
	function __construct($mainman,$code,$fullcode,$path){
		$this->init($mainman,$code,$fullcode,$path);	
	}
	function encode($value){
		//json_encode ( mixed $value [, int $options = 0 [, int $depth = 512 ]] )	
		return json_encode($value,JSON_PRETTY_PRINT);
	}
	function decode($value){
		//json_decode ( string $json [, bool $assoc = false [, int $depth = 512 [, int $options = 0 ]]] )
		return json_decode($value."",true);
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
		$item=new mwmod_mw_data_json_subitem($key,$this);
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
		$data=$this->_get_data();
		$string=$this->encode($data);
		$myFile= fopen($f,'a'); 
		fputs($myFile, $string); 
		fclose($myFile); 
		return true;
	}
	function get_data_to_load(){
		if($f=$this->get_file_full_path_if_exists()){
			if($string=file_get_contents($f)){
				if($data=$this->decode($string)){
					if(is_array($data)){
						return $data;
						//return true;	
					}
				}
			}
		}
			
	}

}


?>