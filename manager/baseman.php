<?php

abstract class  mwmod_mw_manager_baseman extends mwmod_mw_manager_basemanabs{
	final function init($code,$ap){
		$this->setManCode($code);
		$this->set_mainap($ap);	
	}
	

}
?>