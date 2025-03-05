<?php
class  mwmod_mw_lng_helper extends mw_apsubbaseobj{
	function __construct($lngcode=false){
		if($lngcode){
			$this->set_lngmsgsmancod($lngcode);
		}
	}

}
?>