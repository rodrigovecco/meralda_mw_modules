<?php
class  mwmod_mw_paymentapi_api_culqi_api extends mwmod_mw_paymentapi_abs_api{
	function __construct($man){
		$this->init($man);
	}
	function debugTestApiClassesLoaded(){
		$culqi = new mwmod_mw_paymentapi_api_culqi_facade("{Codigo de comercio}");
		echo get_class($culqi);
	}
	/**
	 * Devuelve el cliente Culqi (fachada compatible con $culqi->Charges->create(...) etc.).
	 *
	 * Implementacion propia, sin dependencias externas. Reemplaza al SDK
	 * culqi-php (Composer + rmccue/Requests) que estaba en modulesext/culqi.
	 *
	 * @return mwmod_mw_paymentapi_api_culqi_facade|false
	 */
	function createCulqi(){
		if(!$key=$this->man->get_key_item("privatekey")->get_data()){
			return false;
		}
		return new mwmod_mw_paymentapi_api_culqi_facade($key);
	}
}
?>