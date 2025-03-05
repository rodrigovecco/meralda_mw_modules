<?php

abstract class mwmod_mw_helper_img_abs extends mw_apsubbaseobj{
	private $mainimgman;
	function load_main_img_man(){
		if($man=$this->mainap->get_submanager("imgman")){
			if(is_a($man,"mwmod_mw_helper_img_imgman")){
				return $man;
			}
		}
		$man= new mwmod_mw_helper_img_imgman();
		return $man;
	}
	final function set_main_img_man($man){
		if($man){
			if(is_object($man)){
				if(is_a($man,"mwmod_mw_helper_img_imgman")){
					$this->mainimgman=$man;
					return true;	
				}
	
			}
		}
	}
	final function __get_priv_mainimgman(){
		if(!isset($this->mainimgman)){
			$this->mainimgman=$this->load_main_img_man();	
		}
		return $this->mainimgman;	
	}
	
}
?>