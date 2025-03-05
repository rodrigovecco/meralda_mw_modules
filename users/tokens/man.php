<?php
class  mwmod_mw_users_tokens_man extends mwmod_mw_manager_man{
	private $usersMan;
	public $tokenLength=100;
	public $appIDEnabled=false;
	public $currentAppID;//identificador único para instancias de aplicación en aplicaciones móviles
	public $numTranslationTbl;
	public $invalidtokenErrorCode;
	public $invalidtokenErrorMsg;
	
	function __construct($code,$tblName,$userMan){
		$this->inittokensMan($code,$tblName,$userMan);	
	}
	function setCurrentAppID($appID){
		//datos enviado por el usurio
		$this->currentAppID=$appID;
	}
	function setInvalidtokenEreror($code,$msg){
		$this->invalidtokenErrorCode=$code;
		$this->invalidtokenErrorMsg=$this->lng_get_msg_txt("INVALIDtoken_".$cod,$msg);
	}
	function gettokenByInput($inputStr){
		if(!$inputStr=$inputStr.""){
			$this->setInvalidtokenEreror("NoInput","Se requiere adjuntar un token");
			return false;	
		}
		if(!$info=$this->decodetokenInfo($inputStr)){
			$this->setInvalidtokenEreror("Format","El token enviado no tiene un formato válido");
			return false;	
		}
		if(!$token=$this->get_item($info["id"])){
			$this->setInvalidtokenEreror("deosNotExist","El token no existe");
			return false;	
		}
		if(!$info["token"]){
			$this->setInvalidtokenEreror("Format","El token enviado no tiene un formato válido");
			return false;	
		}
		if($info["token"]!==$token->get_data("token")){
			$this->setInvalidtokenEreror("NoCoincidence","El token enviado no coincide");
			return false;	
		}
		if($info["user_id"]!==$token->get_data("user_id")){
			$this->setInvalidtokenEreror("NoCoincidenceUser","El token enviado no pertenece al usuario");
			return false;	
		}
		if($this->appIDEnabled){
			if(!$this->currentAppID){
				$this->setInvalidtokenEreror("InvalidAPPIDNoData","Se requiere un identificador de instancia para validar token");
				return false;	
			}
			if($this->currentAppID!=$token->get_data("appid")){
				$this->setInvalidtokenEreror("InvalidAPPIDNoCoincidence","El token no coincide con el identificador de instancia");
				return false;	
			}
			
		}
		return $token;
		
	}
	function encodetoken($token){
		$r=array();
		$r[]=$this->numToSecretCh($token->get_id());
		$r[]=$token->get_data("token");
		$r[]=$this->numToSecretCh($token->get_data("user_id"));
		return implode(" ",$r);
	}
	function decodetokenInfo($tokenStr){
		
		if(!$a=explode(" ",$tokenStr."")){
			return false;	
		}
		$r=array(
		"id"=>$this->secretChToNum($a[0]),
		"token"=>$a[1],
		"user_id"=>$this->secretChToNum($a[2]),
		);
		return $r;
	}
	
	function getNumTranslationTbl(){
		$this->initNumTranslationTbl();
		return 	$this->numTranslationTbl;
	}
	final function initNumTranslationTbl(){
		if(!isset($this->numTranslationTbl)){
			$this->loadNumTranslationTbl();	
		}
	}
	function loadNumTranslationTbl(){
		//carga una cadena que trasnforma números en letras
		$this->numTranslationTbl="qGeJTcxlow";
	}
	
	function numToSecretCh($num){
		$this->initNumTranslationTbl();
		$str=round(abs($num+0))."";
		$r="";
		for($x=0;$x<strlen($str);$x++){
			$chN=substr($str,$x,1)+0;
			$r.=substr($this->numTranslationTbl,$chN,1);
		}
		return $r;
		
	}
	function secretChToNum($str){
		$this->initNumTranslationTbl();
		$str=$str."";
		$r="";
		for($x=0;$x<strlen($str);$x++){
			$chN=substr($str,$x,1);
			$rr=strpos($this->numTranslationTbl,$chN);
			if($rr===false){
				return false;
			}
			$r.=$rr;
		}
		return $r;
		
	}
	function newExpDate(){
		return false;//never expieres
	}
	function createNewtoken($user){
		$nd=array(
			"user_id"=>$user->get_id(),
			"token"=>$this->randomtokenStr(),
			"active"=>1,
			"creation_date"=>date("Y-m-d H:i:s"),
			"innerkey"=>$this->buildInnerKey($user->tblitem->get_data("pass")),
			"creation_ip"=>$_SERVER["REMOTE_ADDR"]
		);
		if($d=$this->newExpDate()){
			$nd["exp_date"]=$d;	
		}
		if($this->appIDEnabled){
			$nd["appid"]=$this->currentAppID;	
		}
		if($nitem=$this->tblman->insert_item($nd)){
			return $this->get_item($nitem->get_id());	
		}
		
		
	}
	function randomtokenStr(){
		$_alphaSmall = 'abcdefghijklmnopqrstuvwxyz';
		$_alphaCaps  = strtoupper($_alphaSmall);
		$_numerics   = '1234567890';
		$_specialChars = '~!#$%^*()-_=+{;:,|';
		$_container = $_alphaSmall.$_alphaCaps.$_numerics.$_specialChars;
		$password = '';
		$_len=$this->tokenLength;
		for($i = 0; $i < $_len; $i++) {
			$_rand = rand(0, strlen($_container) - 1);
			$password .= substr($_container, $_rand, 1);
		}
		return $password;
	}
	
	//determinación de innerkey a partir de dato almacenado de contraseña de usuario
	function buildInnerKey($string){
		$string=$string."";
		if(!$string){
			return false;	
		}
		$a1=array();
		$a2=array();
		$x=0;
		$odd=true;
		while($x<strlen($string)){
			$ch=substr($string,$x,1);
			if($odd){
				$a2[]=$ch;	
				$odd=false;
			}else{
				$odd=true;
				$a1[]=$ch;	
			}
			$x++;
		}
		$string1=implode("",$a1).implode("",$a2);
		return substr(hash('ripemd160', $string1),0,250);
		
	}
		
	
	
	
	function create_item($tblitem){
		
		$item=new mwmod_mw_users_tokens_item($tblitem,$this);
		return $item;
	}
	
	final function inittokensMan($code,$tblName,$userMan){
		$this->init($cod,$userMan->mainap,$tblName);
		$this->usersMan=$userMan;	
	}
	final function __get_priv_usersMan(){
		return $this->usersMan; 	
	}

}
?>