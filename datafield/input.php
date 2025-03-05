<?php
class mwmod_mw_datafield_input extends mwmod_mw_datafield_datafielabs{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		$this->fix_slashes_and_quotes=true;
	}
	function add_js_alnumandunder_validation($msg=false,$allowempty=false){
		
		$e="false";
		if($allowempty){
			$e="true";	
		}
		$valid_fnc=$this->set_js_validation_function();
		$js.="var validator=new mw_validator();\n";
		$js.="var val=this.get_value()+'';\n";
		if(!$msg){
			$msg=$this->lng_get_msg_txt("value_contains_invalid_characters","El valor contiene caracteres no válidos");	
		}
		$js.="if(!validator.check_alphanumeric_and_underline(val,$e)){\n";
		$js.="this.set_validation_status_error('".$valid_fnc->get_txt($msg)."');\n";
		$js.="return false;\n";
		$js.="}\n";
		$js.="this.set_validation_status_normal();\n";
		$js.="return true;\n";
		$valid_fnc->set_validation_js_code_in($js);
		return $valid_fnc;

	
	}
	
	
	function add_js_email_validation($allowempty=false){
		
		$e="false";
		if($allowempty){
			$e="true";	
		}
		$valid_fnc=$this->set_js_validation_function();
		$js="var validator=new mw_validator();\n";
		$js.="var val=this.get_value()+'';\n";
		$msg=$this->lng_get_msg_txt("enter_a_valid_email","Ingresa un correo válido");	
		$js.="if(!validator.check_email(val,$e)){\n";
		$js.="this.set_validation_status_error('".$valid_fnc->get_txt($msg)."');\n";
		$js.="return false;\n";
		$js.="}\n";
		$js.="this.set_validation_status_normal();\n";
		$js.="return true;\n";
		$valid_fnc->set_validation_js_code_in($js);
		return $valid_fnc;

	
	}
	function get_value_for_input(){
		$v=$this->get_value();
		if($this->fix_slashes_and_quotes){
			$v=$this->fix_slashes_and_quoutes_str($v);
		}
		return htmlspecialchars($v."");
	}
	
}
?>