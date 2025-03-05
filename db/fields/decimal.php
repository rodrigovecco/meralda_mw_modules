<?php
class mwmod_mw_db_fields_decimal extends mwmod_mw_db_fields_num{
	var $DIGITS;
	var $DECIMAL_DIGITS;
	
	function __construct($cod,$DIGITS=10,$DECIMAL_DIGITS=2,$UNSIGNED=true,$DEFAULT=false){
		$this->Type="DECIMAL";
		$this->init_dec($cod,$DIGITS,$DECIMAL_DIGITS,$UNSIGNED,$DEFAULT);
			
	}
	
	function init_dec($cod,$DIGITS=10,$DECIMAL_DIGITS=2,$UNSIGNED=false,$DEFAULT=false){
		$this->init($cod);
		if($DIGITS=$DIGITS+0){
			$this->DIGITS=$DIGITS;	
		}
		$this->DECIMAL_DIGITS=$DECIMAL_DIGITS+0;	
		if($UNSIGNED){
			$this->UNSIGNED=true;	
		}else{
			$this->UNSIGNED=false;	
				
		}
		if($DEFAULT!==false){
			$this->DEFAULT=$DEFAULT+0;	
		}
	}

	
	
}
?>