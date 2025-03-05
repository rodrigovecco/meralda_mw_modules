<?php
abstract class  mwmod_mw_facebook_manabs extends mwmod_mw_manager_baseman{
	public $jsappversion="v2.12";
	
	private $fbApp;
	
	public $debugMode=false;
	public $jsSDKurlProd="https://connect.facebook.net/es_LA/sdk.js";
	public $jsSDKurlDebug="https://connect.facebook.net/en_US/sdk/debug.js";
	function initFB($code,$ap){
		$this->init($code,$ap);
		$this->enable_treedata();
	}
	function isEnabled(){
		if(!$td=$this->get_treedata_item("cfg")){
			return false;
		}
		if(!$td->get_data("enabled")){
			return false;	
		}
		if($this->getAppID()){
			return true;	
		}
		return false;
			
	}
	function secretOK(){
		if($this->isEnabled()){
			if($this->getAppSecret()){
				return true;	
			}
				
		}
		return false;
	}
	function getDefaultGraphVersion(){
		return $this->getJSAppVersion();	
	}
	final function __get_priv_fbApp(){
		if(!isset($this->fbApp)){
			$this->fbApp=$this->createFBappDef();
		}
		return $this->fbApp; 	
	}
	function createFBappDef(){
		$app=new mwmod_mw_facebook_app();
		$app->app_id=$this->getAppID();
		$app->app_secret=$this->getAppSecret();
		$app->isdefault=true;
		if($v=$this->getDefaultGraphVersion()){
			$app->app_version=$v;
		}
		return $app;
		
	}
	/*
	
	function load_packageTypes(){
		return new mwap_pastipan_sales_packagetypes_man($this);
	}
	function getInitJS($jscontainer=false){
		
		if(!$jscontainer){
			$jscontainer=new mwmod_mw_jsobj_script();	
		}
		$params=$this->getJsInitParams();
		$jscontainer->add_cont("window.fbAsyncInit = function() {");
		$jscontainer->add_cont("FB.init(".$params.");\n");
		$jscontainer->add_cont("};\n");
		
		$jscontainer->add_cont("(function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = '".$this->getjsSDKurl()."';
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));");
		
		
		return $jscontainer;
	}
	*/
	function getJsFMManObj($js=false){
		if(!$js){
			$js=new mwmod_mw_jsobj_newobject("mw_fb_man");	
		}
		$js->set_prop("fb.url",$this->getjsSDKurl());
		$js->set_prop("fb.initparams",$this->getJsInitParams());
		return $js;
			
	}
	function getJsInitParams($js=false){
		if(!$js){
			$js=new mwmod_mw_jsobj_obj();	
		}
		$js->set_prop("appId",new mwmod_mw_jsobj_str($this->getAppID()));
		//$js->set_prop("cookie",true);
		$js->set_prop("xfbml",true);
		$js->set_prop("version",$this->getJSAppVersion());
		return $js;	
	}
	function getAppID(){
		if($td=$this->get_treedata_item("keys")){
			return $td->get_data("appId")."";	
		}
	}
	function getAppSecret(){
		if($td=$this->get_treedata_item("keys")){
			return $td->get_data("appsecret")."";	
		}
	}
	function getjsSDKurl(){
		if($this->isDebugMode()){
			return $this->getjsSDKurlDebug();	
		}
		return $this->getjsSDKurlProd();
	}
	function getjsSDKurlProd(){
		return $this->jsSDKurlProd;	
	}
	function getjsSDKurlDebug(){
		return $this->jsSDKurlDebug;	
	}
	
	
	function isDebugMode(){
		return $this->debugMode;	
	}
	function getJSAppVersion(){
		return $this->jsappversion;	
	}
	function createCfgUI($cod,$parent){
		$ui=new mwmod_mw_facebook_ui_cfg($cod,$parent,$this);
		return $ui;
	}
}
?>