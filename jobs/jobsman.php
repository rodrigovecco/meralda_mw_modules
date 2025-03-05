<?php
class  mwmod_mw_jobs_jobsman extends mw_apsubbaseobj{
	private $man;//app sub manager
	
	private $jobs_main_man;//mwmod_mw_jobs_jobsmainman
	private $cod;
	private $_items=array();
	function __construct($man){
		$this->init_jobs_man($man);
	}
	function log_on_exec($item,$result){
		if($man=$this->__get_priv_jobs_main_man()){
			return $man->log_on_exec($item,$result);
		}
	}
	function get_treedata_item_for_job($item,$code="data",$path=false){
		if(!$man=$this->__get_priv_jobs_main_man()){
			return false;
		}
		$p="jobs/".$item->cod;
		if($path){
			$p.="/".$path;	
		}
		return $this->jobs_main_man->get_treedata_item_for_man($this,$code,$p);
	}

	function exec_job_as_cron($cod){
		
		if($item=$this->get_item($cod)){
			return $item->exec_job_as_cron();
		}
	}
	function get_debug_data(){
		$r=array(
			"class"=>get_class($this),
		);
		if($items=$this->get_items()){
			foreach($items as $cod=>$item){
				$r["items"][$cod]=$item->get_debug_data();	
			}
		}
		return $r;
	}
	function register_job_exec($item){
		if(!$man=$this->__get_priv_jobs_main_man()){
			return false;
		}
		
		$this->jobs_main_man->register_job_exec($item);
	}
	final function add_item($item){
		if(!$item->cod){
			return false;	
		}
		if(!$cod=basename($item->cod)){
			return false;	
		}
		$this->_items[$cod]=$item;
		return $item;
	}
	final function get_items(){
		return $this->_items;
	}
	final function get_item($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		return $this->_items[$cod]??null;
	}
	final function init_jobs_man($man){
		$this->man=$man;
		$ap=$man->mainap;
		$this->cod=basename($man->__get_ap_submanager_cod());
		$this->set_lngmsgsmancod($man->lngmsgsmancod);	
	}
	final function set_jobs_main_man($man){
		if($this->jobs_main_man){
			return false;	
		}
		
		if($man){
			if(is_object($man)){
				if(is_a($man,"mwmod_mw_jobs_jobsmainman")){
					$this->jobs_main_man=$man;
					return $man;	
				}
			}
		}
	}
	function load_jobs_main_man(){
		if($man=$this->mainap->get_submanager("jobs")){
			return $man;	
		}
	}
	final function __get_priv_cod(){
		return $this->cod;	
	}
	final function __get_priv_man(){
		return $this->man;	
	}
	
	final function __get_priv_jobs_main_man(){
		if(isset($this->jobs_main_man)){
			return $this->jobs_main_man;	
		}
		if($man=$this->load_jobs_main_man()){
			if($this->set_jobs_main_man($man)){
				return $this->jobs_main_man;
			}
		}
		return false;
		
		
		
	}
	
	
}

?>