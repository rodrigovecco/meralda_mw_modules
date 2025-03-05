<?php
class  mwmod_mw_paymentapi_api_culqi_api extends mwmod_mw_paymentapi_abs_api{
	function __construct($man){
		$this->init($man);
	}
	function debugTestApiClassesLoaded(){
		$this->createAutoloaders();
		$COD_COMERCIO = "{Código de comercio}";
 		$culqi = new Culqi\Culqi(array('api_key' => $COD_COMERCIO));
		echo get_class($culqi);

	}
	function createAutoloaders(){
		$autoloader=mw_get_autoload_manager();
		



		if(!$autoloader->get_pref_man("culqi")){
			$exmodpath=$this->mainap->get_sub_path("modulesext","system");
			
			$autoloader->addSpecialAutoloader("culqi",
						$exmodpath."/culqi",
						"Culqi","Culqi"
						);
		}
	}
	function createCulqi(){
		if(!$key=$this->man->get_key_item("privatekey")->get_data()){
			return false;	
		}
		$this->createAutoloaders();

		
		/*
		$GLOBALS["__mw_autoload_manager"]->
		*/

 		 //$culqi = new Culqi\Culqi(array('api_key' => $key));
		 //return $culqi;
		
	}

}
?>