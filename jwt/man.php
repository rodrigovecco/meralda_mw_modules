<?php

abstract class mwmod_mw_jwt_man extends mw_apsubbaseobj{
	private $cfgpath;
	public $appIDEnabled=false;
	public $currentAppID;//identificador único para instancias de aplicación en aplicaciones móviles
	//public $numTranslationTbl;
	public $invalidtokenErrorCode;
	public $invalidtokenErrorMsg;
	private $secretCfgDataMan;
	private $secretKey;

	public $tokenPin="123456789";

	public $neverExpiresAllowed=true;

	function __construct($cfgpath){
		$this->setCfgPath($cfgpath);
		
	}
	final function setCfgPath($cfgpath){
		$this->cfgpath=$cfgpath;
	}
	final function __get_priv_cfgpath(){
		return $this->cfgpath;
	}
	function setAPIID($appid){
		$this->currentAppID=$appid;
		$this->appIDEnabled=true;
	}

	




	
	
	
	
	
	function getNextExpired(){
		return time() + (60 * 60 * 24 * 90); // 90 days expiration
	}



    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    function createToken(array $payload) {
		if(!$payload=$this->constructPayload($payload)){
			return false;
		}
		
		return $this->do_createToken($payload);
	}
	function constructPayload(array $payload) {
		if(!is_array($payload)){
			$payload=array();
		}
		if(!isset($payload["exp"])){
			if(!$this->neverExpiresAllowed){
				$payload["exp"]=$this->getNextExpired();
			}
			
		}else{
			if(!is_numeric($payload["exp"])){
				$payload["exp"]=strtotime($payload["exp"]);
			}
			
		}
		if($this->appIDEnabled){
			$payload["appid"]=$this->currentAppID;
		}
		$payload["pin"]=$this->tokenPin;
		$payload["created_at"]=date("H:i:s d-m-Y");
		$payload["creation_ip"]=$_SERVER["REMOTE_ADDR"]??null;
		return $this->constructPayloadSub($payload);
		

	}
	function constructPayloadSub(array $payload) {
		//override in subclasses
		return $payload;
	}
	
	function do_createToken(array $payload) {
    	if(!$secretKey=$this->getSecretKey()){
    		return false;
    	}

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secretKey, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

	public function validateToken($token) {
		if(!$payload=$this->retriveTokenData($token)){
			return false;
		}
		if($this->appIDEnabled){
			if(!isset($payload["appid"])){
				$this->invalidtokenErrorCode="no_appid";
				$this->invalidtokenErrorMsg="App ID is required";
				return false;
			}
			if($payload["appid"]!=$this->currentAppID){
				$this->invalidtokenErrorCode="invalid_appid";
				$this->invalidtokenErrorMsg="App ID is not valid";
				return false;
			}
		}
		if(isset($payload["exp"])){
			if($payload["exp"] < time()){
				$this->invalidtokenErrorCode="expired";
				$this->invalidtokenErrorMsg="Token has expired";
				return false;
			}
		}else{
			if(!$this->neverExpiresAllowed){
				$this->invalidtokenErrorCode="no_exp";
				$this->invalidtokenErrorMsg="Token expiration is required";
				return false;
			}
		}
		if(!isset($payload["pin"])){
			$this->invalidtokenErrorCode="no_pin";
			$this->invalidtokenErrorMsg="Token PIN is required";
			return false;
		}
		if($payload["pin"]!=$this->tokenPin){
			$this->invalidtokenErrorCode="invalid_pin";
			$this->invalidtokenErrorMsg="Token PIN is not valid";
			return false;
		}
		return $this->validateTokenSub($payload);

		

	}
	function getTokenObjFromTokenString($tokenStr){
		if(!$payload=$this->validateToken($tokenStr)){
			return false;
		}
	
		if($obj=$this->createTokenObject($payload)){
			$obj->validated=true;
			return $obj;
		}
	}
	function validateTokenSub($payload){
		//override in subclasses
		return $payload;
	}
	function createTokenObject(array $payload) {
		$token=new mwmod_mw_jwt_token($this,$payload);
		return $token;
	}

    public function retriveTokenData($token) {
    	if(!$token){
    		return false;
    	}
    	if(!is_string($token)){
    		return false;
    	}
    	if(!$secretKey=$this->getSecretKey()){
    		return false;
    	}
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false; // Invalid token format
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secretKey, true));

        if ($expectedSignature !== $signatureEncoded) {
            return false; // Invalid signature
        }

        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
		
        return $payload;
    }
















	function getSecretKey(){
		return $this->__get_priv_secretKey();
	}

	function loadOrCreateSecretKey(){
		if(!$dm=$this->__get_priv_secretCfgDataMan()){
			return false;
		}
		if(!$item=$dm->getItemStr('secretkey')){
			return false;
		}
		if($v=$item->get_data()){
			return $v;
		}
		$new=$this->newSecretKey();
		$item->set_data($new);
		$item->save();
		return $new;
	}
	final function __get_priv_secretKey(){
		if(!isset($this->secretKey)){
			if(!$this->secretKey=$this->loadOrCreateSecretKey()){
				$this->secretKey=false;	
			}
		}
		return $this->secretKey;
	}




	function newSecretKey(){
		$secretKey = base64_encode(random_bytes(32));
		return $secretKey;
	}

	function createSecretCfgDataMan(){
		if(!$this->cfgpath){
			return false;
		}
		$m= new mwmod_mw_data_secret($this->cfgpath);
		return $m;
	}
	final function __get_priv_secretCfgDataMan(){
		if(!isset($this->secretCfgDataMan)){
			if(!$this->secretCfgDataMan=$this->createSecretCfgDataMan()){
				$this->secretCfgDataMan=false;	
			}
		}
		return $this->secretCfgDataMan;
	}

	
	


}
?>