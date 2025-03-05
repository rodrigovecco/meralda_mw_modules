<?php
abstract class mwmod_mw_facebook_sess_obj extends mw_apsubbaseobj {
	
	
	private $sessKey;
	public $protectedKeys="";
	final function init($sessKey){
		$this->sessKey=$sessKey;
	}
	function getProtectedKeys(){
		if($s=$this->protectedKeys){
			return explode(",",$s);	
		}
		return false;
	}
	function getExtendedInfo(){
		$d=$this->getInfo();
		return $d;
	}
	
	function getInfo(){
		if(!$data=$this->getSessData()){
			return false;	
		}
		if($protectedkeys=$this->getProtectedKeys()){
			foreach($protectedkeys as $k){
				unset($data[$k]);	
			}
		}
		return $data;
	}
	final function setSessData($val,$key){
		if(!$sv=$this->getSessKey()){
			return false;	
		}
		
		if(!is_array($_SESSION[$sv])){
			$_SESSION[$sv]=array();	
		}
		mw_array_set_sub_key($key,$val,$_SESSION[$sv]);
		
		return true;
		
	}
	final function getSessData($key=false){
		if(!$sv=$this->getSessKey()){
			return false;	
		}
		if(!is_array($_SESSION[$sv])){
			return false;	
		}
		return mw_array_get_sub_key($_SESSION[$sv],$key);	
	
	}
	
	final function unsetSessData(){
		if(!$sv=$this->getSessKey()){
			return false;	
		}
		$_SESSION[$sv]=NULL;
		unset($_SESSION[$sv]);
		return true;
		
		
	}
	final function getSessKey(){
		return $this->sessKey;	
	}

	

}
?>