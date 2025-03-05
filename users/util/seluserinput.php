<?php

class mwmod_mw_users_util_seluserinput extends mwmod_mw_datafield_select{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		$this->populate_users_list();	
	}
	function populate_users_list(){
		if(!$uman=$this->mainap->get_user_manager()){
			return false;	
		}
		$u=$uman->get_active_users();
		$this->create_optionslist($u);
	}
	/*
	function create_optionslist($options=false){
		if($options){
			if(is_a($options,"mwmod_mw_listmanager_listman")){
				$this->optionslist= $options; 	
				return 	$this->optionslist;
			}
		}
		
		$this->optionslist= new mwmod_mw_listmanager_listman($options); 	
		return 	$this->optionslist;
	}
	*/

	
}
?>