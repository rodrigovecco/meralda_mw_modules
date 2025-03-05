<?php
abstract class  mwmod_mw_paymentapi_abs_manabs extends mw_apsubbaseobj{
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
	private $logsPathMan;
	
	private $_isEnabled;
	final function init($cod){
		$this->cod=basename($cod);
		$this->set_mainap();	
	}
	function doLoadApiClasses(){
		//ver mwmod_mw_paymentapi_api_test_man	
	}
	function createNewApi(){
		return false;	
	}
	function new_debug_ui($cod,$parent){
		return false;
	}
	function get_id(){
		return $this->cod;	
	}
	function get_cod(){
		return $this->cod;	
	}
	function get_name(){
		if($this->name){
			return $this->name;		
		}
		return $this->get_cod();	
	}
	
	
	final function newApi(){
		$this->loadApiClasses();
		
		return $this->createNewApi();	
	}
	
	final function loadApiClasses(){
		if($this->apiclasesloaded){
			return;	
		}
		$this->doLoadApiClasses();
		$this->apiclasesloaded=true;
	}
	function isEnabled(){
		return $this->_isEnabled();
	}
	function loadIsEnabled(){
		$ok=false;
		if($this->useCfg){
			if($this->isEnabledByCfg()){
				$ok=true;
			}
		}else{
			$ok=$this->isEnabled;
		}
		if(!$ok){
			return false;	
		}
		return $this->checkForEnable();
		
	}
	function checkForEnable(){
		return false;	
	}
	
	final function _isEnabled(){
		if(!isset($this->_isEnabled)){
			if($this->loadIsEnabled()){
				$this->_isEnabled=true;	
			}else{
				$this->_isEnabled=false;	
			}
		}
		return $this->_isEnabled;
	}
	
	function isEnabledByCfg(){
		if($td=$this->get_treedata_item("cfg")){
			return $td->get_data("enabled");	
		}
	}
	
	function isDebug(){
		return $this->testMode;
	}
	function get_key_item($cod="publickey",$path=false){
		if($m=$this->get_keysdataman()){
			return $m->get_datamanager($cod,$path);	
		}
	}
	
	final function get_keysdataman(){
		if(isset($this->_keysdataman)){
			return 	$this->_keysdataman;
		}
		if($m=$this->load_keysdataman()){
			$this->_keysdataman=$m;
			return 	$this->_keysdataman;
		}
	}
	function load_keysdataman(){
		if(!$pm=$this->getDataPathMan()){
			return false;
		}
		if(!$root=$pm->get_path()){
			return false;	
		}
		$m= new mwmod_mw_data_str_mancuspath("keys",$root);
		return $m;
	}
	
	
	
	function get_strdata_item($cod="data",$path=false){
		if($m=$this->get_strdataman()){
			return $m->get_datamanager($cod,$path);	
		}
	}
	final function get_strdataman(){
		if(isset($this->_strdataman)){
			return 	$this->_strdataman;
		}
		if($m=$this->load_strdataman()){
			$this->_strdataman=$m;
			return 	$this->_strdataman;
		}
	}
	function load_strdataman(){
		if(!$p=$this->get_rel_path("str")){
			return false;
		}
		$m= new mwmod_mw_data_str_man($p);
		return $m;
	}
	
	function get_treedata_item($cod="data",$path=false){
		if($m=$this->get_treedataman()){
			return $m->get_datamanager($cod,$path);	
		}
	}
	final function get_treedataman(){
		if(isset($this->_treedataman)){
			return 	$this->_treedataman;
		}
		if($m=$this->load_treedataman()){
			$this->_treedataman=$m;
			return 	$this->_treedataman;
		}
	}
	function load_treedataman(){
		if(!$p=$this->get_rel_path("data")){
			return false;		
		}
		$m= new mwmod_mw_data_tree_man($p);
		return $m;
	}
	
	
	function get_rel_path($sub=false){
		if(!$p=$this->_get_rel_path()){
			return false;	
		}
		if(!$sub){
			return $p;	
		}
		return $p."/".$sub;
	}
	
	final function _get_rel_path(){
		if(!$cod=basename($this->cod)){
			return false;
		}
		$p="paymentapi/".$cod;
		return $p;
			
	}
	function getDataPathMan($sub=false){
		if(!$man=$this->__get_priv_dataPathMan()){
			return false;	
		}
		if(!$sub){
			return $man;	
		}
		return $man->get_sub_path_man($sub);
	}
	final function __get_priv_dataPathMan(){
		if(!isset($this->dataPathMan)){
			$this->dataPathMan=$this->loadDataPathMan();	
		}
		return $this->dataPathMan;
		
	}
	function loadDataPathMan(){
		if(!$p=$this->get_rel_path()){
			return false;	
		}
		return $this->mainap->get_sub_path_man($p,"instance");
			
	}
	final function __get_priv_logsPathMan(){
		if(!isset($this->logsPathMan)){
			$this->logsPathMan=$this->loadLogsPathMan();	
		}
		return $this->logsPathMan;
		
	}
	function loadLogsPathMan(){
		if(!$p=$this->get_rel_path("logs")){
			return false;	
		}
		return $this->mainap->get_sub_path_man($p,"root");
			
	}
	
	final function __get_priv_cod(){
		return $this->cod; 	
	}
	
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
		
		);
		return $r;	
	}
	
	function get_status_info_str($sep=" "){
		return implode($sep,$this->get_status_info_list());	
	}
	function get_status_info_list(){
		$r=array();
		if($this->isEnabled()){
			$r[]="Activo";	
		}else{
			$r[]="Inactivo";	
		}
		if($this->isDebug()){
			$r[]="Modo de pruebas";	
		}
		return $r;
	}
	

}
?>