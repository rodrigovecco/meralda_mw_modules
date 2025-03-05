<?php

class mwmod_mw_helper_img_onitem_itemimgs extends mwmod_mw_helper_img_onitem_abs{
	function __construct($mainitem,$imgsgr=false){
		$this->setMainItem($mainitem);
		if($imgsgr){
			$this->setImgsGr($imgsgr);
		}
	}
	function saveFileName($fn){
		if(!$this->tbldatakey){
			return false;	
		}
		$nd=array($this->tbldatakey=>$fn."");
		$this->mainitem->tblitem->do_update($nd);
		return true;
	}
	
	function getFilename(){
		if(!$this->tbldatakey){
			return "";	
		}
		return $this->mainitem->get_data($this->tbldatakey)."";
	}
	
	
}
?>