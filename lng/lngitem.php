<?php
class  mwmod_mw_lng_lngitem extends mw_apsubbaseobj{
	private $code;
	private $name;
	private $_stored_msg=array();
	private $_stored_result;
	private $man;
	private $index;
	private $cfgdata;
	private $_path;
	private $inicfg;
	function __construct($code,$man,$name=false){
		$this->init($code,$man,$name);	
	}
	function create_inicfg(){
		
		$cod=$this->get_code();
		$srcs=array(
			new mwmod_mw_data_cfg_src_ini("lng_def",$this->mainap->get_path("system")."/lng/cfg/{$cod}.ini"),
			new mwmod_mw_data_cfg_src_ini("lng_cus",$this->mainap->get_path("instance")."/lng/cfg/{$cod}.ini"),
		);
		$man=new mwmod_mw_data_cfg_man($srcs,$this->mainap);
		return $man;
	}
	function get_ini_cfg_value($cod,$def=false){
		if($man=$this->get_inicfg()){
			return $man->get_value($cod,$def);	
		}
		return $def;
	}
	function get_globalize_locale_src(){
		return $this->get_ini_cfg_value("globalize_locale_src");
	}
	function get_globalize_locale_cod(){
		return $this->get_ini_cfg_value("globalize_locale_cod");
	}
	
	
	function get_locale_cod(){
		return $this->get_ini_cfg_value("locale");
	}

	final function get_inicfg(){
		return $this->__get_priv_inicfg();	
	}
	final function __get_priv_inicfg(){
		if(!isset($this->inicfg)){
			$this->inicfg=$this->create_inicfg();	
		}
		return $this->inicfg; 	
	}

	
	function get_init_cfg_data(){
		if(!$code=basename($this->code)){
			return false;
		}
		$sp="cfg/lng/".$code;
		if(!$file=$this->mainap->get_file_path_if_exists("cfg.php",$sp,"instance")){
			return false;
		}
		ob_start();
		include $file;
		ob_end_clean();
		return $data;
	}
	final function _init_cfg_data(){
		if(isset($this->cfgdata)){
			return true;	
		}
		$this->cfgdata=$this->get_init_cfg_data();
		if(!is_array($this->cfgdata)){
			$this->cfgdata=array();	
		}
		return true;
		
	}
	final function get_cfg_data($key=false){
		$this->_init_cfg_data();
		return mw_array_get_sub_key($this->cfgdata,$key);
			
	}
	final function get_path(){
		if(isset($this->_path)){
			return $this->_path;	
		}
		if(!$code=basename($this->code)){
			return false;
		}
		
		if(!$p=$this->mainap->get_sub_path("lng/".$code,"system")){
			return false;	
		}
		$this->_path=$p;	
		return $this->_path;	
		
	}
	final function init_stored_msgs(){
		//en desuso
		if(isset($this->_stored_result)){
			return $this->_stored_result;	
		}
		$this->_stored_result=false;
		return false;
		/*
		
		if(!$p=$this->get_path()){
			return false;
		}
		
		$file=$p."/msgs.php";
		
		if(!is_file($file)){
			
			return false;	
		}
	
		if(!file_exists($file)){
			return false;	
		}
	
		include $file;
		if(is_array($data)){
			$this->_stored_msg=$data;
			$this->_stored_result=true;
		}
		return $this->_stored_result;
		*/
		
		
	}
	final function get_stored_msg($code){
		//en desusp
		return false;
		if(!$this->check_str_key($code)){
			return false;
		}
		
		if(!$this->init_stored_msgs()){
			return false;	
		}
		if($msg=$this->_stored_msg[$code]){
			return stripslashes($msg);
		}
	}
	
	final function set_index($index){
		$this->index=$index+0; 	
	}
	final function get_code(){
		return $this->code; 	
	}
	function __toString(){
		return 	$this->name;
	}
	final function __get_priv_name(){
		return $this->name; 	
	}
	final function __get_priv_index(){
		return $this->index; 	
	}
	final function __get_priv_code(){
		return $this->code; 	
	}
	
	final function init($code,$man,$name=false){
		$ap=$man->mainap;
		$this->man=$man;
		$this->code=basename($code);
		if(!$name){
			$name=$code;	
		}
		$this->name=$name;
		$this->set_mainap($ap);	
		
	}

}
?>