<?php
class mwmod_mw_users_jwt_access extends mwmod_mw_users_jwt_man{
	
	function validateTokenForUserFinal($user,$payload){

		if(!isset($payload["type"])){
			return false;
		}
		if($payload["type"]=="access"){
			return $payload;
			
		}
	
		return false;
	}
	
	
	function getPayloadForUserExtra($user,$payload){
		$payload["created_at"]=date("Y-m-d H:i:s");
		$payload["creation_ip"]=$_SERVER["REMOTE_ADDR"]??null;
		$payload["type"]="access";
		return $payload;
	}
	
	


}
?>