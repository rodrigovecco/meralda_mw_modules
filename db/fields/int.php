<?php
class mwmod_mw_db_fields_int extends mwmod_mw_db_fields_num{
	var $UNSIGNED=false;
	var $AUTO_INCREMENT=false;

	function __construct($cod,$UNSIGNED=true,$Length=11,$DEFAULT=false){
		$this->Type="INT";
		$this->init_int($cod,$Length,$UNSIGNED,$DEFAULT);
	}
	function set_as_primary($AUTO_INCREMENT=true,$UNSIGNED=true){
		$this->AUTO_INCREMENT=$AUTO_INCREMENT;
		$this->UNSIGNED=$UNSIGNED;
	}
	
	function init_int($cod,$Length=false,$UNSIGNED=false,$DEFAULT=false){
		$this->init($cod);
		if($Length=$Length+0){
			$this->Length=$Length;	
		}
		if($UNSIGNED){
			$this->UNSIGNED=true;	
		}
		if($DEFAULT!==false){
			$this->DEFAULT=$DEFAULT+0;	
		}
	}
	function after_init(){
		$this->DEFAULT=0;	
	}

	
	
	
}
?>