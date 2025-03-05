<?php
class  mwmod_mw_facebook_app extends mw_apsubbaseobj {
	public $app_id;
	public $app_secret;
	public $app_version="v2.12";
	public $isdefault=false;
	public $name;
	private $currentUserSess;
	public $currentUserSessDataKey="_fb_current_user_data";

	function __construct(){
		
	}
	function getInfo(){
		$r=array(
			"id"=>$this->app_id,
			"name"=>$this->name,
		);
		return $r;
	}
	function unsetCurrentUserSess(){
		$this->getCurrentUserSess()->unsetSessData();	
	}
	function getCurrentUserSess(){
		return $this->__get_priv_currentUserSess();	
	}
	function getCurrentUserSessKey(){
		return 	$this->currentUserSessDataKey."_app_".$this->app_id;
	}
	function createCurrentUserSess(){
		$obj=new mwmod_mw_facebook_sess_user($this->getCurrentUserSessKey());
		return $obj;
	}
	final function __get_priv_currentUserSess(){
		if(!isset($this->currentUserSess)){
			$this->currentUserSess=$this->createCurrentUserSess();	
		}
		return $this->currentUserSess;
			
	}
	

	
	function newFacebookApp(){
		$app= new Facebook\FacebookApp($this->app_id,$this->app_secret);
		
		return $app;	
	}
	function newFBhelperWithApptoken(){
		return $this->newFBhelper($this->getGenericApitoken());	
	}
	function newFBhelper($token=false){
		$fb=$this->newFacebook($token);
		$h=new mwmod_mw_facebook_helper_fb($fb);
		return $h;
	}
	function newFacebook($token=false){
		$data=array(
		  'app_id' => $this->app_id,
		  'app_secret' => $this->app_secret,
		  //'enable_beta_mode' => $this->enable_beta_mode,
		  'default_graph_version' => $this->app_version,
		);
		if($token){
			$data['default_access_token'] = $token;	
		}
		
		$fb = new Facebook\Facebook($data);
		return $fb;
		
	}
	function get_name(){
		if($this->name){
			return $this->name;
		}
		return $this->app_id;
	}
	
	function isDefault(){
		return $this->isdefault;	
	}
	function getGenericApitoken(){
		return $this->app_id."|".$this->app_secret;	
	}
	
}
?>