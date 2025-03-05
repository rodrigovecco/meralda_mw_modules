<?php
class  mwmod_mw_jobs_debug_apsubman extends mw_apsubbaseobj implements mwmod_mw_jobs_apsubmaninterface{
	private $jobs_man;
	function __construct($ap){
		$this->set_mainap($ap);	
		$this->set_lngmsgsmancod("debug");	
	}
	final function __get_priv_jobs_man(){
		if(!isset($this->jobs_man)){
			if($man=$this->create_jobs_man()){
				$this->jobs_man=$man;	
			}
		}
		return $this->jobs_man;
	}
	function create_jobs_man(){
		$man=new mwmod_mw_jobs_jobsman($this);
		$man->add_item(new mwmod_mw_jobs_debug_job($man));
		$man->add_item(new mwmod_mw_jobs_debug_job1($man));
		//acá agregar jobs
		return $man;
	}
	
	function debug(){
		$man=$this->__get_priv_jobs_man();
		mw_array2list_echo($man->get_debug_data());
		
		
		
		//echo get_class($this);	
	}
	
}

?>