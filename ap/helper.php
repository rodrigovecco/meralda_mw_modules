<?php
//retrives unique instances of many aplication managers
class mwmod_mw_ap_helper extends mwmod_mw_ap_helper_abs{
	function __construct(){
		$this->init();
	}
	function iniConvertToMB($value) {
		$value=$value??"";
	    $unit = strtoupper(substr($value, -1));
	    $number = (int)$value;

	    switch ($unit) {
	        case 'G':
	            return $number * 1024;
	        case 'M':
	            return $number;
	        case 'K':
	            return $number / 1024;
	        default:
	            return $number / (1024 * 1024); // Assuming bytes
	    }
	}
	
}

?>