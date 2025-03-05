<?php
class  mwmod_mw_paymentapi_api_niubiz_man extends mwmod_mw_paymentapi_abs_man{
	public $accesstoken;//loaded by req or retrived from sotred for specific order
	public $transactiontoken;
	public $accesstokenLoadedResult;
	public $debugHTML;
	public $sessionKeyInputData;//temp!!!
	public $sessiontokenResponse;
	public $sessiontoken;
	public $currency="PEN";
	public $orderData;
	public $validateTransactionData;
	public $transactionResponse;
	public $transactionResponseNotAutorized;
	public $transactionResponseRaw;
	public $transactionResponseCode;
	public $transactionResponseErrNo;
	public $transactionResponseSTATUS;
	public $transactionResponseACTION_DESCRIPTION;
	public $paymentInfoHuman;
	public $transactionResponseNotAutorizedACTION_DESCRIPTION;
	public $transactionResponseNotAutorizedTxtInfo;
	public $sessionKeyRawResponse;
	public $sessionKeyRawResponseInfo;

	function __construct(){
		$this->init("niubiz");
		$this->testMode=false;
		$this->name="Niubiz";
	}
	///validacion de pago
	function setAccesstoken($accesstoken){
		$this->accesstoken=$accesstoken;
	}
	function setTransactiontoken($transactiontoken){
		$this->transactiontoken=$transactiontoken;
	}
	function buildValidateTransactionData(){
		if(!$this->orderData){
			return false;
		}
		$this->validateTransactionData= new stdClass();
		$this->validateTransactionData->channel="web";
		$this->validateTransactionData->captureType="manual";
		$this->validateTransactionData->countable=true;
		$this->validateTransactionData->order=$this->orderData;
		return true;

	}
	function buildOrderData($purchaseNumber,$amount,$transactiontoken=false,$currency=false){
		if(!$currency){
			$currency=$this->currency;
		}
		if(!$transactiontoken){
			$transactiontoken=$this->transactiontoken;
		}
		if(!$transactiontoken){
			return false;
		}
		$this->orderData= new stdClass();
		$this->orderData->tokenId=$transactiontoken;
		$this->orderData->purchaseNumber=$purchaseNumber;
		$this->orderData->amount=$amount;
		$this->orderData->currency=$currency;
		return true;



	}
	function validateTransactiontoken(){
		unset($this->transactionResponse);
		if(!$this->validateTransactionData){
			return false;
		}
		$merchantId=$this->getMerchantID();
		$url = $this->getEndPointURL("api.authorization/v3/authorization/ecommerce/{$merchantId}");
		$key=$this->getAccesstoken();
		$header = array("Content-Type: application/json","Authorization: $key");
		$ch = curl_init();
		$request_body=json_encode($this->validateTransactionData);

		

		
		//echo "<textarea>$request_body</textarea>";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		//echo "<textarea>$response</textarea>";
		$this->transactionResponseRaw=$response;
		$this->transactionResponseErrNo=curl_errno($ch);


		if(!$this->transactionResponseErrNo){
			if($info = curl_getinfo($ch)){
				$this->transactionResponseCode=$info["http_code"];

				//mw_array2list_echo($info);
				if($info["http_code"]=="200"){
					if($this->transactionResponse=@json_decode(trim($response))){
						$paymentInfoHuman=array();

						if(is_object($this->transactionResponse)){
							if($this->transactionResponse->dataMap){
								if($this->transactionResponse->dataMap->CARD){
									$paymentInfoHuman[]="CARD: ".$this->transactionResponse->dataMap->CARD;

								}
								if($this->transactionResponse->dataMap->BRAND){
									$paymentInfoHuman[]="BRAND: ".$this->transactionResponse->dataMap->BRAND;

								}
								if($this->transactionResponse->dataMap->TRANSACTION_DATE){
									$paymentInfoHuman[]="FECHA: ".$this->getDateFormated($this->transactionResponse->dataMap->TRANSACTION_DATE);

								}
								
							}
							$this->paymentInfoHuman=implode("\n", $paymentInfoHuman);

							return true;
							//echo "<textarea>".vardump($this->transactionResponse)."</textarea>";

						}
					}
					
							
				}
			}
		}
		
		if($this->transactionResponseNotAutorized=@json_decode(trim($this->transactionResponseRaw))){
			//echo "<textarea>".."</textarea>";
			//vardump($this->transactionResponseNotAutorized); 
			if($this->transactionResponseNotAutorized->data){

				$this->transactionResponseNotAutorizedACTION_DESCRIPTION=$this->transactionResponseNotAutorized->data->ACTION_DESCRIPTION;
				$paymentInfoHuman=array();
				if($this->transactionResponseNotAutorized->data->CARD){
					$paymentInfoHuman[]="CARD: ".$this->transactionResponseNotAutorized->data->CARD;
				}
				if($this->transactionResponseNotAutorized->data->BRAND){
					$paymentInfoHuman[]="BRAND: ".$this->transactionResponseNotAutorized->data->BRAND;
				}
				if($this->transactionResponseNotAutorized->data->TRANSACTION_DATE){
					$paymentInfoHuman[]="FECHA: ".$this->getDateFormated($this->transactionResponseNotAutorized->data->TRANSACTION_DATE);
				}
				$this->transactionResponseNotAutorizedTxtInfo=implode(" ", $paymentInfoHuman);
			
							
				
			}

		}
		

	}
	function getDateFormated($dateNBformat){
		//220302175334
		if(!$dateNBformat){
			return "";
		}
		$y=substr($dateNBformat,0,2);
		$m=substr($dateNBformat,2,2);
		$d=substr($dateNBformat,4,2);
		$H=substr($dateNBformat,6,2);
		$i=substr($dateNBformat,8,2);
		$s=substr($dateNBformat,10,2);
		return "20$y-$m-$d $H:$i:$s";


		

	}
	function validateOrderLastTransactionResponse($purchaseNumber,$amount,$currency){
		if(!$purchaseNumber){
			return false;
		}
		if(!$amount){
			return false;
		}		
		if(!$this->transactionResponse){
			return false;
		}
		if(!is_object($this->transactionResponse)){
			return false;
		}
		if(!$this->transactionResponse->order){
			return false;
		}		
		if(!is_object($this->transactionResponse->order)){
			return false;
		}
		if(is_object($this->transactionResponse->dataMap)){
			$this->transactionResponseSTATUS=$this->transactionResponse->dataMap->STATUS;
			$this->transactionResponseACTION_DESCRIPTION=$this->transactionResponse->dataMap->ACTION_DESCRIPTION;
		}

		
		if($this->transactionResponse->order->purchaseNumber==$purchaseNumber){
			if($this->transactionResponse->order->amount==$amount){
				if($this->transactionResponse->order->currency==$currency){
					
					return true;
				}

			}

		}
		

	}




	/////	
	function prepareUIbuttonMode($ui){
		$util= new mwmod_mw_paymentapi_api_niubiz_uiutil();
		if($jsman=$util->get_js_man($ui)){
			
			$src="https://static-content.vnforapps.com/v2/js/checkout.js";
			if($this->testMode){
				$src="https://static-content-qas.vnforapps.com/v2/js/checkout.js?qa=true";
			}
			$item=new mwmod_mw_html_manager_item_jsexternal("niubiz",$src);
			$jsman->add_item_by_item($item);
		}
		
	}

	function prepareUIformMode($ui){
		$util= new mwmod_mw_paymentapi_api_niubiz_uiutil();
		if($jsman=$util->get_js_man($ui)){
			$src="https://static-content.vnforapps.com/elements/v1/payform.min.js";
			if($this->testMode){
				$src="https://pocpaymentserve.s3.amazonaws.com/payform.min.js";
			}
			$item=new mwmod_mw_html_manager_item_jsexternal("niubiz",$src);
			$jsman->add_item_by_item($item);
		}
		if($jsman=$util->get_css_man($ui)){
			$src="https://static-content.vnforapps.com/elements/v1/payform.min.css";
			if($this->testMode){
				$src="https://pocpaymentserve.s3.amazonaws.com/payform.min.css";
			}
			$item=new mwmod_mw_html_manager_item_css("niubiz",$src);
			$jsman->add_item_by_item($item);
		}
	}
	function setSessionKeyInputDataFromOrderData($purchaseNumber,$amount,$transactiontoken=false,$currency=false){
		$data=new stdClass();
		$data->channel="web";
		$data->amount=$amount;
		$data->antifraud=new stdClass();
		$data->antifraud->clientIp=$_SERVER['REMOTE_ADDR'];//reescrita en setMerchantDefineDataStd1
		$data->antifraud->merchantDefineData=new stdClass();
		$data->antifraud->merchantDefineData->MMD4="web";//correo del cliente
		$data->antifraud->merchantDefineData->MDD21="0";
		$data->antifraud->merchantDefineData->MDD32="Canl";//cl id
		$data->antifraud->merchantDefineData->MDD75="Registrado";//K
		//$data->antifraud->merchantDefineData->MDD77="Canl";//Días transcurridos desde la fecha de registro hasta la fecha actual o no considerar
		$this->resetSessionKeyInputData($data);

		/*
		MMD4: Correo del cliente
		MDD21: Enviar en 0
		MDD32: ID del cliente
		MDD75: Registrado o Invitado
		MDD77: Días transcurridos desde la fecha de registro hasta la fecha actual
		*/
	}
	function setMerchantDefineDataStd1($clientEmail,$clientID,$clientAccountDays=false,$clientIP=false){
		//sta fijado por niubiz para negocio de comida
		if(!$this->sessionKeyInputData){
			return false;
		}
		if(!$this->sessionKeyInputData->antifraud){
			return false;
		}
		if(!$this->sessionKeyInputData->antifraud->merchantDefineData){
			$this->sessionKeyInputData->antifraud->merchantDefineData=new stdClass();
		}
		//$this->sessionKeyInputData->antifraud->merchantDefineData->MMD4=$clientEmail."";//correo del cliente
		$this->sessionKeyInputData->antifraud->merchantDefineData->MDD4=$clientEmail."";//correo del cliente
		$this->sessionKeyInputData->antifraud->merchantDefineData->MDD32="".$clientID;//cl id
		$this->sessionKeyInputData->antifraud->merchantDefineData->MDD75="Registrado";//K
		if($clientAccountDays){
			$this->sessionKeyInputData->antifraud->merchantDefineData->MDD77="".$clientAccountDays;
			//Días transcurridos desde la fecha de registro hasta la fecha actual o no 	considerar		
		}
		if($clientIP){
			$this->sessionKeyInputData->antifraud->clientIp=$clientIP;
			//Días transcurridos desde la fecha de registro hasta la fecha actual o no 	considerar		
		}
		return true;


	}

	function resetSessionKeyInputData($data=false){
		if(!$data){
			$data=new stdClass();
		}
		$this->sessionKeyInputData=$data;
	}
	function getAccesstoken(){
		$this->loadAccesstoken();
		return $this->accesstoken;

	}
	function loadAccesstoken(){
		if(!isset($this->accesstokenLoadedResult)){
			$this->reloadAccesstoken();
		}
		return $this->accesstokenLoadedResult;

	}
	function createTestSessionKey(){
		
		$data=new stdClass();
		$data->channel="web";
		$data->amount=8.5;
		$data->antifraud=new stdClass();
		$data->antifraud->clientIp="24.252.107.29";
		$data->antifraud->merchantDefineData=new stdClass();
		$data->antifraud->merchantDefineData->MDD1="web";
		$data->antifraud->merchantDefineData->MDD2="Canl";
		$data->antifraud->merchantDefineData->MDD3="Canl";
		return $this->createSessionKey($data);



  




	}
	function createSessionKey($data=false){
		if($data){
			$this->resetSessionKeyInputData($data);
		}
		if(!$this->sessionKeyInputData){
			return false;
		}
		unset($this->sessiontoken);
		unset($this->sessiontokenResponse);
		$merchantId=$this->getMerchantID();
		$url = $this->getEndPointURL("api.ecommerce/v2/ecommerce/token/session/{$merchantId}");
		//echo $url."<br>";
		//$user=$this->getSecretUsername();
		//$pass=$this->getSecretPassword();

		$key=$this->getAccesstoken();


		$header = array("Content-Type: application/json","Authorization: $key");
		$ch = curl_init();
		$request_body=json_encode($this->sessionKeyInputData);
		//echo "<textarea>$request_body</textarea>";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		$this->sessionKeyRawResponse=$response;


		//echo "<textarea>$response</textarea>";
		if(!curl_errno($ch)){
			if($info = curl_getinfo($ch)){
				$this->sessionKeyRawResponseInfo=$info;

				if($info["http_code"]=="200"){
					if($this->sessiontokenResponse=@json_decode(trim($response))){
						if(is_object($this->sessiontokenResponse)){
							if($this->sessiontoken=trim($this->sessiontokenResponse->sessionKey."")){
								return $this->sessiontoken;
							}
							

						}
					}
				}
			}
			
			//mw_array2list_echo($info);
			
		  	//echo "<textarea>$data</textarea>";
		  
		}		

		curl_close($ch);
		return $this->sessiontoken;


	}
	function reloadAccesstoken(){
		unset($this->accesstoken);
		unset($this->accesstokenLoadedResult);
		$url = $this->getEndPointURL("api.security/v1/security");
		//echo $url."<br>";
		$user=$this->getSecretUsername();
		$pass=$this->getSecretPassword();


		$header = array("Content-Type: application/json");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		#curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		#curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$data = curl_exec($ch);
		$this->accesstokenLoadedResult=false;
		if(!curl_errno($ch)){
			if($info = curl_getinfo($ch)){
				if($info["http_code"]=="201"){
					$this->accesstokenLoadedResult=false;
					$this->accesstoken=trim($data."");
				}
			}
			
			//mw_array2list_echo($info);
			
		  	//echo "<textarea>$data</textarea>";
		  
		} else {
		   $this->debugHTML= 'Curl error: ' . curl_error($ch)."<br>";
		}

		curl_close($ch);
       
	}
	function get_js_init(){
		if(!$this->isEnabled()){
			return false;	
		}
		
		$js= new mwmod_mw_jsobj_codecontainer();
		//$key=$this->getPublicKey()."";
		//$js->add_cont("Culqi.publicKey = '".$js->get_txt($key)."';\n");
		return $js;
	
	}
	
	function new_debug_ui($cod,$parent){
		
		
		//return new mwmod_mw_paymentapi_api_culqi_debugui_main($this,$cod,$parent);
	}
	
	function checkForEnable(){
		
		if($this->getSecretUsername()){
			if($this->getSecretPassword()){
				return true;	
			}
				
		}
		
	}
	function getMerchantID(){
		return $this->get_key_item("merchantid")->get_data()."";	
	}
	function getSecretUsername(){
		return $this->get_key_item("username")->get_data()."";	
	}
	function getSecretPassword(){
		return $this->get_key_item("password")->get_data()."";	
	}
	
	
	
	function createNewApi(){
		return new mwmod_mw_paymentapi_api_niubiz_api($this);
	}
	function doLoadApiClasses(){
		 /*
		$file=dirname(__FILE__)."/apiclases/culqi.php";
		if(file_exists($file)){
			require_once($file);
		}
		*/
		
	}
	function getEndPointURL($sub=false){
		$r=$this->_getEndPointURL();
		if($sub){
			$r.="/".$sub;
		}
		return $r;
	}
	function _getEndPointURL(){
		if($this->testMode){
			return "https://apisandbox.vnforappstest.com";
		}else{
			return "https://apiprod.vnforapps.com";
		}
	}


}
?>