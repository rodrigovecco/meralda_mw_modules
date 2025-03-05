<?php
//20230221
class mwmod_mw_helper_inputvalidator_request extends mwmod_mw_helper_inputvalidator_man{
	private $req_mode=0;//0: def ($_REQUEST) 1: $_REQUEST, 2: $_GET, 3 $_POST, 4 other (manual);
		//en el futuro se podrá get, post, etc
		
	var $req_input_ok=false;
	function __construct($varname=false,$mode=false){
		$this->set_req_mode($mode);
		if($varname){
			$this->set_value_from_root_input_array($varname);	
		}
	}
	final function set_req_mode($mode=false){
		$this->req_mode=0;
		
		if(!$mode=$mode+0){
			return;	
		}
		if($mode==1){
			$this->req_mode=1;	
		}
		if($mode==2){
			$this->req_mode=2;	
		}
		if($mode==3){
			$this->req_mode=3;	
		}
		if($mode==4){
			$this->req_mode=4;	
		}
	}
	function is_req_input_ok(){
		return $this->req_input_ok;
	}
	function set_value_from_root_input_array($varname=false){
		$v=$this->get_value_from_root_input_array($varname);
		$this->req_input_ok=false;
		if($v){
			if(is_array($v)){
				$this->req_input_ok=true;	
			}
		}
		return $this->set_value($v);

	}
	function set_value_manual_mode_from_array($value){
		$this->req_input_ok=false;
		if($value){
			if(is_array($value)){
				$this->req_input_ok=true;	
			}
		}
		return $this->set_value($value);
	}
	function get_value_from_root_input_array($varname=false){
		$v=$this->get_root_input_array();
		return mw_array_get_sub_key($v,$varname);
	}
	function get_root_input_array(){
		if($this->req_mode==1){
			return $_REQUEST;	
		}
		if($this->req_mode==2){
			return $_GET;	
		}
		if($this->req_mode==3){
			return $_POST;	
		}
		if($this->req_mode==4){
			//es manual, debe usarse set_value_manual_mode_from_array
			return false;
		}
		return $_REQUEST;	
	}
	final function __get_priv_req_mode(){
		return $this->req_mode; 	
	}
}
?>