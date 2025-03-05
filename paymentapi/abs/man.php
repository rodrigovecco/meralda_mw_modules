<?php
abstract class  mwmod_mw_paymentapi_abs_man extends mwmod_mw_paymentapi_abs_manabs{
	/*
	private $cod;
	private $_strdataman;
	private $_keysdataman;
	private $_treedataman;
	private $dataPathMan;
	public $testMode=false;
	public $isEnabled=true;
	public $useCfg=false;
	private $apiclasesloaded=false;
	public $name;
	*/
	function get_debug_data(){
		$r=array(
			"cod"=>$this->cod,
			"name"=>$this->get_name(),
			"testMode"=>$this->testMode,
			"isEnabled"=>$this->isEnabled,
			"useCfg"=>$this->useCfg,
			"class"=>get_class($this),
			"isDebug"=>$this->isDebug(),
			"isEnabled"=>$this->isEnabled(),
			"paths"=>array(
				"data"=>$this->dataPathMan->get_sub_path(),
				"logs"=>$this->logsPathMan->get_sub_path(),
			
			),
			"keys"=>$this->get_debug_data_keys_files(),
			"cfgfile"=>$this->get_treedata_item("cfg")->get_file_full_path()
			
			
		
		);
		return $r;	
	}
	function get_debug_data_keys_files(){
		$r=array();
		$cods=explode(",","publickey,privatekey");
		foreach($cods as $c){
			$r[$c]=array();
			if($di=$this->get_key_item($c)){
				$r[$c]["file"]=$di->get_file_full_path();
			}
		}
		return $r;
	}
	function new_debug_ui($cod,$parent){
		
		
		return new mwmod_mw_paymentapi_debugui_mod_main($this,$cod,$parent);
	}
	
	

}
?>