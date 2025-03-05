<?php
class mwmod_mw_db_fields_tinyint extends mwmod_mw_db_fields_int{
	
	/*
	A very small integer. The signed range is -128 to 127. The unsigned range is 0 to 255.
	*/
	function __construct($cod,$UNSIGNED=true,$Length=4,$DEFAULT=false){
		$this->Type="TINYINT";
		$this->init_int($cod,$Length,$UNSIGNED,$DEFAULT);
	}

	
	
	
}
?>