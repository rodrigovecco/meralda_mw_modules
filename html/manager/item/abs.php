<?php
//rvh 2015-01-10 v 1

//para manejadores de javascripts, css, etc
abstract class mwmod_mw_html_manager_item_abs extends mw_apsubbaseobj{
	private $cod;
	var $extenal_src;
	var $bottom=false;
	public $man;
	final function init_item($cod){
		$this->cod=$cod;
		$this->set_mainap();
	}
	function setMan($man){
		if($this->man){
			return false;	
		}
		$this->man=$man;
		return true;	
	}
	function set_src_by_man_def($src,$man){
		$this->extenal_src=$man->def_path.$src;
	}
	function get_html_declaration(){
		return "";	
	}
	function is_bottom(){
		return $this->bottom;
	}
	
	function get_src(){
		if($this->extenal_src){
			return $this->extenal_src;	
		}
		return $this->cod;		
	}
	final function __get_priv_def_cod(){
		return $this->cod;	
	}
	final function __get_priv_cod(){
		return $this->cod;	
	}
	function __toString(){
		return $this->cod."";	
	}
	function isIE8orLower(){
		$s=$_SERVER['HTTP_USER_AGENT'];
		$pos=strpos($s,"MSIE");
		if($pos===false){
			return false;
		}
		$rest=substr($s,$pos+4)."";
		$parts=explode(" ",$rest);
		$v=$parts[0]+0;
		if($v<9){
			return true;	
		}
		return false;
	
	}
	

	
}
?>