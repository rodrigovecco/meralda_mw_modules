<?php
abstract class mwmod_mw_data_fixcontent_absitem extends mw_apsubbaseobj{
	//no usado. en el futuro  se puede usar placeholders
	private $filepath;
	private $fixcontentman;
	private $phMatrix;
	private $phProcessor;
	private $phContentSetted;
	
	final function init_fix_content_item($filepath,$fixcontentman){
		$this->filepath=$filepath;
		$this->fixcontentman=$fixcontentman;
	
	}
	function setPHValue($value,$cod){
		if(!$cod){
			return false;	
		}
		if(!$item=$this->getPHSrc($cod)){
			return false;	
		}
		return $item->setValue($value);
			
	}
	function getPHSrc($cod=false){
		if(!$root=$this->getPHSrcRoot()){
			return false;	
		}
		if(!$cod){
			return $root;	
		}
		return $root->getItem($cod);	
	}
	function getPHSrcRoot(){
		return $this->__get_priv_phProcessor()->get_or_create_ph_src();	
	}
	
	function getContentPHMode(){
		if($this->setPHContent()){
			return $this->phProcessor->get_text_final();	
		}
		return $this->getContent();
	}
	function doSetPHContent(){
		if(!$php=$this->__get_priv_phProcessor()){
			return false;	
		}
		if($php->matrix){
			return true;	
		}
		if(!$matrix=$this->__get_priv_phMatrix()){
			return false;	
		}
		$php->set_matrix($matrix);
		
		return true;
	}
	final function setPHContent(){
		if(isset($this->phContentSetted)){
			return $this->phContentSetted;	
		}
		if($this->doSetPHContent()){
			$this->phContentSetted=true;	
		}else{
			$this->phContentSetted=false;		
		}
		return $this->phContentSetted;
	}
	//new 20240422
	function fileExists(){
		if($file=$this->fixcontentman->getFileFullPath($this->filepath,true)){
			return $file;
		}
	}
	function getContentHTML(){
		return $this->fixcontentman->getContentHTML($this->filepath);
	}
	function getContent(){
		return $this->fixcontentman->getContent($this->filepath);
	}
	function createPhProcessor(){
		$php=new mwmod_mw_placeholder_processor_phprocessor();
		return $php;
	}
	final function __get_priv_phProcessor(){
		if(!isset($this->phProcessor)){
			$this->phProcessor=$this->createPhProcessor();
		}
		
		return $this->phProcessor;
	}
	
	
	function createPhMatrix(){
		$txt=$this->getContent();
		return new mwmod_mw_placeholder_matrix_phmatrix($txt);	
	}
	final function __get_priv_phMatrix(){
		if(!isset($this->phMatrix)){
			$this->phMatrix=$this->createPhMatrix();	
		}
		
		return $this->phMatrix;
	}
	
	final function __get_priv_filepath(){
		return $this->filepath;
	}
	final function __get_priv_fixcontentman(){
		return $this->fixcontentman;
	}
	
	
	
}

?>