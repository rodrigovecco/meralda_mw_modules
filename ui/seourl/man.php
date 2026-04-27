<?php
class mwmod_mw_ui_seourl_man extends mw_apsubbaseobj{
	private $maininterface;
	public $baseurl="";
	private $children;
	public $requestedPath;


	function __construct($maininterface){
		$this->setMainUI($maininterface);

	}
	final function setMainUI($maininterface){
		$this->maininterface=$maininterface;
	}
	final function __get_priv_maininterface(){
		return $this->maininterface;
	}
	function execServiceByREQUEST_URI($baseurl=false){
		if($baseurl){
			$this->baseurl=$baseurl;	
		}
		$b=$this->baseurl."";
		$sub=false;
		$req="";
		if($url_p=parse_url($_SERVER['REQUEST_URI'])){
			$req=trim($url_p['path'],"/");
		}
		if($l=strlen($b)){
			if(substr($req,0,$l)!=$b){
			
				return false;	
			}
		}
		
		$sub=trim(substr($req,$l),"/")."";
		$this->requestedPath=$sub;
		$this->execAsRoot($sub);
	}
	function execAsRoot($path){
		$codslist=array();
		$ch=$this->getChildByPath($path,$codslist);
		//mw_array2list_echo($codslist);
		$subpath="";
		if(sizeof($codslist)){
			$subpath=implode("/",$codslist);	
		}
		if($ch){
			
			$ch->execAsChild($subpath);	
		}else{
			$this->execAsDefault($path);	
		}
		
	}
	function execAsDefault($subpath){
		echo $subpath." not found.";
	}
	function getChildByPath($path,&$codslist=array()){
		if(!is_array($codslist)){
			$codslist=array();	
		}
		return $this->getChildBySrtSepCod($path,"/",$codslist);
	}
	function getChildBySrtSepCod($fullcod,$sep="/",&$codslist=array()){
		$fullcod=trim($fullcod."");
		$list=explode($sep,$fullcod);
		if(!is_array($codslist)){
			$codslist=array();	
		}
		if(sizeof($codslist)){
			$codslist=array();	
		}
		foreach($list as $c){
			if($c=trim($c)){
				$codslist[]=$c;	
			}
		}
		if(!sizeof($codslist)){
			return false;	
		}
		return $this->getChildByCodsList($codslist);
		
	}
	
	function getChildByCodsList(&$list=array()){
		if(!sizeof($list)){
			return false;	
		}
		$cod=array_shift($list);
		if(!$ch=$this->getChild($cod)){
			return false;	
		}
		if($ch->isFinal()){
			return $ch;	
		}
		if(!sizeof($list)){
			return $ch;
		}
		return $ch->getChildByCodsList($list);
		
	}
	final function getChild($cod){
		$this->initChildren();
		if(!$cod=$cod.""){
			return false;
		}
		if(isset($this->children[$cod])){
			return $this->children[$cod];
		}


	}
	function createChildren(){
		//returns array;
	}
	final function initChildren(){
		if(!isset($this->children)){
			$this->children=array();
			if($children=$this->createChildren()){
				foreach($children as $cod=>$child){
					$this->children[$cod]=$child;
				}
			}
		}
	}
	
}
?>