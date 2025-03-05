<?php

class  mwmod_mw_kcfinder_mainman extends mw_apsubbaseobj implements mwmod_mw_kcfinder_apsubmaninterface{
	public $enabled=false;
	function __construct($ap){
		$this->init($ap);	
	}
	function kcfinder_enabled(){
		return true;	
	}
	function set_ckeditor_js_cfg($mancod,$cmdcod,$js=false,$params=array(),$urlparams=array()){
		if(!$js){
			$js=new mwmod_mw_jsobj_obj();
				
		}
		$js->set_prop("filebrowserBrowseUrl",$this->get_url($mancod,$cmdcod,$params,"files",$urlparams,false,"ckeditor",false,false));
		$js->set_prop("filebrowserImageBrowseUrl",$this->get_url($mancod,$cmdcod,$params,"images",$urlparams,false,"ckeditor",false,false));
		$js->set_prop("filebrowserFlashBrowseUrl",$this->get_url($mancod,$cmdcod,$params,"flash",$urlparams,false,"ckeditor",false,false));

		$js->set_prop("filebrowserUploadUrl",$this->get_url($mancod,$cmdcod,$params,"files",$urlparams,false,"ckeditor",false,true));
		$js->set_prop("filebrowserImageUploadUrl",$this->get_url($mancod,$cmdcod,$params,"images",$urlparams,false,"ckeditor",false,true));
		$js->set_prop("filebrowserFlashUploadUrl",$this->get_url($mancod,$cmdcod,$params,"flash",$urlparams,false,"ckeditor",false,true));
		return $js;
		
		
			
	}
	function get_url_from_params($mancod,$cmdcod,$params=array(),$urlparams=array(),$upload=false){
		if(!is_array($urlparams)){
			$urlparams=array();	
		}
		if(!$urlparams["lng"]){
			if($lngman=$this->mainap->get_current_lng_man()){
				if($lng=$lngman->get_globalize_locale_cod()){
					$urlparams["lng"]=$lng;		
				}
			}
			
		}
		if(!$cms=$this->get_cod_for_man($mancod,$cmdcod,$params)){
			return false;	
		}
		$urlparams["cms"]=$cms;
		$url="/kcfinder/browse.php";
		if($upload){
			$url="/kcfinder/upload.php";	
		}
		if($q=mw_array2urlquery($urlparams)){
			$url.="?".$q;	
		}
		return $url;
	
		
	}

	function get_url($mancod,$cmdcod,$params=array(),$type=false,$urlparams=array(),$dir=false,$opener=false,$lng=false,$upload=false){
		if(!is_array($urlparams)){
			$urlparams=array();	
		}
		if($type){
			$urlparams["type"]=$type;		
		}
		if($dir){
			$urlparams["dir"]=$dir;		
		}
		if($opener){
			$urlparams["opener"]=$opener;		
		}
		if($lng){
			$urlparams["lng"]=$lng;		
		}
		return $this->get_url_from_params($mancod,$cmdcod,$params,$urlparams,$upload);
		
		
	}
	function get_cod_for_man($mancod,$cmdcod,$params=array()){
		$list=array($mancod,$cmdcod);
		if(is_array($params)){
			foreach($params as $c=>$v){
				$list[]=$c;
				$list[]=$v;
					
			}
		}
		return implode(".",$list);
	}
	function set_kcfinder_config_by_cmdcod_debug($params,&$config){
		$cfgman=new mwmod_mw_kcfinder_cfgsetter($params,$config);
		if(!$cfgman->allow("debug")){
			return false;
		}
		if(!$cfgman->set_upload_url_by_rel("debug/kcfinder")){
			return false;
		}
		$cfgman->enabled();
		return true;
	}
	function set_kcfinder_config_by_cmdcod($cmdcod,$params,&$config){
		if($cmdcod=="debug"){
			return $this->set_kcfinder_config_by_cmdcod_debug($params,$config);	
		}
		
	}
	function is_enabled(){
		return $this->enabled;	
	}
	function create_auth_man($cod){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;	
		}
		
	}
	function set_kcfinder_config(&$config,$cod){
		if(!$this->is_enabled()){
			return false;
		}
		if(!is_string($cod)){
			return false;	
		}
		$a=explode(".",$cod);
		if(sizeof($a)<2){
			return false;	
		}
		$mancod=array_shift($a);
		$cmdcod=array_shift($a);
		if(!$mancod=$this->check_str_key_alnum_underscore($mancod)){
			return false;	
		}
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			return false;	
		}
		if(!$man=$this->mainap->get_submanager($mancod)){
			return false;
		}
		
		$interfaces = class_implements($man);

		if (!isset($interfaces['mwmod_mw_kcfinder_apsubmaninterface'])) {
			return false;
		}

		
		if(!$man->kcfinder_enabled()){
			return false;
		}
		$params=array();
		if($s=sizeof($a)){
			for($x=0;$x<$s;$x++){
				$pcod=$a[$x];
				$x++;
				$pval=$a[$x];
				$params[$pcod]=$pval;
			}
		}
		return $man->set_kcfinder_config_by_cmdcod($cmdcod,$params,$config);

		
		//$config['disabled'] = false;
		
		
	}
	function exec_user_validation(){
		if($man=$this->get_admin_user_manager()){
			return $man->exec_user_validation();	
		}
		
	}
	function get_admin_user_manager(){
		return $this->mainap->get_admin_user_manager();	
	}
	final function init($ap){
		$this->set_mainap($ap);	
	}

}
?>