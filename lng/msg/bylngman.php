<?php
class  mwmod_mw_lng_msg_bylngman extends mwmod_mw_lng_msg_lngmanabs{
	private $lng;
	function __construct($lng,$man){
		$lng=$lng;
		$lngcod=$lng->code;
		$this->init($lngcod,$man);	
	}

}
?>