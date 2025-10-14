<?php
/**
 * Represents a generic JavaScript input object with optional children and validations.
 *
 * This class allows defining form inputs, groups, buttons, validations, and event handlers,
 * while maintaining a tree-like structure of child components.
 *
 */
class mwmod_mw_jsobj_inputs_input extends mwmod_mw_jsobj_newobject{
	var $def_js_class="mw_datainput_item_input";
	var $cod;
	var $def_js_class_pref="mw_datainput_item_";
	var $def_js_class_type="input";
	var $js_type="input";
	var $passJsClassPref2Children=false;
	private $_children;
	/**
	 * Constructor.
	 *
	 * @param string $cod Input code.
	 * @param string|false $objclass JavaScript class name (optional).
	 */
	function __construct($cod,$objclass=false){
		$this->init_js_input($cod,$objclass);
	}
	///quick props setters//////////
	function setLabel($lbl){
		$this->set_prop("lbl",$lbl);
	}

	function setNotes($notes){
		$this->set_prop("notes",$notes);
	}
	function setInputName($name){
		$this->set_prop("input_name",$name);
	}









	//////////
	/**
	 * Sets the input as required.
	 *
	 * @param bool $val Whether the input is required (default: true).
	 */
	function setRequired($val=true){
		$this->set_prop("state.required",$val);
	}
	/**
	 * Sets the input as disabled.
	 *
	 * @param bool $val Whether the input is disabled (default: true).
	 */	
	function setReadOnly($val=true){
		$this->set_prop("state.readOnly",$val);
	}
	function setReadOnlySelfAndChildren($val=true){
		$this->setReadOnly($val);
		if($children=$this->get_children()){
			foreach($children as $child){
				$child->setReadOnlySelfAndChildren($val);
			}
		}
	}
	/**
	 * Sets the input as disabled.
	 *
	 * @param bool $val Whether the input is disabled (default: true).
	 */
	function setDisabled($val=true){
		$this->set_prop("state.disabled",$val);
	}
	/**
	 * Adds a new default child input.
	 *
	 * @param string $cod Input code.
	 * @param string|false $type JavaScript input type (optional).
	 * @return mwmod_mw_jsobj_inputs_def
	 */

	function addNewChild($cod,$type=false){
		$p=false;
		if($this->passJsClassPref2Children){
			$p=$this->def_js_class_pref;	
		}
		
		$gr=new mwmod_mw_jsobj_inputs_def($cod,$type,$p);
		return $this->add_child($gr);
			
	}
	function setFileMode($filetypesStr=false){
		$this->set_js_type("file");
		$this->set_prop("inputAttsCfgEnabled",true);	
		if($filetypesStr){
			$this->set_prop("inputAtts.accept",$filetypesStr);
			
			
		}
		
	}
	/**
	 * Sets the JavaScript input type and updates the JS class accordingly.
	 *
	 * @param string|false $type Optional type to set; defaults to $def_js_class_type.
	 */
	function set_js_type($type=false){
		if(!$type){
			$type=$this->def_js_class_type;
		}
		$this->js_type=$type;
		$c=$this->get_js_class_from_type($this->js_type);
		$this->set_js_class($c);
	}
	/**
	 * Adds a new select input.
	 *
	 * @param string $cod Input code.
	 * @param array|false $options Optional list of options.
	 * @param string|false $objclass Optional JS class name.
	 * @return mwmod_mw_jsobj_inputs_select
	 */
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
	
	/**
	 * @param mixed $cod 
	 * @return mwmod_mw_jsobj_inputs_btgridgr 
	 */
	function addNewGrGrid($cod){
		$p=false;
		if($this->passJsClassPref2Children){
			$p=$this->def_js_class_pref;	
		}
		
		$gr=new mwmod_mw_jsobj_inputs_btgridgr($cod,$p);
		return $this->add_child($gr);
	}
	/**
	 * Adds a new input group.
	 *
	 * @param string $cod Input code.
	 * @param string|false $type Optional group type.
	 * @return mwmod_mw_jsobj_inputs_gr
	 */
	function addNewGr($cod,$type=false){
		$p=false;
		if($this->passJsClassPref2Children){
			$p=$this->def_js_class_pref;	
		}
		
		$gr=new mwmod_mw_jsobj_inputs_gr($cod,$type,$p);
		return $this->add_child($gr);
	}
	/**
	 * Adds a new group of buttons.
	 *
	 * @param string $cod Code for the group.
	 * @param string|false $type Optional type.
	 * @return mwmod_mw_jsobj_inputs_btnsgr
	 */
	function addNewBtnsGr($cod,$type=false){
		$p=false;
		if($this->passJsClassPref2Children){
			$p=$this->def_js_class_pref;	
		}
		
		$gr=new mwmod_mw_jsobj_inputs_btnsgr($cod,$type,$p);
		return $this->add_child($gr);
	}
	/**
	 * Adds a new button input.
	 *
	 * @param string $cod Button code.
	 * @param string|false $type Optional type.
	 * @return mwmod_mw_jsobj_inputs_btn
	 */
	function addNewBtn($cod,$type=false){
		$p=false;
		if($this->passJsClassPref2Children){
			$p=$this->def_js_class_pref;	
		}
		
		$gr=new mwmod_mw_jsobj_inputs_btn($cod,$type,$p);
		return $this->add_child($gr);
	}
	/**
	 * Sets the JavaScript class name prefix.
	 *
	 * @param string $pref Class prefix.
	 * @param bool $pass2Children Whether to pass prefix to children as well.
	 * @return bool
	 */
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
	/**
	 * Generates the JavaScript class name from a given type.
	 *
	 * @param string|false $type Optional input type.
	 * @return string
	 */
	function get_js_class_from_type($type=false){
		if(!$type){
			$type=$this->def_js_class_type;
		}
		return $this->def_js_class_pref.$type;
	}
	/**
	 * Adds a new child input with default input class.
	 *
	 * @param string $cod Input code.
	 * @param string|false $objclass Optional JS class.
	 * @return mwmod_mw_jsobj_inputs_input
	 */

	function add_new_child($cod,$objclass=false){
		$gr=new mwmod_mw_jsobj_inputs_input($cod,$objclass);
		return $this->add_child($gr);
			
	}
	/**
	 * Adds a data group with default group class.
	 *
	 * @param string $cod Group code.
	 * @return mwmod_mw_jsobj_inputs_input
	 */
	function add_data_gr($cod){
		$gr=new mwmod_mw_jsobj_inputs_input($cod,"mw_datainput_item_group");
		return $this->add_child($gr);
	}
	/**
	 * Adds a basic email validator to the validation list.
	 *
	 * @param bool $allowEmpty Whether empty value is allowed.
	 * @param string|false $txt Error message (optional).
	 * @return mwmod_mw_jsobj_functionext
	 */
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
	/**
	 * Adds a required field validator to the validation list.
	 *
	 * @param string|false $txt Error message (optional).
	 * @return mwmod_mw_jsobj_functionext
	 */
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
	
	/**
	 * Adds a JavaScript function to be executed after appending the input to the DOM.
	 *
	 * @param mwmod_mw_jsobj_functionext|false $fnc Optional function object.
	 * @return mwmod_mw_jsobj_functionext
	 */
	function addAfterAppend2List($fnc=false){
		if(!$fnc){
			$fnc= new mwmod_mw_jsobj_functionext();
			$fnc->add_fnc_arg("inputElem");
		}
		$list=$this->get_array_prop("afterAppendFncs");
		$list->add_data($fnc);
		return $fnc;
	}
	/**
	 * Adds a validation function to the input's validation list.
	 *
	 * @param mwmod_mw_jsobj_functionext|false $validfnc Optional function object.
	 * @return mwmod_mw_jsobj_functionext
	 */
	function addValidation2List($validfnc=false){
		if(!$validfnc){
			$validfnc= new mwmod_mw_jsobj_functionext();
			$validfnc->add_fnc_arg("inputElem");
		}
		$list=$this->get_array_prop("validationList");
		$list->add_data($validfnc);
		return $validfnc;
	}
	/**
	 * Sets the input's value.
	 *
	 * @param mixed $val Value to set.
	 */
	function set_value($val){
		$this->set_prop("value",$val);	
	}
	/**
	 * Adds a child to the internal registry and to the "childrenList" property.
	 *
	 * @param mwmod_mw_jsobj_inputs_input $child The child input object.
	 * @return mwmod_mw_jsobj_inputs_input
	 */
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
	/**
	 * Returns all registered children.
	 *
	 * @return array|false
	 */
	final function get_children(){
		if(isset($this->_children)){
			return $this->_children;	
		}
		return false;
	}
	/**
	 * Retrieves a nested child using dot notation (e.g., group1.input1).
	 *
	 * @param string $cod Dot notation path.
	 * @return object|null
	 */
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
	/**
	 * Retrieves a child input by its code.
	 *
	 * @param string $cod The code of the child.
	 * @return object|null
	 */
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
	/**
	 * Sets the JS function name (i.e., class name in JS).
	 *
	 * @param string|false $objclass The JS class to use.
	 */
	function set_js_class($objclass=false){
		if(!$objclass){
			$objclass=$this->def_js_class;	
		}
		$this->set_fnc_name($objclass);
	}
}
?>