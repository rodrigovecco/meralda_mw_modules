<?php
class mwmod_mw_html_att_att implements mwmod_mw_html_att_interface{
	public $value;
	//aún no probado, se puede implementar para class, style, etc, funciona con setAtt
	public function setValue($value){
		$this->value=$value;
		return true;	
	}
	public function getValue(){
		return $this->value;	
	}
}
?>