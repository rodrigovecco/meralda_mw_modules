<?php

class mwmod_mw_helper_img_imgman extends mw_apsubbaseobj{
	private $filemanager;
	function __construct($ap=false){
		$this->set_mainap($ap);
	}
	function new_manager_from_cfg($cfg=array(),$cod=false){
		$man=$this->new_manager($cod);
		$man->process_cfg($cfg);
		return $man;
	}
	function new_manager($cod=false){
		$man= new mwmod_mw_helper_img_imgsubman($this,$cod);
		return $man;	
	}

	function get_modes_ccb(){
		$r[0]=$this->lng_get_msg_txt("free","Libre");	
		$r[1]=$this->lng_get_msg_txt("fixed","Fijo");	
		$r[2]=$this->lng_get_msg_txt("relative","Relativo");	
		$r[3]=$this->lng_get_msg_txt("minimun","Mínimo");	
		$r[4]=$this->lng_get_msg_txt("maximun","Máximo");	
		return $r;
	}
	

	final function get_filemanager(){
		if(!isset($this->filemanager)){
			$this->filemanager=new mwmod_mw_helper_fileman();
		}
		return $this->filemanager;
	}
	
}
?>