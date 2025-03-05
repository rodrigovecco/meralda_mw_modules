<?php
class mwmod_mw_data_session_man extends mw_apsubbaseobj{
	private $varname;
	var $datainitdone=false;
	private $datamanagers=array();
	function __construct($varname){
		$this->init($varname);
	}
	function getItem($cod,$subcode=false){
		if($dm=$this->get_datamanager($cod)){
			if($subcode){
				return $dm->get_sub_item($subcode);	
			}
			return $dm;
		}
	}
	function get_data($key=false){
		if(!is_array($_SESSION[$this->varname]??null)){
			return false;	
		}
		if(!$key){
			return $_SESSION[$this->varname];
		}
		return mw_array_get_sub_key($_SESSION[$this->varname],$key);	
	}
	function set_data($data,$key=false){
		if(!$key){
			$_SESSION[$this->varname]=$data;
			return;
		}
		if(!is_array($_SESSION[$this->varname]??null)){
			$_SESSION[$this->varname]=array();
		}
		mw_array_set_sub_key($key,$data,$_SESSION[$this->varname]);
			
	}
	
	function unset_subdata($key){
		if(!$key){
			return false;	
		}
		$a=explode(".",$key);
		$last=array_pop($a);
		if(!is_array($_SESSION[$this->varname]??null)){
			return;	
		}
		return $this->unset_subdata_by_keys_list($_SESSION[$this->varname],$last,$a);
		
	}
	private function unset_subdata_by_keys_list(&$array,$key,$topkeys){
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
		return $this->unset_subdata_by_keys_list($array[$first],$key,$topkeys);
		
		
		
	}
	
	function unset_data(){
		unset($_SESSION[$this->varname]);
		
		
	}
	
	
	function _create_datamanager($code){
		if(!$code=$code.""){
			return false;	
		}
		$man= new mwmod_mw_data_session_item($this,$code);
		return $man;	
	}
	final function get_datamanager($code="data"){
		if(!$code=$code.""){
			return false;	
		}
		if($this->datamanagers[$code]??null){
			return $this->datamanagers[$code];	
		}
		if(!$sm=$this->_create_datamanager($code)){
			return false;	
		}
		$this->datamanagers[$code]=$sm;
		return $this->datamanagers[$code];	
		
	}
	final function init($varname){
		$this->varname=$varname;	
		
	}
	final function __get_priv_varname(){
		return $this->varname;	
	}
	

	

}


?>