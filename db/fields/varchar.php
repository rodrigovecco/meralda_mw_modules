<?php
class mwmod_mw_db_fields_varchar extends mwmod_mw_db_fields_textabs{
	function __construct($cod,$Length=255,$DEFAULT=""){
		$this->Type="VARCHAR";
		$this->init_txt($cod,$Length,$DEFAULT);
	}

	
	
	
}
?>