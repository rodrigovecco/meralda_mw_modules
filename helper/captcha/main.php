<?php
class mwmod_mw_helper_captcha_main extends mw_apsubbaseobj{
	//var $items=array();
	var $sessionvarname="captcha";
	function __construct(){
		//$this->init($ap);	
	}
	function validate($input,$cod="captcha",&$msg=false){
		if(!$input){
			$msg=$this->lng_get_msg_txt("invalid_code","Código no válido");
			return false;	
		}
		if(!$okcode=$this->get_item_secret_by_cod($cod)){
			$msg=$this->lng_get_msg_txt("invalid_code","Código no válido");
			return false;	
				
		}
		if($input===$okcode){
			return true;	
		}else{
			$msg=$this->lng_get_msg_txt("invalid_code","Código no válido");
			return false;	
				
		}
	}
	function exec_getcmd_img($params,$filename){
		$this->output_item($params["c"]??null);	
	}
	function __accepts_exec_cmd_by_url(){
		return true;	
	}
	function new_item_for_input($cod){
		if(!$item=$this->new_item($cod)){
			return false;	
		}
		return $item;
	}
	function new_item($cod){
		if(!$cod){
			return false;	
		}
		$item = new mwmod_mw_helper_captcha_item($cod,$this);
		return $item;
	}
	function set_session_data_item($item){
		$cod=$item->cod;
		if(!$cod){
			return false;	
		}
		$this->init_sess_var();
		if(!isset($_SESSION[$this->sessionvarname][$cod])){
			$_SESSION[$this->sessionvarname][$cod]=array();
		}
		
		if(!is_array($_SESSION[$this->sessionvarname][$cod])){
			$_SESSION[$this->sessionvarname][$cod]=array();
		}
		
		$_SESSION[$this->sessionvarname][$cod]["height"]=$item->height;
		$_SESSION[$this->sessionvarname][$cod]["width"]=$item->width;
		$_SESSION[$this->sessionvarname][$cod]["len"]=$item->len;
		$_SESSION[$this->sessionvarname][$cod]["posy1"]=$item->posy1;
		$_SESSION[$this->sessionvarname][$cod]["posy2"]=$item->posy2;
		return true;
			
	}
	function set_item_secret($item){
		$cod=$item->cod;
		if(!$cod){
			return false;	
		}
		$this->init_sess_var();
		if(!$_SESSION[$this->sessionvarname][$cod]??null){
			$_SESSION[$this->sessionvarname][$cod]=array();
		}
		if(!is_array($_SESSION[$this->sessionvarname][$cod]??null)){
			$_SESSION[$this->sessionvarname][$cod]=array();
		}
		$_SESSION[$this->sessionvarname][$cod]["secret"]=$item->secret;
		
			
	}
	function get_item_secret_by_cod($cod){
		if(!$cod){
			return false;	
		}
		$this->init_sess_var();
		if(!is_array($_SESSION[$this->sessionvarname][$cod]??null)){
			return false;
		}
		return $_SESSION[$this->sessionvarname][$cod]["secret"];
		
	}
	function get_item_data($item){
		$cod=$item->cod;
		if(!$cod){
			return false;	
		}
		$this->init_sess_var();
		if(!isset($_SESSION[$this->sessionvarname][$cod])){
			$_SESSION[$this->sessionvarname][$cod]=array();
		}
		
		if(!is_array($_SESSION[$this->sessionvarname][$cod])){
			$_SESSION[$this->sessionvarname][$cod]=array();
		}
		return $_SESSION[$this->sessionvarname][$cod]??null;
		
			
	}
	
	function output_item($cod){
		//mw_array2list_echo($_SESSION[$this->sessionvarname]);
		if(!$cod){
			return false;	
		}
		$this->init_sess_var();
		if(!isset($_SESSION[$this->sessionvarname][$cod])){
			return false;
		}
		
		if(!$_SESSION[$this->sessionvarname][$cod]){
			return false;
		}
		if(!is_array($_SESSION[$this->sessionvarname][$cod])){
			return false;
		}
		if(!$item=$this->new_item($cod)){
			return false;	
		}
		$item->set_data_from_session($_SESSION[$this->sessionvarname][$cod]);
		$item->output();
	}
	
	
	function init_sess_var(){
		if(!isset($_SESSION[$this->sessionvarname])){
			$_SESSION[$this->sessionvarname]=array();	
		}
		if(!is_array($_SESSION[$this->sessionvarname])){
			$_SESSION[$this->sessionvarname]=array();	
		}
	}

	
	
	
}
?>