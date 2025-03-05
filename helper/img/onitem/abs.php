<?php

abstract class mwmod_mw_helper_img_onitem_abs extends mw_apsubbaseobj{
	private $mainitem;
	public $tbldatakey="image";
	public $dir="img";
	public $tempdir="tempimg";
	private $imgsgr;
	public $title;
	public $titleByItemName=true;
	public $publicMode=true;
	public $updateDone=false;
	
	
	function upload_new_img_and_proc($input){
		if(!$this->checkUploaded($input)){
			return false;	
		}
		if(!$this->beforeUpload()){
			return false;	
		}
		if(!$new=$this->imgsgr->upload_new_img_and_proc($input)){
			return false;	
		}
		$this->saveFileName($new);
		return $new;
	}
	function checkUploaded($input){
		if(!$input){
			return false;	
		}
		if(is_array($input)){
			$inputname=$input["fileinputname"];	
		}else{
			$inputname=trim($input."");		
		}
		if(!$inputname=trim($inputname."")){
			return false;	
		}
		if(is_array($_FILES[$inputname])){
			return	$_FILES[$inputname];
		}
		return false;
	}
	function beforeUpload(){
		if(!$this->imgsgr){
			return false;	
		}
		$this->setImgsPathMan();
		$this->updateImgsIfChanged();
		
		if($pm=$this->newTempSubPathMan()){
			$this->imgsgr->set_temp_sub_path_man($pm);
			return true;
		}

		
			
	}
	function setImgsPathMan(){
		if(!$this->imgsgr->get_sub_path_man()){
			if($pm=$this->newSubPathMan()){
				$this->imgsgr->set_sub_path_man($pm);
				return true;
			}
				
		}
		return true;
			
	}
	function upload_new_img_and_proc_by_inputname($inputname){
		if(!$this->checkUploaded($inputname)){
			return false;	
		}
		if(!$this->beforeUpload()){
			return false;	
		}
		if(!$new=$this->imgsgr->upload_new_img_and_proc_by_inputname($inputname)){
			return false;	
		}
		$this->saveFileName($new);
		$this->updateDone=false;
		return $new;
		
	}
	final function setImgsGr($imgsgr){
		$this->imgsgr=$imgsgr;
	}
	final function setMainItem($mainitem){
		$this->mainitem=$mainitem;
	}
	function get_img_url($cod=false){
		$this->updateImgsIfChanged();
		if($gr=$this->__get_priv_imgsgr()){
			return $gr->get_img_url($cod);	
		}
		return "";	
	}
	function getInfo(){
		if(!$this->imgsgr){
			return false;	
		}
		$this->updateImgsIfChanged();
		$r=array();
		if($items=$this->imgsgr->get_items()){
			foreach($items as $cod=>$item){
				$r[$cod]=$this->parseElemInfo($item);	
			}
		}
		return $r;
	}
	function parseElemInfo($elem){
		$r=array();
		$r["src"]=$elem->get_url();
		return $r;

	}
	
	
	function delete(){
		if(!$this->imgsgr){
			return false;	
		}
		$this->setImgsPathMan();

		if(!$this->imgsgr->delete()){
			return false;	
		}
		$this->saveFileName("");
		$this->updateDone=false;
		return true;
	}
	function saveFileName($fn){
		return false;	
	}
	function updateImgsIfChanged(){
		if($this->updateDone){
			return false;	
		}
		$this->updateImgs();
		return true;
	}
	function updateImgs(){
		if(!$this->imgsgr){
			return false;	
		}
		$filename=$this->getFilename();
		$title=$this->getImagesTitle();
		$url= $this->getGeneralUrl();
		$pm=$this->newSubPathMan();//acá cambiar si no son publicas
		$this->imgsgr->set_info_and_url_by_public_path($url,$filename,$title,$pm);
		$this->updateDone=true;
			
	}
	function newSubPathMan(){
		return $this->mainitem->get_sub_path_man($this->dir,$this->publicMode);	
	}
	function newTempSubPathMan(){
		return $this->mainitem->get_sub_path_man($this->tempdir,false);	
	}

	function getGeneralUrl(){
		return $this->mainitem->get_public_url_path().$this->dir;
	}
	
	function setDir($main,$temp=false){
		//must be called in create
		if(!$temp){
			$temp="temp".$main;	
		}
		$this->dir=$main;
		$this->tempdir=$temp;
	}
	function getFilename(){
		return false;	
	}
	function getImagesTitle(){
		if($this->title){
			return $this->title;	
		}
		if($this->titleByItemName){
			return $this->mainitem->get_name();	
		}
		return false;
		
	}
	final function __get_priv_mainitem(){
		return $this->mainitem;
	}
	final function __get_priv_imgsgr(){
		return $this->imgsgr;
	}

	
	
	
}
?>