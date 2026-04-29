<?php
abstract class mwmod_mw_culqi_client_abs extends mw_apsubbaseobj{
	/**
	 * COMPAT culqi-php >= 2.0.x: los métodos de Charges/Cards/Customers/Refunds
	 * envuelven internamente try/catch y, ante error, devuelven el mensaje
	 * (string) en lugar de lanzar excepción. Este helper normaliza el resultado:
	 * si no es un objeto, lanza una Exception con el cuerpo de error original
	 * (que en general es un JSON con merchant_message, user_message, etc.).
	 * Así el resto del código —escrito para la versión vieja que sí lanzaba—
	 * sigue funcionando sin cambios estructurales.
	 *
	 * @param mixed $result Lo retornado por la llamada a la API.
	 * @return object
	 * @throws Exception
	 */
	public static function normalizeCulqiResult($result){
		if(is_object($result)){
			return $result;
		}
		$rawErr=is_string($result)?$result:json_encode($result);
		if(!$rawErr){
			$rawErr='{"user_message":"Respuesta vacía de la pasarela de pago."}';
		}
		throw new Exception($rawErr);
	}
	private $user;
	private $testMode=false;
	public $mustCreate=false;
	private $paymentModule;
	private $paymentTestModule;
	public $error;
	public $clientObject;
	private $cardsMan;
	private $cards;
	public $createCardError;
	public $createCardErrorUserMessage;
	public $culqiError;
	public $newCardData;
	private $userPreferences;
	
	final function __get_priv_userPreferences(){
		if(!isset($this->userPreferences)){
			$this->userPreferences=$this->loadUserPreferences();
		}
		return $this->userPreferences; 	
	}
	function loadUserPreferences(){
		if(!$this->user){
			return false;	
		}
		if($this->isTestMode()){
			return $this->user->get_treedata_item("pref","culqitest");	
		}
		return $this->user->get_treedata_item("pref","culqi");	
	}
	
	final function __get_priv_cards(){
		if(!isset($this->cards)){
			$this->cards=new mwmod_mw_util_itemsbycod();
			$this->cards->isEnabledMethod="isEnabled";
			if($items=$this->load_cardsItems()){
				$this->cards->addItemsAssoc($items);	
			}
		}
		return $this->cards; 	
	}
	function load_cardsItems(){
		if(!$this->user){
			return false;	
		}
		if(!$man=$this->__get_priv_cardsMan()){
			return false;	
		}
		if(!$tblman=$man->get_tblman()){
			return false;	
		}
		if(!$query=$tblman->new_query()){
			return false;	
		}
		$query->where->add_where("user_id=".$this->user->get_id());
		if($this->isTestMode()){
			$query->where->add_where("test_mode=1");	
		}else{
			$query->where->add_where("test_mode=0");	
		}
		//echo $query->get_sql();
		return $man->get_items_by_query($query);
		

	}
	
	final function __get_priv_cardsMan(){
		if(!isset($this->cardsMan)){
			$this->cardsMan=$this->load_cardsMan();
		}
		return $this->cardsMan; 	
	}
	function load_cardsMan(){
		if($man=$this->mainap->get_submanager("sales")){
			return $man->culqiCardsMan;
			
		}
	}
	
	
	
	function createCulqi(){
		if(!$paymentModule=$this->getCulqi()){
			return false;	
		}
		$api=$paymentModule->newApi();
		if($culqi=$api->createCulqi()){
			return $culqi;	
		}
	}
	function loadClientObject(){
		if($clid=$this->getClientID()){
			return false;	
		}
		if(!$culqi=$this->createCulqi()){
			return false;	
		}
		try {
			$ch=self::normalizeCulqiResult($culqi->Customers->get($clid));
		} catch (Exception $e) {
			$this->error=$e;
			return false;
		}
		if(!$ch){
			return false;	
		}
		$this->clientObject=$ch;
		return $ch;
		
		
			
	}
	
	function createClientObject(){
		if(!$data=$this->getNewUserData()){
			return false;	
		}
		$data["metadata"]=array(
			"userID"=>$this->user->get_id(),
		);
		if(!$culqi=$this->createCulqi()){
			return false;	
		}
		
		try {
			$ch=self::normalizeCulqiResult($culqi->Customers->create($data));
		} catch (Exception $e) {
			$this->error=$e;
			return false;
		}
		if(!$ch){
			return false;	
		}
		if($ch->id){
			$cod=$this->getClientIDdatacod();
			$nd=array();
			$nd[$cod]=$ch->id;
			$this->user->tblitem->do_update($nd);	
		}
		return $ch;

	}
	function getNewUserData(){
		if(!$this->user){
			return false;	
		}
		$r=array(
			"first_name"=>$this->user->get_first_name(),
			"last_name"=>$this->user->get_last_name(),
			"email"=>$this->user->get_email(),
			"address"=>"Lima Peru",
			"address_city"=>"Lima",
			"country_code"=>"PE",
			"phone_number"=>"6505434800",
		
		);
		return $r;	
	}
		
	function getDebugData(){
		$data=array();
		if($this->user){
			$data["userID"]=$this->user->get_id();
				
		}
		$data["newuserdata"]=$this->getNewUserData();
		if($this->isTestMode()){
			$data["testMode"]=true;	
		}
		if($this->error){
			$data["errorMsg"]=$this->error->getMessage();		
		}
		return $data;
	}
	final function setTestMode($test=true){
		if($test){
			$this->testMode=true;
		}else{
			$this->testMode=false;
		}
			
	}
	function getClientID(){
		if(!$this->user){
			return false;	
		}
		$cod=$this->getClientIDdatacod();
		return $this->user->tblitem->get_data($cod);
	}
	function getClientIDdatacod(){
		if($this->isTestMode()){
			return "culqiclientidtest";	
		}
		return "culqiclientid";
	}
	function isTestMode(){
		return $this->__get_priv_testMode();	
	}
	
	final function setUser($user){
		$this->user=$user;
	}
	final function __get_priv_user(){
		return $this->user; 	
	}
	final function __get_priv_testMode(){
		return $this->testMode; 	
	}
	function getCulqi(){
		if($this->isTestMode()){
			return $this->__get_priv_paymentTestModule();	
		}
		return $this->__get_priv_paymentModule();	
	}
	final function __get_priv_paymentModule(){
		if(!isset($this->paymentModule)){
			$this->paymentModule=$this->load_paymentModule();
		}
		return $this->paymentModule; 	
	}
	final function __get_priv_paymentTestModule(){
		if(!isset($this->paymentTestModule)){
			$this->paymentTestModule=$this->load_paymentTestModule();
		}
		return $this->paymentTestModule; 	
	}
	function load_paymentModule(){
		return new mwmod_mw_paymentapi_api_culqi_man();
	}
	function load_paymentTestModule(){
		return new mwmod_mw_paymentapi_api_culqi_testman();
	}
	
	
}
?>