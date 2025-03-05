<?php
class mwmod_mw_google_jsinit extends mwmod_mw_html_manager_item_js{
	public $googleMan;
	function __construct($man,$cod="googleapi"){
		$this->init_item($cod);
		$this->googleMan=$man;
		
	}
	function get_src(){
		return "https://apis.google.com/js/platform.js";	
	}
	function get_html_declaration(){
		if(!$this->googleMan){
			return "";	
		}
		if(!$this->googleMan->isEnabled()){
			return false;	
		}
		$id=$this->googleMan->getAppID();
		$r="<meta name='google-signin-client_id' content='$id'>\n";
		
		$r.="<script  src='".$this->get_src()."' async defer></script>\n";
		return $r;
	}

	
	
	
}
?>