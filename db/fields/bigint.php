<?php
class mwmod_mw_db_fields_bigint extends mwmod_mw_db_fields_int{
	
	/*
	A large integer. The signed range is -9223372036854775808 to 9223372036854775807. The unsigned range is 0 to 18446744073709551615.
	*/
	function __construct($cod,$UNSIGNED=true,$Length=20,$DEFAULT=false){
		$this->Type="BIGINT";
		$this->init_int($cod,$Length,$UNSIGNED,$DEFAULT);
	}

	
	
	
}
?>