<?php
class mwmod_mw_jsobj_dataoptim extends mwmod_mw_jsobj_obj{
	private $_data_fields=array();
	private $_data=array();
	private $has_fields=false;
	//var $key=false;
	
	private $params;
	
	public $dataOnlyMode=false;
	//var $auto_add_fields=true;
	function __construct($cod=false){
		if($cod){
			$this->set_cod($cod);	
		}
	}
	function toSerializable(){
		$result=new stdClass();
		$result->keys=$this->get_data_keys();
		$result->data=[];
		if($dataall=$this->get_data_list()){
			foreach($dataall as $data){
				if($d=$this->new_array_from_data_item($data)){
					$dd=array();
					foreach($d as $v){
						$dd[]=$v;
					}
					$result->data[]=$dd;
				}
			}
		}
		return $result;


	}
	/*
	//todo: completar en el futuro si se requiere, ver mwmod_mw_helper_inputvalidator_abs
	function setFullValue($allData){
		if(!is_array($allData)){
			return false;
		}
		if(!is_array($allData["keys"])){
			return false;
		}
		if(is_array($allData["params"]??null)){
			if(is_string($allData["params"]["key"]??null)){
				$this->set_key($allData["params"]["key"]);
				//no other params allowed
			}
		}

	}
	*/


	function set_cod($cod){
		$this->__get_priv_params();
		$this->params->set_prop("cod",$cod);
			
	}
	function set_key($cod="id"){
		$this->__get_priv_params();
		$this->params->set_prop("key",$cod);
	}
	function new_keys_array_js(){
		$keys=new mwmod_mw_jsobj_array($this->get_data_keys());
		return $keys;	
	}
	function new_data_array_js(){
		$finaljs=new mwmod_mw_jsobj_array();
		if($dataall=$this->get_data_list()){
			foreach($dataall as $data){
				if($djs=$this->new_js_array_from_data_item($data)){
					$finaljs->add_data($djs);		
				}
			}
		}
		return $finaljs;
			
	}
	function new_js_array_from_data_item($data){
		$d=$this->new_array_from_data_item($data);
		$djs=new mwmod_mw_jsobj_array($d);
		return $djs;
			
	}
	function new_array_from_data_item($data){
		$r=array();
		if(!is_array($data)){
			return $r;	
		}
		if($items=$this->get_data_fields()){
			foreach($items as $cod=>$item){
				if(array_key_exists($cod,$data)){
					$val=$item->get_value($data[$cod]);	
				}else{
					$val=$item->get_default_value();	
				}
				$r[$cod]=$val;
			}
		}
		return $r;
	}
	function get_as_js_val(){
		if($this->dataOnlyMode){
			return $this->get_as_js_valDataOnlyMode();	
		}
		$keys=$this->new_keys_array_js();
		$data=$this->new_data_array_js();
		$params=$this->__get_priv_params();
		
		$r="new mw_js_optim_data(";
		$r.=$keys->get_as_js_val().",";
		$r.=$data->get_as_js_val().",";
		$r.=$params->get_as_js_val()."";
		$r.=")";
		return $r;
	}
	function get_as_js_valDataOnlyMode(){
		$js=$this->newDataOnlyObj();
		return $js->get_as_js_val();
	}
	
	function newDataOnlyObj(){
		$js=new mwmod_mw_jsobj_obj();
		$js->set_prop("keys",$this->new_keys_array_js());
		$js->set_prop("data",$this->new_data_array_js());
		$js->set_prop("params",$this->__get_priv_params());
		return $js;

			
	}
	function add_data($data,$auto_create_fields="ifnone"){
		if($auto_create_fields==="ifnone"){
			if($this->has_fields){
				$auto_create_fields=false;	
			}else{
				$auto_create_fields=true;		
			}
		}
		return $this->_add_data($data,$auto_create_fields);	
	}
	final function get_fields_num(){
		return sizeof($this->_data_fields);	
	}
	final function _add_data($data,$auto_create_fields=false){
		if(!is_array($data)){
			return false;	
		}
		if($auto_create_fields){
			$keys=array_keys($data);
			$this->create_missing_fields_by_keys($keys);	
		}
		$this->_data[]=$data;
		return true;
	}
	function create_missing_fields_by_keys($keys){
		$r=array();
		foreach($keys as $key){
			if(!$this->get_data_field($key)){
				if($field=$this->add_data_field($key)){
					$r[$key]=$field;	
				}
			}
		}
		if(sizeof($r)){
			return $r;	
		}
	}
	final function get_data_keys(){
		return 	array_keys($this->_data_fields);
	}
	final function get_data_list(){
		return $this->_data;	
	}
	final function get_data_fields(){
		return $this->_data_fields;	
	}
	final function get_data_field($cod){
		if(!$cod){
			return false;	
		}
		return $this->_data_fields[$cod]??null;	
	}
	final function add_data_field_item($field,$overwrite=true){
		if(!$cod=$field->cod){
			return false;
		}
		
		if(!$overwrite){
			if($this->get_data_field($cod)){
				return false;	
			}
		}
		$this->has_fields=true;
		$this->_data_fields[$cod]=$field;
		return $field;
		//
	}
	
	function add_data_field($cod){
		if(!$cod){
			return false;
		}
		if(is_object($cod)){
			if(is_a($cod,"mwmod_mw_jsobj_dataoptim_field")){
				$field=$cod;
				return $this->add_data_field_item($field);
			}
		}
		$field=new mwmod_mw_jsobj_dataoptim_field($cod);
		return $this->add_data_field_item($field);
	}
	final function __get_priv_has_fields(){
		return $this->has_fields;	
	}
	final function __get_priv_params(){
		if(!isset($this->params)){
			$this->params=new mwmod_mw_jsobj_obj();	
		}
		return $this->params;	
	}
	
	
	
}
?>