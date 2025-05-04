<?php
/**
 * Clase para los gestores de la aplicación.
 *
 * @property mwmod_mw_ap_def $mainap Aplicación principal.
 * 
 * Se puede usar para mejorar la calidad de interpretación de código para los IDEs.
 * DELCARAR propertys en la clase EXTENDIDA para que los IDEs puedan interpretarlos. 
 */
class  mwmod_mw_ap_util_submanagers{
	
	private $mainap;
	
	function __construct($mainap){
		$this->setMainAp($mainap);
	}
	
	final function getMainAp(){
		return $this->mainap;	
	}
	
	final function setMainAp($mainap){
		$this->mainap=$mainap;	
	}

	function __get($name){
		if(!$name){
			return false;	
		}
		if(!is_string($name)){
			return false;	
		}
		return $this->getMainAp()->get_submanager($name);
		
	}

}