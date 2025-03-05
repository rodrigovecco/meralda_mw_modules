<?php
class mwmod_mw_culqi_client extends mwmod_mw_culqi_client_abs{
	function __construct($user=false){
		if($user){
			$this->setUser($user);
		}
	}
	function setDefCardID($id){
		if($this->userPreferences){
			$this->userPreferences->set_data($id."","defcardid");
			$this->userPreferences->save();
		}
	}
	function getDefCardID(){
		if($this->userPreferences){
			return $this->userPreferences->get_data("defcardid");
		}
	}
	
	/*
	function createCharge($sourceID,$amount,$params=array()){
		$this->culqiClient->culqiError=NULL;
		if(!is_array($params)){
			$params=array();	
		}
		$params["amount"]=$amount*100;
		$params["source_id"]=$sourceID;
		if(!$culqi=$this->createCulqi()){
			return false;	
		}
		try {
			$ch=$culqi->Refunds->create($params);
		} catch (Exception $e) {
			$this->culqiError=$e;
			return false;
		}
		if($ch){
			return $ch;	
		}
		
		
		
	}
	*/
	function refundPartialCharge($charge,$finalAmount,$reason="solicitud_comprador"){
		$this->culqiClient->culqiError=NULL;
		if(!$charge->current_amount){
			return false;	
		}
		
		$amount=$charge->current_amount-($finalAmount*100);
		if($amount<=0){
			return false;		
		}
		$params=array(
			"amount" => $amount,
    		"charge_id" => $charge->id,
   			"reason" => $reason
		);
		if(!$culqi=$this->createCulqi()){
			return false;	
		}
		try {
			$ch=$culqi->Refunds->create($params);
		} catch (Exception $e) {
			$this->culqiError=$e;
			return false;
		}
		if($ch){
			return $ch;	
		}
		
	}

	function refundFullCharge($charge,$reason="solicitud_comprador"){
		$this->culqiClient->culqiError=NULL;
		if(!$charge->current_amount){
			return false;	
		}
		$params=array(
			"amount" => $charge->current_amount,
    		"charge_id" => $charge->id,
   			"reason" => $reason
		);
		if(!$culqi=$this->createCulqi()){
			return false;	
		}
		try {
			$ch=$culqi->Refunds->create($params);
		} catch (Exception $e) {
			$this->culqiError=$e;
			return false;
		}
		if($ch){
			return $ch;	
		}
		
	}
	function captureCharge($id){
		$this->culqiClient->culqiError=NULL;
		if(!$culqi=$this->createCulqi()){
			return false;	
		}
		try {
			$ch=$culqi->Charges->getCapture($id);
		} catch (Exception $e) {
			$this->culqiError=$e;
			return false;
		}
		if($ch){
			return $ch;	
		}
			
	}
	
	function getCharge($id){
		$this->culqiClient->culqiError=NULL;
		if(!$culqi=$this->createCulqi()){
			return false;	
		}
		try {
			$ch=$culqi->Charges->get($id);
		} catch (Exception $e) {
			$this->culqiError=$e;
			return false;
		}
		if($ch){
			return $ch;	
		}
			
	}
	
	function createCardFromtoken($token,$params=array()){
		if(!$token=$token.""){
			return false;	
		}
		if(!$clid=$this->getClientID()){
			return false;	
		}
		if(!$culqi=$this->createCulqi()){
			return false;	
		}
		$params=array(
			"customer_id" => $clid,
			"token_id" => $token
		);
		try {
			$ch=$culqi->Cards->create($params);
		} catch (Exception $e) {
			$this->createCardError=$e;
			if($e){
				if(@$msg=json_decode($e->getMessage())){
					if($msg){
						if(is_object($msg)){
							$this->createCardErrorUserMessage=$msg->user_message."";	
						}
					}
				}
			}
			return false;
		}
		if(!$ch){
			return false;	
		}
		$card_brand="";
		$card_number="";
		$last_four="";
		$name="xxx";
		if($ch->source){
			$card_number=$ch->source->card_number."";
			$last_four=$ch->source->last_four."";
			$name=$card_number;
			if($ch->source->iin){
				$card_brand=$ch->source->iin->card_brand."";
				$name=$card_brand." ".$card_number;
			}
		}
		
		
		//$name=$ch->token->token
		$nd=array(
			"user_id"=>$this->user->get_id(),
			"name"=>$name,
			"customer_id"=>$clid,
			"card_id"=>$ch->id,
			"card_brand"=>$card_brand,
			"card_number"=>$card_number,
			"last_four"=>$last_four,
		);
		if(@$info=json_encode($ch)){
			$nd["info"]=$info;	
		}
		if($this->isTestMode()){
			$nd["test_mode"]=1;		
		}
		$this->newCardData=$nd;
		return $this->cardsMan->insert_item($nd);
		//return $ch;


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
		if($this->createCardError){
			$data["createCardErrorMsg"]=$this->createCardError->getMessage();		
		}
		if($this->newCardData){
			$data["newCardData"]=$this->newCardData;		
		}
		
		return $data;
	}
	function clientExists(){
		//tal vez se deber{ia verificar que es válido!!!
		if($this->getClientID()){
			return true;	
		}
		return false;	
	}
	
}
?>