<?php
class mwmod_mw_html_att_js  extends mwmod_mw_html_att_att implements mwmod_mw_html_att_interface{
	//aún no probado, se puede implementar para class, style, etc, funciona con setAtt
	public $js;
	function __construct(){
		
	}
	function get_as_att($cod){
		$in="";
		if($this->js){
			$in=$this->js->get_as_js_val();	
		}
		return $cod."=\"".$in."\"";
	}
	function getJs(){
		if(!$this->js){
			$this->js=new mwmod_mw_jsobj_codecontainer();
			$this->js->contSeparator="";
		}
		return $this->js;	
	}
	function setJs($js=false){
		if(!$js){
			$js=new mwmod_mw_jsobj_codecontainer();	
			$js->contSeparator="";
		}
		$this->js=$js;
		return $this->js;	
	}
	
}
?>