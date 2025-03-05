<?php
abstract class mwmod_mw_data_manabs extends mw_apsubbaseobj{
	private $rel_path;
	private $path_man;
	private $public_path_man;
	private $dataman_tree;
	private $dataman_json;
	private $dataman_xml;
	private $dataman_string;
	
	
	
	var $xmlEnabled=true;
	var $treeEnabled=true;
	var $strEnabled=true;
	var $jsonEnabled=true;
	
	function get_debug_data(){
		$r=array();
		$r["relpath"]=$this->getStrRelPath();
		return $r;
		
	}
	/**
	* Gets a treedata manager
	* @return mwmod_mw_data_tree_man
	*/
	function getItem($code="data",$path=false){
		return $this->getItemTree($code,$path);
	}
	function getItemTree($code="data",$path=false){
		if($m=$this->__get_priv_dataman_tree()){
			return $m->get_datamanager($code,$path);	
		}
	}
	function getItemStr($code="data",$path=false){
		if($m=$this->__get_priv_dataman_string()){
			return $m->get_datamanager($code,$path);	
		}
	}
	function getItemJson($code="data",$path=false){
		if($m=$this->__get_priv_dataman_json()){
			return $m->get_datamanager($code,$path);	
		}
	}
	function getItemXML($code="data",$path=false){
		if($m=$this->__get_priv_dataman_xml()){
			return $m->get_datamanager($code,$path);	
		}
	}
	
	function get_treedata_item($code="data",$path=false){
		return $this->getItemTree($code,$path);
	}
	function get_strdata_item($code="data",$path=false){
		return $this->getItemStr($code,$path);
	}



	function create_dataman_string(){
		if(!$this->strEnabled){
			return false;	
		}
		if(!$p=$this->getStrRelPath("str")){
			return false;		
		}
		
		$m= new mwmod_mw_data_str_man($p);
		return $m;
	}
	final function __get_priv_dataman_string(){
		if(!isset($this->dataman_string)){
			if(!$this->dataman_string=$this->create_dataman_string()){
				$this->dataman_string=false;	
			}
		}
		return $this->dataman_string;
	}



	function create_dataman_xml(){
		if(!$this->xmlEnabled){
			return false;	
		}
		if(!$p=$this->getStrRelPath("xml")){
			return false;		
		}
		
		$m= new mwmod_mw_data_xmltree_man($p);
		return $m;
	}
	final function __get_priv_dataman_xml(){
		if(!isset($this->dataman_xml)){
			if(!$this->dataman_xml=$this->create_dataman_xml()){
				$this->dataman_xml=false;	
			}
		}
		return $this->dataman_xml;
	}

	
	function create_dataman_json(){
		if(!$this->jsonEnabled){
			return false;	
		}
		if(!$p=$this->getStrRelPath("json")){
			return false;		
		}
		
		$m= new mwmod_mw_data_json_man($p);
		return $m;
	}
	final function __get_priv_dataman_json(){
		if(!isset($this->dataman_json)){
			if(!$this->dataman_json=$this->create_dataman_json()){
				$this->dataman_json=false;	
			}
		}
		return $this->dataman_json;
	}
	
	function create_dataman_tree(){
		if(!$this->treeEnabled){
			return false;	
		}
		if(!$p=$this->getStrRelPath("data")){
			return false;		
		}
		
		$m= new mwmod_mw_data_tree_man($p);
		return $m;
	}
	final function __get_priv_dataman_tree(){
		if(!isset($this->dataman_tree)){
			if(!$this->dataman_tree=$this->create_dataman_tree()){
				$this->dataman_tree=false;	
			}
		}
		return $this->dataman_tree;
	}
	
	
	
	function get_path($sub=false,$mode="userfiles"){
		if(!$p=$this->getStrRelPath($sub)){
			return false;	
		}
		return $this->mainap->get_sub_path($p,$mode);
		
	}
	function get_public_path($sub=false){
		return $this->get_path($sub,"userfilespublic");
	}

	function create_public_path_man(){
		if(!$p=$this->getStrRelPath()){
			return false;	
		}
		return $this->mainap->get_sub_path_man($p,"userfilespublic");
	}
	final function __get_priv_public_path_man(){
		if(!isset($this->public_path_man)){
			if(!$this->public_path_man=$this->create_public_path_man()){
				$this->public_path_man=false;	
			}
		}
		return $this->public_path_man;
	}
	
	
	function create_path_man(){
		if(!$p=$this->getStrRelPath()){
			return false;	
		}
		return $this->mainap->get_sub_path_man($p,"userfiles");
	}
	final function __get_priv_path_man(){
		if(!isset($this->path_man)){
			if(!$this->path_man=$this->create_path_man()){
				$this->path_man=false;	
			}
		}
		return $this->path_man;
	}
	
	
	final function set_rel_path($str){
		$this->rel_path=$str;
	}
	
	
	final function getStrRelPath($sub=false){
		if(!$this->rel_path){
			return false;	
		}
		$r=$this->rel_path;
		if($sub){
			$r.="/".$sub;	
		}
		return $r;
	}
	final function __get_priv_rel_path(){
		return $this->rel_path; 	
	}

	
	
}

?>