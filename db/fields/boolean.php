<?php
class mwmod_mw_db_fields_boolean extends mwmod_mw_db_fields_int{
	
	function __construct($cod,$DEFAULT=false){
		$this->Type="BOOLEAN";
		$this->init_int($cod,1,true,$DEFAULT);
	}

	
	
	
}
?>