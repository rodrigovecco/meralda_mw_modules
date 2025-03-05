<?php
class mwmod_mw_helper_captcha_input extends mwmod_mw_datafield_datafielabs{
	private $_captcha;
	var $captcha_cod="captcha";
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		if(!$lbl){
			$lbl=$this->lng_get_msg_txt("input_captcha_code","Ingresa el código de verificación");	
		}
		$this->init($name,$lbl,$value,$req,$att,$style);
		$this->set_required();
	}
	
	function get_html_bootstrap($bt_output_man){
		$r="<div class='form-group'>\n";
		if(!$this->omit_lbl){
			$r.=$bt_output_man->get_html_def_lbl($this);
		}
		$params=$this->get_bootstrap_params();
		if($v=$params->get_prop("input.class")){
			$this->att["class"]=$v;
		}else{
			$this->att["class"]="form-control";
				
		}
		if($pl=$bt_output_man->get_placeholder($this)){
			$this->att["placeholder"]=htmlspecialchars($pl);	
		}
		$r.="<div class='input-group input-group-captcha'>";
		if($captcha=$this->get_captcha()){
			$r.="<div class='input-group-addon'>";
			$r.=$captcha->get_img_html();
			$r.="</div>\n";
		}
		$r.="<input ".$this->get_input_att().">";
		
		
		$r.="</div>\n";
		
		//$r.=$this->get_input_html();
		
		
		//$r.=$bt_output_man->get_html_def_crt($this);
		$r.="</div>\n";
		return $r;

		
		//return $bt_output_man->get_html_def($this);
	}

	
	
	function get_input_html(){
		
		if($captcha=$this->get_captcha()){
			$r.="<span class='input-group-addon' >";
			$r.=$captcha->get_img_html();
			$r.="</span>";
		}
		$r.="<input ".$this->get_input_att().">";
		return $r;
	}
	
	final function set_captcha($item){
		$this->_captcha=$item;	
	}
	final function get_captcha(){
		if($this->_captcha){
			return $this->_captcha;	
		}
		
		if(!$man=$this->mainap->get_submanager("captcha")){
			return false;	
		}
		if($item=$man->new_item_for_input($this->captcha_cod)){
			$this->_captcha=$item;
			return $this->_captcha;		
		}
	}
	function get_value_for_input(){
		return "";
	}
	
}
?>