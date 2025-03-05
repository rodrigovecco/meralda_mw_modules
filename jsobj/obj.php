<?php

class mwmod_mw_jsobj_obj extends mw_object_as_array{
	private $_array_props=array();
	var $fnc_name;
	var $xml_parent;
	public $disabled=false;
	public $js_code_in;
	
	public $outputAsScriptOnHTML=false;
	public $args_as_list;
	//private $ap_helper;
	public $unassocArraysAsList=true;
	
	function __construct($props=NULL){
		if(!is_null($props)){
			$this->set_props($props);	
		}
	}
	function setProp(){
		$num=func_num_args();
		$args=func_get_args();
		$this->_setPropVarArgs($num,$args);
		return $this;
	
	}
	function _setPropVarArgs($num,$args){
		$method="_setProp".$num."Arg";
		if(method_exists($this,$method)){
			$this->$method($args);	
		}
		
	}
	function _setProp1Arg($args){
		$arg=$args[0];
		if(is_array($arg)){
			$this->set_props_by_dot_cod_list($arg);
			return;
		}
	}
	function _setProp2Arg($args){
		$cod=$args[0];
		$val=$args[1];
		if($cod){
			$this->set_prop($cod,$val);		
		}
	}
	function prop2Array($cod){
		$val=$this->get_prop($cod);
		$origarray=false;
		if($val){
			if(!is_object($val)){
				if(is_array($val)){
					$origarray=$val;	
				}
			}
		}
		if(!$a=$this->get_array_prop($cod)){
			return false;
		}
		
		if($origarray){
			$a->setData($origarray);	
		}
		return $a;
	}
	
	function set_props_by_dot_cod_list($list){
		if(!is_array($list)){
			return false;	
		}
		$n=0;
		foreach($list as $cod=>$val){
			if($cod){
				$n++;
				$this->set_prop($cod,$val);	
			}
		}
		return $n;
	}
	function addFunction($cod,$fnc=false){
		
		if(!$fnc){
			$fnc=new mwmod_mw_jsobj_functionext();
		}
		$this->set_prop($cod,$fnc);
		return $fnc;
	}
	
	
	
	function lng_get_msg_txt($cod,$def=false,$params=false){
		return $this->lng_common_get_msg_txt($cod,$def,$params);
	}

	function lng_common_get_msg_txt($cod,$def=false,$params=false){
		if($ap=mw_get_main_ap()){
			if($man=$ap->get_msgs_man_common()){
				return $man->get_msg_txt($cod,$def,$params);	
			}
		}
		return $def;
	}
	
	function new_doptim_prop($cod,$key="id"){
		$doptim=new mwmod_mw_jsobj_dataoptim();
		$this->set_prop($cod,$doptim);
		if($key){
			$doptim->set_key($key);	
		}
		return $doptim;
	}
	function getPropObj($cod){
		if($p=$this->get_prop($cod)){
			if(is_object($p)){
				return $p;	
			}elseif(is_array($p)){
				$p=new mwmod_mw_jsobj_obj($p);
				$this->set_prop($cod,$p);
				return $p;
				
			}
		}
		$p=new mwmod_mw_jsobj_obj();
		$this->set_prop($cod,$p);
		return $p;
	}
	final function get_array_prop($cod){
		if($this->_array_props[$cod]??null){
			return $this->_array_props[$cod];	
		}
		$this->_array_props[$cod]=new mwmod_mw_jsobj_array();
		$this->set_prop($cod,$this->_array_props[$cod]);
		return $this->_array_props[$cod];	
	}
	function get_txt($txt){
		return mw_text_nl_js($txt);
	}
	function get_prop($cod=false){
		return $this->get_prop_from_key_dot($cod);	
	}
	function set_prop($key,$val){
		return $this->set_prop_from_key_dot($key,$val);	
	}
	function get_prop_cod_with_quote($cod){
		$ok=true;
		if(strpos($cod,",")!==false){
			$ok= false;	
		}
		if(strpos($cod,".")!==false){
			$ok= false;	
		}
		if(strpos($cod,"=")!==false){
			$ok= false;	
		}
		if(strpos($cod,":")!==false){
			$ok= false;	
		}
		if(strpos($cod," ")!==false){
			$ok= false;	
		}
		if(strpos($cod,"|")!==false){
			$ok= false;	
		}
		if($ok){
			return $cod;	
		}
		return "'".$cod."'";

	}
	
	function get_as_js_val_from_array($array){
		$isunassoc=false;
		$isassoc=true;
		$r="{";
		$aunasscorr=array();
		
		if($a=$this->get_as_js_vals_array($array)){
			$i=0;
			$rr=array();
			foreach ($a as $cod=>$v){
				if($cod==0){
					$isunassoc=true;	
				}
				if($cod!=$i){
					$isassoc=true;	
				}
				$aunasscorr[]=$v;	
				if($cod){
					$c=$this->get_prop_cod_with_quote($cod);
					$rr[]=$c.":".$v;	
				}
				$i++;
			}
			if(sizeof($rr)){
				$r.=implode(",",$rr);	
			}
		}
		if(!$isassoc){
			if($isunassoc){
				if($this->unassocArraysAsList){
					//probando
					return "[".implode(",",$aunasscorr)."]";	
				}
			}
		}
			
		$r.="}";
		return $r;
		
	}
	
	function get_as_js_vals_array($array){
		$r=array();
		if(!is_array($array)){
			return $r;	
		}
		foreach ($array as $key=>$val){
			$r[$key]=$this->get_prop_as_js_val($val);	
		}
		return $r;
	}
	
	function get_prop_as_js_val($val){
		if (is_object($val)){
			if(is_a($val,"mwmod_mw_datafield_jsobj_obj")){
				return 	$val->get_as_js_val();
			}
			if(is_a($val,"mwmod_mw_jsobj_obj")){
				return 	$val->get_as_js_val();
			}
			if(method_exists($val,"get_as_js_val")){
				return 	$val->get_as_js_val();	
			}
			//new 20240918
			if ($val instanceof stdClass) {
				return json_encode($val);
			}
		}
		if(is_array($val)){
			return $this->get_as_js_val_from_array($val);	
		}
		if(is_null($val)){
			return "null";	
		}
		
		if(is_bool($val)){
			if($val){
				return "true";	
			}else{
				return "false";	
			}
		}
		if((is_numeric($val))and($val==($val+0))){
			if(is_string($val)){
				if((strpos($val,"0") === 0)and(strlen($val)>1)){
					return "'".mw_text_nl_js($val."")."'";	
				}
				if(strpos($val,"E") !== false){
					return "'".mw_text_nl_js($val."")."'";	
				}
				if(strpos($val,"e") !== false){
					return "'".mw_text_nl_js($val."")."'";	
				}
			}
			return ($val+0)."";	
		}
		if(is_string($val)){
			return "'".mw_text_nl_js($val)."'";	
		}
		return "null";
		
	}
	function isDisabled(){
		return $this->disabled;	
	}
	function get_as_js_val(){
		if($this->isDisabled()){
			return "{}";	
		}
		if(!isset($this->props)){
			return "{}";	
		}
		return $this->get_prop_as_js_val($this->props);
	}
	function get_as_js_obj_args_in(){
		if($this->is_args_as_list()){
			return 	$this->get_as_js_obj_args_in_as_list();
		}
		if(!isset($this->props)){
			return "{}";	
		}
		return $this->get_prop_as_js_val($this->props);
			
	}
	function get_props_as_array(){
		return  $this->get_as_js_vals_array($this->props);
	
	}
	function get_as_js_obj_args_in_as_list(){
		if(!isset($this->props)){
			return "";	
		}
		if($a=$this->get_as_js_vals_array($this->props)){
			return implode(",",$a);	
		}
		return "";	
			
	}
	function is_args_as_list(){
		if($this->args_as_list){
			return true;	
		}
		return false;
	}
	
	function force_prop_boolean($key){
		$val=$this->get_prop_from_key_dot($key);
		$this->set_prop_boolean($key,$val);	
	}
	function force_prop_numeric($key,$val,$int=true,$min=0,$max=false){
		$val=$this->get_prop_from_key_dot($key);
		$this->set_prop_numeric($key,$val,$int,$min,$max);
	}
	function set_prop_boolean($key,$val){
		if($val){
			$val=true;	
		}else{
			$val=false;	
		}
	}
	function set_prop_numeric($key,$val,$int=true,$min=0,$max=false){
		$val=$val+0;
		if($int){
			$val=round($val);	
		}
		if(is_numeric($max)){
			if($val<$min){
				$val=$min;	
			}
		}
		if(is_numeric($max)){
			if($val>$max){
				$max=$max;	
			}
		}
		
		
		return $this->set_prop_from_key_dot($key,$val);	
	}
	function __mw_array_allow_use_this_object(){
		return true;	
	}
	function get_as_js_fnc(){
		$r="function(){".$this->get_js_fnc_code_in()."}";	
		return $r;
	}
	function set_js_code_in($jscode){
		
		$this->js_code_in=$jscode;	
	}
	function set_fnc_name($fncname){
		$this->fnc_name=$fncname;	
	}
	function get_fnc_name(){
		if($this->fnc_name){
			return 	$this->fnc_name;
		}
		return "function";
	}
	function get_js_script_html(){
		$r="\n<script language='javascript' type='text/javascript'>\n";
		$r.=$this->get_as_js_val();
		$r.="\n</script>\n";
		return $r;	
	}
	function get_js_fnc_code_in(){
		if(is_string($this->js_code_in)){
			return $this->js_code_in;
		}
		return "";
	}
	function __toString(){
		if($this->outputAsScriptOnHTML){
			return $this->get_js_script_html();	
		}
		return $this->get_as_js_val();	
	}
	
}

?>