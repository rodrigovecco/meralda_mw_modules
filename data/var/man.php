<?php
class mwmod_mw_data_var_man extends mwmod_mw_data_session_man{
	private $data=array();
	function __construct(){
		
	}
	function get_data($key=false){
		return $this->_get_data($key);
	}
	final function _get_data($key=false){
		if(!$key){
			return $this->data;
		}
		return mw_array_get_sub_key($this->data,$key);	
			
	}
	final function _set_data($data,$key=false){
		if(!$key){
			$this->data=$data;
			return;
		}
		if(!is_array($this->data)){
			$this->data=array();
		}
		mw_array_set_sub_key($key,$data,$this->data);
			
	}
	
	function set_data($data,$key=false){
		return $this->_set_data($data,$key);
			
	}
	function unset_subdata($key){
		return $this->_unset_subdata($key);
	}
	
	final function _unset_subdata($key){
		if(!$key){
			return false;	
		}
		$a=explode(".",$key);
		$last=array_pop($a);
		if(!is_array($this->data)){
			return;	
		}
		return $this->var_unset_subdata_by_keys_list($this->data,$last,$a);
		
	}
	private function var_unset_subdata_by_keys_list(&$array,$key,$topkeys){
		if(!is_array($array)){
			return;	
		}
		if(!$key){
			return false;	
		}
		if(!sizeof($topkeys)){
			unset($array[$key]);
			return;	
		}
		$first=array_shift($topkeys);
		if(!$first){
			return false;	
		}
		if(!is_array($array[$first])){
			return;
		}
		return $this->var_unset_subdata_by_keys_list($array[$first],$key,$topkeys);
		
		
		
	}
	
	function unset_data(){
		$this->_unset_data();
	}
	
	final function _unset_data(){
		$this->data=array();
	}
	
	function _create_datamanager($code){
		if(!$code=$code.""){
			return false;	
		}
		$man= new mwmod_mw_data_var_item($this,$code);
		return $man;	
	}
	

	

}


?>