<?php
class mwmod_mw_bootstrap_html_specialelem_alert extends mwmod_mw_bootstrap_html_specialelem_elemabs{
	var $dismissible=false;
	var $modalMode;
	var $title;
	
	public $jsNotify;
	function __construct($cont=false,$display_mode="info"){
		$this->init_bt_special_elem("alert","div",$display_mode);
		$this->avaible_display_modes="success,info,warning,danger";
		if($cont!==false){
			$this->add_cont($cont);
		}
		$this->set_att("role","alert");
	
	}
	function setMsgSuccess($msg=false){
		return $this->setMsg($msg,"success");	
	}
	function setMsgInfo($msg=false){
		return $this->setMsg($msg,"info");	
	}
	function setMsgWarning($msg=false){
		return $this->setMsg($msg,"warning");	
	}
	function setMsgError($msg=false){
		return $this->setMsg($msg,"danger");	
	}
	function getJsNotify(){
		if($this->jsNotify){
			return $this->jsNotify;	
		}
		$this->jsNotify=new mwmod_mw_jsobj_obj();
		$this->updateJsNotify($this->jsNotify);
		return $this->jsNotify;	

	}
	function updateJsNotify($js){
		if(!$this->is_visible()){
			$js->disabled=true;	
		}else{
			$js->disabled=false;	
		}
		
		$js->set_prop("message",$this->get_toast_msg());	
		$js->set_prop("isHTML",true);
		if($this->dismissible){
			$js->set_prop("bta.dismissible",true);
				
		}
		if($v=$this->get_toast_type()){
			$js->set_prop("type",$v);		
		}
		if(isset($this->modalMode)){
			$js->set_prop("modalMode",$this->modalMode);	
		}
		if(isset($this->title)){
			$js->set_prop("title",$this->title);	
		}
		
		
	}
	function setModalMode($title=false,$v=true){
		if($title){
			$this->title=$title;		
		}
		if($v){
			$this->modalMode=true;	
		}else{
			$this->modalMode=false;	
		}
		$this->updateCurrentJsNotify();
	}
	
	function updateCurrentJsNotify(){
		if($this->jsNotify){
			$this->updateJsNotify($this->jsNotify);
		}
	}
	function disableAlert($enabled=false){
		$this->set_visible($enabled);
	}
	function setMsg($msg=false,$mode=false){
		if(!$msg){
			$this->disableAlert();	
		}else{
			$this->disableAlert(true);	
		}
		$this->set_cont($msg);
		$this->setAlertMode($mode);
		$this->updateCurrentJsNotify();
		
	}
	function setAlertMode($mode=false){
		if($mode){
			$this->set_display_mode($mode);
		}
		
	}
	function get_ui_devexpress_toast_options(){
		$js=new mwmod_mw_jsobj_obj();
		$js->set_prop("message",$this->get_toast_msg());	
		if($v=$this->get_toast_type()){
			$js->set_prop("type",$v);		
		}
		return $js;
		
	}
	function get_ui_devexpress_toast_options_array(){
		$r=array();
		$r["message"]=$this->get_toast_msg();	
		if($v=$this->get_toast_type()){
			$r["type"]=$v;		
		}
		return $r;
		
	}
	
	function get_toast_msg(){
		return $this->get_cont_as_html();	
	}
	
	function get_toast_type(){
		//'info'|'warning'|'error'|'success'|'custom'
		if($this->display_mode=="danger"){
			return "error";	
		}
		return $this->display_mode;
	}
	function get_html_after_open(){
		if(!$this->dismissible){
			return "";	
		}
		$html="<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";
		$html.="";
		return $html;
	}
	function add_other_class_names_to_list(&$list){
		if($this->dismissible){
			$list[]="alert-dismissible";	
		}
		
	}
	
}

?>