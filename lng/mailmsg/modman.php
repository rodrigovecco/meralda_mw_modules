<?php
class  mwmod_mw_lng_mailmsg_modman extends mwmod_mw_lng_mailmsg_man{
	
	private $mod_cod;
	
	function __construct($mod_cod,$lngman){
		$this->init_mod_man($mod_cod,$lngman);	
	}
	final function init_mod_man($mod_cod,$lngman){
		if($mod_cod=basename($mod_cod)){
			$this->mod_cod=$mod_cod;	
		}
		$this->init($lngman);
	}
	final function __get_priv_mod_cod(){
		return $this->mod_cod;	
	}
	function get_item_files_subpath($item){
		if(!$cc=$this->mod_cod){
			return false;	
		}
		
		if(!$c=basename($item->cod)){
			return false;	
		}
		return "mailmsgsmodules/$cc/$c";	
	}
	

}
?>