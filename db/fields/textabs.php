<?php
abstract class mwmod_mw_db_fields_textabs extends mwmod_mw_db_fields_abs{
	function is_text(){
		return true;
	}
	function after_init(){
		$this->DEFAULT="";	
	}
	function init_txt($cod,$Length=false,$DEFAULT=""){
		$this->init($cod);
		if($Length!==false){
			$this->Length=$Length;	
		}
		if($DEFAULT!==false){
			$this->DEFAULT=$DEFAULT;	
		}
	}

	
	
	
}
?>