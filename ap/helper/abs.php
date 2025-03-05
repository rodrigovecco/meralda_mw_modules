<?php
//retrives unique instances of many aplication managers
abstract class  mwmod_mw_ap_helper_abs extends mw_apsubbaseobj{
	//in this abstract class, only declare private propierties and its retrive methods
	private $file_man;
	private $mimetypes;
	private $dateman;
	
	final function init(){
		$this->set_mainap();
	}
	
	function concat_lng_strings($list){
		if(!is_array($list)){
			return "";	
		}
		if(!sizeof($list)){
			return "";	
		}
		$last=array_pop($list);
		if(!sizeof($list)){
			return $last;	
		}
		$str=implode(", ",$list)." ".$this->lng_common_get_msg_txt("and","y")." ".$last;
		return $str;
		
	}

	final function __get_priv_dateman(){
		if(isset($this->dateman)){
			return $this->dateman;	
		}
		$this->dateman=false;
		if($fm=$this->mainap->get_submanager("dateman")){
			$this->dateman=$fm;
		}
		return $this->dateman;	
	}
	
	
	final function __get_priv_mimetypes(){
		if(isset($this->mimetypes)){
			return $this->mimetypes;	
		}
		$this->mimetypes=false;
		if($fm=$this->mainap->get_submanager("mimetypes")){
			$this->mimetypes=$fm;
		}
		return $this->mimetypes;	
	}
	
	final function __get_priv_file_man(){
		if(isset($this->file_man)){
			return $this->file_man;	
		}
		$this->file_man=false;
		if($fm=$this->mainap->get_submanager("fileman")){
			$this->file_man=$fm;
		}
		return $this->file_man;	
	}
	
}

?>