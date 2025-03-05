<?php
class  mwmod_mw_paymentapi_api_culqi_man extends mwmod_mw_paymentapi_abs_man{
	function __construct(){
		$this->init("culqi");
		$this->testMode=false;
		$this->name="Culqi";
	}
	
	function get_js_init(){
		if(!$this->isEnabled()){
			return false;	
		}
		
		$js= new mwmod_mw_jsobj_codecontainer();
		$key=$this->getPublicKey()."";
		$js->add_cont("Culqi.publicKey = '".$js->get_txt($key)."';\n");
		return $js;
	
	}
	
	function new_debug_ui($cod,$parent){
		
		
		return new mwmod_mw_paymentapi_api_culqi_debugui_main($this,$cod,$parent);
	}
	
	function checkForEnable(){
		if($this->getPublicKey()){
			if($this->getPrivateKey()){
				return true;	
			}
				
		}
	}
	function getPublicKey(){
		return $this->get_key_item("publickey")->get_data()."";	
	}
	function getPrivateKey(){
		return $this->get_key_item("privatekey")->get_data()."";	
	}
	
	
	
	function createNewApi(){
		return new mwmod_mw_paymentapi_api_culqi_api($this);
	}
	function doLoadApiClasses(){
		 /*
		$file=dirname(__FILE__)."/apiclases/culqi.php";
		if(file_exists($file)){
			require_once($file);
		}
		*/
		
	}


}
?>