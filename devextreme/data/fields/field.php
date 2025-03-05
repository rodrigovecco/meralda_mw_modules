<?php
class mwmod_mw_devextreme_data_fields_field extends mwmod_mw_devextreme_data_fields_abs{
	function __construct($cod,$sqlExp=false){
		$this->setCod($cod);
		if($sqlExp){
			$this->setSQLExp($sqlExp);	
		}
	}
	
}
?>