<?php
class mwmod_mw_users_jwt_man extends mw_apsubbaseobj{
	private $usersMan;
	function __construct($usersMan=false){
		if($usersMan){
			$this->setUsersMan($usersMan);
		}
		
	}

	public $cfgpath="jwtdefault";
	public $appIDEnabled=false;
	public $currentAppID;//identificador único para instancias de aplicación en aplicaciones móviles
	//public $numTranslationTbl;
	public $invalidtokenErrorCode;
	public $invalidtokenErrorMsg;


	private $secretCfgDataMan;
	private $secretKey;

	public $neverExpires=true;


	function createTokenForUser($user){
		if(!$payload=$this->getPayloadForUser($user)){
			return false;
		}
		return $this->createToken($payload);
	}
	function validateTokenForUserFinal($user,$payload){
		return $payload;
	}
	function validateTokenForUser($user,$token){
		if(!$user){
			return false;
		}
		if(!$payload=$this->validateToken($token)){
			return false;
		}
		if($user_id=$payload['user_id']??null){
			if($user_id==$user->get_id()){
				if($s=$payload['s']??null){
					if($s==$this->getUserSecret($user)){
						if($this->appIDEnabled){
							if(!$appid=$payload["appid"]??null){
								return false;
							}
							if($appid!=$this->currentAppID){
								return false;
							}

						}

						return $this->validateTokenForUserFinal($user,$payload);
					}
				}
			}
			
		}
		
		
		

	}
	function getUserSecret($user){
		$userPasswordHash=$user->get_password();
		$doubleHash = hash('sha256', $userPasswordHash);
		return $doubleHash;
	}
	function getPayloadForUserExtra($user,$payload){
		return $payload;
	}
	function getPayloadForUser($user){
		$payload=array();
		$payload["user_id"]=$user->get_id();
		if(!$this->neverExpires){
			$payload["exp"]=$this->getNextExpired();
		}
		
		$payload["s"]=$this->getUserSecret($user);
		if($this->appIDEnabled){
			$payload["appid"]=$this->currentAppID;
		}
		return $this->getPayloadForUserExtra($user,$payload);


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

    public function createToken(array $payload) {
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

        if(!$payload){
        	return false;
        }
        if($exp=$payload['exp']??null){
        	if($payload['exp'] < time()){
        		return false;
        	}
        }else{
        	if(!$this->neverExpires){
        		return false;
        	}
        }

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

	final function setUsersMan($usersMan){
		$this->usersMan=$usersMan;
	}
	final function __get_priv_usersMan(){
		return $this->usersMan;
	}
	


}
?>