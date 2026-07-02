<?php

class mwmod_mw_helper_img_img extends mwmod_mw_helper_img_abs{
	private $cod="img";
	private $current_filename;
	private $img_path;
	private $url;
	var $def_url;
	var $title;
	var $active=true;
	public $isMainSrc=false;
	public $group;
	var $cfg=array();

	private $bucket;
	private $bucketPath;
	
	function __construct($cod=false){
		$this->init($cod);	
	}
	final function setBucketMode($bucket,$bucketPath=false){
		if(!$bucket){
			return false;	
		}
		$this->bucket=$bucket;
		if($bucketPath){
			$this->bucketPath=$bucketPath;	
		}
		return true;
	}
	final function __get_priv_bucket(){
		if(isset($this->bucket)){
			return $this->bucket;
		}
		return false;
	}
	final function __get_priv_bucketPath(){
		if(isset($this->bucketPath)){
			return $this->bucketPath;
		}
		return false;
	}

	function isBucketMode(){
		if($this->__get_priv_bucket()){
			return true;	
		}
		return false;

	}
	function getBucketUrl($expires=300){
		if(!$bucket=$this->__get_priv_bucket()){
			return false;	
		}
		if(!$bucketPath=$this->__get_priv_bucketPath()){
			return false;	
		}
		$filename=$this->current_filename;
		
		if(!$filename){
			return false;	
		}
		if(!$filename=basename($filename)){
			return false;
		}
		$ops=array();
		$ops["inline"]=true;
		$ops["filename"]=$filename;
		$ops["contentType"]="image/jpeg";
		if(!$url=$bucket->getObjectTempURL($bucketPath."/".$filename,$expires,$ops)){
			return false;	
		}
		return $url;
		
	}
	function outputBucketMode(){
		//ob_end_flush();
		//die("Bucket mode output not implemented yet");
		ob_start();
		$contet=$this->getBucketContent();
		if(!$contet){
			ob_end_clean();
			return false;	
		}
		ob_end_clean();
		header ('Content-length: ' .strlen($contet));
		header ('Content-type: image/jpeg');
		echo $contet;



		return true;
	}
	function getBucketContent(){
		if(!$bucket=$this->__get_priv_bucket()){
			return false;	
		}
		if(!$bucketPath=$this->__get_priv_bucketPath()){
			return false;	
		}
		$filename=$this->current_filename;
		
		if(!$filename){
			return false;	
		}
		if(!$filename=basename($filename)){
			return false;
		}
		if(!$content=$bucket->getObject($bucketPath."/".$filename)){
			return false;	
		}
		return $content;
	}


	function getMainSrcImgItem(){
		if($this->group){
			if($this->group->mainSrcImgItem){
				if($this->group->mainSrcImgItem->cod!=$this->cod){
					return $this->group->mainSrcImgItem;
				}
			}
		}
	}

	function fileExistsOrCanBeCreated(){
		if($file=$this->get_file_full_path_if_ok()){
			return true;
		}
		if($src=$this->getMainSrcImgItem()){
			
			if($filesrc=$src->get_file_full_path_if_ok()){
				
				return true;	
			}
		}
		return false;
		
			
	}
	function get_url_if_ok($params=false){
		if($this->fileExistsOrCanBeCreated()){
			return $this->get_url($params);	
		}
	}
	function createFileFromOtherSrc($cod){
		if(!$cod){
			return false;	
		}
		if($this->cod==$cod){
			return false;	
		}
		
		if(!$this->group){
			
			return false;	
		}
		if($src=$this->group->get_item($cod)){
			
			if($filesrc=$src->get_file_full_path_if_ok()){
				
				if($subman=$this->new_img_subman()){
					if($n=$subman->copy_from_file($filesrc)){
						$this->set_current_filename($n);
						return 	$this->get_file_full_path_if_ok();
					}
				}
			}
		}
	}
	function createFileFromMainSrc(){
		if(!$src=$this->getMainSrcImgItem()){
			return false;	
		}
		if(!$filesrc=$src->get_file_full_path_if_ok()){
			return false;	
		}
		if($subman=$this->new_img_subman()){
			if($n=$subman->copy_from_file($filesrc)){
				$this->set_current_filename($n);
				return 	$this->get_file_full_path_if_ok();	
			}
		}
		
	}

	function getFullFileOrCreateFromMainSrc($otherCode=false){
		
		if(!$file=$this->get_file_full_path_if_ok()){
			if(!$file=$this->createFileFromMainSrc()){
				if($otherCode){
					return $this->createFileFromOtherSrc($otherCode);	
				}
				return false;
			}
		}
		
			
		if($file){
			return $file;
			

		}	
	}
	
	function output(){
		if($this->isBucketMode()){
			return $this->outputBucketMode();
		
		}
		

		if(!$file=$this->get_file_full_path_if_ok()){
			$file=$this->createFileFromMainSrc();	
		}
			
		if($file){
			ob_end_clean();
			header ('Content-length: ' .filesize($file));
			header ('Content-type: image/jpeg');
			readfile ($file);

		}	
			
	}
	function setGroup($group){
		$this->group=$group;	
	}
	function setAsMain(){
		$this->isMainSrc=true;//used to creat missing imges	
	}
	
	function setModeFree($axis="B"){
		//$axis: H,W,B
		return $this->setMode(0,$axis);	
	}
	function setModeFixed($axis="B"){
		//$axis: H,W,B
		return $this->setMode(1,$axis);	
	}
	function setModeRelative($axis="H"){
		//$axis: H,W,B
		return $this->setMode(2,$axis);	
	}
	function setModeMin($axis="B"){
		//$axis: H,W,B
		return $this->setMode(3,$axis);	
	}
	function setModeMax($axis="B"){
		//$axis: H,W,B
		return $this->setMode(4,$axis);	
	}
	function setHeight($dim){
		return $this->setDim($dim,"H");
		
	}
	function setWidth($dim){
		return $this->setDim($dim,"W");
		
	}
	function setDim($dim,$axis="B"){
		$dim=$dim+0;
		return $this->setCfgParam($dim,$axis,"dim");
	}

	function setMode($mode,$axis="H"){
		$mode=$mode+0;
		return $this->setCfgParam($mode,$axis,"mode");
	}
	private function setCfgParam($val,$axis="H",$param=false){
		if($param!="dim"){
			$param="mode";	
		}
		$chH=false;
		$chW=false;
		if($axis=="H"){
			$chH=true;
		}
		if($axis=="W"){
			$chW=true;
		}
		if($axis=="B"){
			$chH=true;
			$chW=true;
		}
		if($chH){
			$this->cfg["height"][$param]=$val;
		}
		if($chW){
			$this->cfg["width"][$param]=$val;
		}
		
	}
	
	function is_active(){
		return $this->active;	
	}
	function new_img_subman(){
		if(!$main=$this->mainimgman){
			return false;
		}
		
		if(!$path=$this->img_path){
			return false;	
		}
		$cfg=$this->cfg;
		if(!$subman=$main->new_manager_from_cfg($cfg)){
			return false;	
		}
		$subman->set_img_path($path);
		if($this->current_filename){
			$subman->current_filename=	$this->current_filename;
		}
		return $subman;
	
		//if($subman=$main->

	}

	
	function get_debug_data(){
		$r=array();
		$r["src"]=$this->get_url();
		$r["title"]=$this->get_title();
		$r["html"]=$this->get_html();
		$r["imgpath"]=$this->img_path;
		$r["file_full_path"]=$this->get_file_full_path();
		
		
		$r["cfg"]=$this->get_cfg();
		
		return $r;
	}
	function get_title(){
		return $this->title."";	
	}
	function get_file_full_path_if_ok(){
		if(!$p=$this->get_file_full_path()){
			return false;	
		}
		if(is_file($p)){
			if(file_exists($p)){
				return $p;	
			}
		}
	}
	function get_file_full_path(){
		if($this->img_path){
			if($this->current_filename){
				return $this->img_path."/".$this->current_filename;	
			}
		}
	}
	function get_img_elem(){
		$title=htmlentities($this->get_title());
		if($url=$this->get_url()){
			$elem=new mwmod_mw_html_img($url);
			$elem->set_title($title);
			return $elem;
		}
			
	}
	
	function get_html(){
		$title=htmlentities($this->get_title());
		if($url=$this->get_url()){
			return "<img src='$url' title='$title' alt='$title'>";	
		}
		return "";
	}
	function set_def_url($url){
		$this->def_url=$url;
	}
	function set_title($title){
		$this->title=$title;
	}
	function get_url($params=false){
		if($this->url){
			return $this->url;	
		}
		return $this->def_url;
	}
	function get_cfg(){
		return $this->cfg;	
	}
	final function __get_priv_img_path(){
		return $this->img_path;	
	}
	final function __get_priv_url(){
		return $this->url;	
	}
	final function __get_priv_current_filename(){
		return $this->current_filename;	
	}
	
	final function set_current_filename($fn=false){
		
		$this->current_filename=false;
		if($fn){
			$this->current_filename=basename($fn);
			return true;
		}
		
	}
	final function set_url($url){
		$this->url=$url;	
	}
	
	final function set_img_path($path){
		$this->img_path=$path;	
	}
	function set_cfg($cfg=array()){
		$this->cfg=$cfg;
	}
	final function init($cod=false){
		if($cod){
			$this->cod=$cod;	
		}
	}
	final function __get_priv_cod(){
		return $this->cod;	
	}
	
}
?>