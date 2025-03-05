<?php
class mwmod_mw_devextreme_objs_notifycfg extends mwmod_mw_jsobj_obj{
	function __construct($msg=false){
		$this->set_msg($msg);
		$this->set_type_info();		
	}
	
	//'info'|'warning'|'error'|'success'|'custom'
	function set_type_info(){
		$this->set_prop("type","info");	
	}
	function set_type_warning(){
		$this->set_prop("type","warning");	
	}
	function set_type_error(){
		$this->set_prop("type","error");	
	}
	function set_type_success(){
		$this->set_prop("type","success");	
	}
	function set_type_custom(){
		$this->set_prop("type","custom");	
	}
	
	
	function set_msg($msg=false){
		$this->set_prop("message",$msg);
		if($msg){
			$this->set_disabled(false);	
		}else{
			$this->set_disabled(true);		
		}
	}
	function set_disabled($val=true){
		if($val){
			$this->set_prop("disabled",true);
			$this->set_prop("enabled",false);
		}else{
			$this->set_prop("disabled",false);
			$this->set_prop("enabled",true);
		}
	}
}
?>