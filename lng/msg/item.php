<?php
class  mwmod_mw_lng_msg_item extends mw_apsubbaseobj{
	private $cod;
	private $man;//mwmod_mw_lng_msg_lngmanabs
	var $msg="";
	function __construct($cod,$man){
		$this->init($cod,$man);	
	}
	function get_txt_file_line(){
		return $this->cod." ".$this->escape_msg_for_storage();	
	}
	function escape_msg_for_storage(){
		$search = array("\t", "\n", "\r");
    	$replace = array('\t', '\n', '\r');
    	return str_replace($search, $replace, $this->msg);
	}
	function get_msg_txt($params=false){
		$msg=$this->msg;
		if(is_array($params)){
			foreach($params as $cod=>$v){
				$msg=str_replace("%{$cod}%",$v,$msg);
			}
		}
		return $msg;
	}

	function set_msg($msg){
		$this->msg=$msg;
	}
	final function __get_priv_cod(){
		return $this->cod; 	
	}
	final function __get_priv_man(){
		return $this->man; 	
	}

	final function init($cod,$man){
		$this->man=$man;
		$this->cod=$cod;
		
	}

}
?>