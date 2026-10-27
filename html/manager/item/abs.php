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
	// Cache-busting para las declaraciones de JS/CSS.
	//
	// Apache no manda Cache-Control ni ETag para los estaticos: solo
	// Last-Modified. Sin directivas, el navegador aplica frescura heuristica
	// (aprox. 10% del tiempo transcurrido desde Last-Modified) y puede pasar
	// horas sirviendo el archivo viejo desde cache sin revalidar. Al desplegar
	// un fix de JS el usuario sigue viendo el comportamiento anterior y no hay
	// forma de saberlo desde el servidor.
	//
	// Se agrega ?v=<filemtime> solo a los src locales que resuelven a un archivo
	// real del docroot. URLs externas, relativas y srcs con query propia se
	// devuelven sin tocar.
	function add_version_to_src($src){
		if(!$src){
			return $src;
		}
		$src=$src."";
		if(strpos($src,"?")!==false){
			return $src;
		}
		if(!preg_match("#^/+[^/]#",$src)){
			return $src;
		}
		if(!$docroot=$this->get_docroot_path()){
			return $src;
		}
		$file=$docroot.rtrim($src,"/");
		if(!is_file($file)){
			return $src;
		}
		if(!$t=@filemtime($file)){
			return $src;
		}
		return $src."?v=".$t;
	}
	final function get_docroot_path(){
		$p=false;
		if(isset($this->mainap)&&$this->mainap){
			$p=$this->mainap->public_path;
		}
		if(!$p){
			$p=$_SERVER['DOCUMENT_ROOT']??false;
		}
		if(!$p){
			return false;
		}
		return rtrim($p,"/\\");
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