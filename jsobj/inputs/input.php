<?php
class mwmod_mw_jsobj_inputs_input extends mwmod_mw_jsobj_newobject{
	var $def_js_class="mw_datainput_item_input";
	var $cod;
	var $def_js_class_pref="mw_datainput_item_";
	var $def_js_class_type="input";
	var $js_type="input";
	var $passJsClassPref2Children=false;
	private $_children;
	function __construct($cod,$objclass=false){
		$this->init_js_input($cod,$objclass);
	}

	function setRequired($val=true){
		$this->set_prop("state.required",$val);
	}
	function setReadOnly($val=true){
		$this->set_prop("state.readOnly",$val);
	}
	function setDisabled($val=true){
		$this->set_prop("state.disabled",$val);
	}

	function addNewChild($cod,$type=false){
		$p=false;
		if($this->passJsClassPref2Children){
			$p=$this->def_js_class_pref;	
		}
		
		$gr=new mwmod_mw_jsobj_inputs_def($cod,$type,$p);
		return $this->add_child($gr);
			
	}
	function set_js_type($type=false){
		if(!$type){
			$type=$this->def_js_class_type;
		}
		$this->js_type=$type;
		$c=$this->get_js_class_from_type($this->js_type);
		$this->set_js_class($c);
	}
	function addNewSelect($cod,$options=false,$objclass=false){
		$input=new mwmod_mw_jsobj_inputs_select($cod,$objclass);
		if($this->passJsClassPref2Children){
			$input->def_js_class_pref=$this->def_js_class_pref;	
		}
		if($options){
			$input->addSelectOptions($options);	
		}
		
		
		return $this->add_child($input);
		
	}
	function addNewGr($cod,$type=false){
		$p=false;
		if($this->passJsClassPref2Children){
			$p=$this->def_js_class_pref;	
		}
		
		$gr=new mwmod_mw_jsobj_inputs_gr($cod,$type,$p);
		return $this->add_child($gr);
	}
	function addNewBtnsGr($cod,$type=false){
		$p=false;
		if($this->passJsClassPref2Children){
			$p=$this->def_js_class_pref;	
		}
		
		$gr=new mwmod_mw_jsobj_inputs_btnsgr($cod,$type,$p);
		return $this->add_child($gr);
	}
	function addNewBtn($cod,$type=false){
		$p=false;
		if($this->passJsClassPref2Children){
			$p=$this->def_js_class_pref;	
		}
		
		$gr=new mwmod_mw_jsobj_inputs_btn($cod,$type,$p);
		return $this->add_child($gr);
	}
	
	function setJSClassPref($pref,$pass2Children=true){
		if(!$pref){
			return false;	
		}
		$this->def_js_class_pref=$pref;
		if($pass2Children){
			$this->passJsClassPref2Children=true;	
		}
		return true;
	}
	function get_js_class_from_type($type=false){
		if(!$type){
			$type=$this->def_js_class_type;
		}
		return $this->def_js_class_pref.$type;
	}

	function add_new_child($cod,$objclass=false){
		$gr=new mwmod_mw_jsobj_inputs_input($cod,$objclass);
		return $this->add_child($gr);
			
	}
	function add_data_gr($cod){
		$gr=new mwmod_mw_jsobj_inputs_input($cod,"mw_datainput_item_group");
		return $this->add_child($gr);
	}
	function addValidationEmail($allowEmpty=false,$txt=false){
		if(!$txt){
			$txt=$this->lng_get_msg_txt("enterAValidEmail","Ingresa un correo válido.");
		}
		if($allowEmpty){
			$allowEmpty="true";	
		}else{
			$allowEmpty="false";	
		}
		$validfnc=$this->addValidation2List();
		
		$msg=$this->get_txt($txt);
		if($arg=$validfnc->get_arg_by_index()){
			$validfnc->add_cont("var va=new mw_validator();\n");
			$validfnc->add_cont("var v={$arg}.get_input_value();\n");
			$validfnc->add_cont("if(va.check_email(v,$allowEmpty)){return true}else{{$arg}.set_validation_status_error('".$msg."') ; return false;}");
			
			
			
			
			
			//if({$arg}.get_input_value()){return true}else{{$arg}.set_validation_status_error('".$msg."') ; return false;}");
		}else{
			$validfnc->add_cont("return true;");	
		}
		return $validfnc;
		//	$fncvalid->add_cont("if(elem.get_input_value()){return true}else{elem.set_validation_status_error('".$msg."') ; return false;}");

	}
	
	function addValidationRequired($txt=false){
		if(!$txt){
			$txt=$this->lng_get_msg_txt("required_data","Dato requerido");
		}
		$validfnc=$this->addValidation2List();
		
		$msg=$this->get_txt($txt);
		if($arg=$validfnc->get_arg_by_index()){
			$validfnc->add_cont("if({$arg}.get_input_value()){return true}else{{$arg}.set_validation_status_error('".$msg."') ; return false;}");
		}else{
			$validfnc->add_cont("return true;");	
		}
		return $validfnc;
		//	$fncvalid->add_cont("if(elem.get_input_value()){return true}else{elem.set_validation_status_error('".$msg."') ; return false;}");

	}
	
	//
	function addAfterAppend2List($fnc=false){
		if(!$fnc){
			$fnc= new mwmod_mw_jsobj_functionext();
			$fnc->add_fnc_arg("inputElem");
		}
		$list=$this->get_array_prop("afterAppendFncs");
		$list->add_data($fnc);
		return $fnc;
	}
	function addValidation2List($validfnc=false){
		if(!$validfnc){
			$validfnc= new mwmod_mw_jsobj_functionext();
			$validfnc->add_fnc_arg("inputElem");
		}
		$list=$this->get_array_prop("validationList");
		$list->add_data($validfnc);
		return $validfnc;
	}
	function set_value($val){
		$this->set_prop("value",$val);	
	}
	
	final function add_child($child){
		if(!$cod=$child->cod){
			if(!$cod=$child->get_prop("cod")){
				return false;	
			}
		}
		if(!isset($this->_children)){
			$this->_children=array();	
		}
		$this->_children[$cod]=$child;
		$this->add_child_sub($child);
		return $child;
		
	}
	function add_child_sub($child){
		$list=$this->get_array_prop("childrenList");
		$list->add_data($child);
		
	}
	final function get_children(){
		if(isset($this->_children)){
			return $this->_children;	
		}
		return false;
	}
	function get_child_by_dot_cod($cod){
		if(!$cod){
			return false;	
		}
		
		$keys=explode(".",$cod);
		if(!sizeof($keys)){
			return false;	
		}
		$first=array_shift($keys);
		if(!$child=$this->get_child($first)){
			return false;	
		}
		if(!sizeof($keys)){
			return $child;	
		}
		if(!method_exists($child,"get_child_by_dot_cod")){
			return false;
		}
		$cod=implode(".",$keys);
		return $child->get_child_by_dot_cod($cod);
		
		
	}
	final function get_child($cod){
		if(!$cod){
			return false;	
		}
		if(isset($this->_children)){
			return $this->_children[$cod]??null;	
		}
		return false;
	}
	
	function set_cod($cod){
		$this->cod=$cod;
		$this->set_prop("cod",$cod);	
		
	}
	function init_js_input($cod,$objclass=false){
		$this->set_js_class($objclass);
		$this->set_cod($cod);
		
	}
	function init_js_input_type_mode($cod,$type=false){
		$this->set_js_type($type);
		$this->set_cod($cod);
		
	}
	function set_js_class($objclass=false){
		if(!$objclass){
			$objclass=$this->def_js_class;	
		}
		$this->set_fnc_name($objclass);
	}
}
?>