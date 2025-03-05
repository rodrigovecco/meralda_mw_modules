<?php
class  mwmod_mw_util_text extends mw_apsubbaseobj{
	function __construct(){
		
	}
	function time2id($timestamp=0){
		if(!$timestamp){
			$timestamp=time();
		}
		$letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
		$base = strlen($letters); 
		$baseNString = "";

		while ($timestamp > 0) {
		    // Calculate the remainder when dividing by the base
		    $remainder = $timestamp % $base;

		    // Prepend the corresponding character from the letters
		    $baseNString = $letters[$remainder] . $baseNString;

		    // Update the timestamp by integer division
		    $timestamp = (int)($timestamp / $base);
		}
		return $baseNString;
	}
	
}
?>