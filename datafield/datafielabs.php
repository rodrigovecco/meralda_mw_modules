<?php
abstract class mwmod_mw_datafield_datafielabs extends mw_apsubbaseobj{
	private $template;
	private $_params_man;
	private $_allow_set_value=true;
	public $datafields_creator;
	var $fix_slashes_and_quotes=false;
	public $att=array();
	public $placeholder;
	public $omit_lbl=false;
	
	private $_bootstrap_params;
	public $output_as_html=false;
	public $name;
	public $lbl;
	public $req;
	public $style;
	public $value;
	public $items;
	public $parent_data_field;
	var $js_mw_datainput;
	public $btFloating=false;
	public $readonly=false;
	public $disabled;


	function setBTFloating($children=true){
		
		$this->btFloating=true;
		if(!$this->placeholder){
			$this->placeholder="...";
		}
		if($children){
			if(is_array($this->items)){
				foreach($this->items as $i){
					$i->setBTFloating(true);	
				}
			}
		}
		
		
	}

	function get_js_mw_datainput(){
		if(!$this->js_mw_datainput){
			$this->js_mw_datainput=$this->new_js_mw_datainput();
		}
		return $this->js_mw_datainput;	
		
	}
	function new_js_mw_datainput(){
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_input");	
		$jsinput->set_prop("cod",$this->get_cod());
		$jsinput->set_prop("lbl",$this->get_lbl());
		return $jsinput;
		
	}

	
	function prepare_js_bootstrap($frm){
		$this->prepare_js_bootstrap_req_clases($frm);
		$this->prepare_js_bootstrap_this($frm);
		if($items=$this->get_items()){
			foreach($items as $item){
				$item->prepare_js_bootstrap($frm);	
			}
		}
	}
	function prepare_js_bootstrap_req_clases($frm){
		if($c=$this->get_js_man_class_bootstrap_mode()){
			$frm->bootstrap_req_class_add($c);	
		}
	}
	function prepare_js_bootstrap_this($frm){
		if($js=$this->get_js_bootstrap_create()){
			$frm->bootstrap_create_inputs_add($js);	
		}
	}
	function prepare_params_for_bootstrap(){
		//	
	}
	function get_js_bootstrap_create(){
		if(!$c=$this->get_js_man_class_bootstrap_mode()){
			return false;
		}
		$args=array(
			"n"=>$this->get_frm_field_id(),
			
		);
		$this->prepare_params_for_bootstrap();
		if($man=$this->get_params_man_if_set()){
			$args["p"]=$man;
		}

		
		$js= new mwmod_mw_jsobj_newobject($c,$args,true);	
		return $js;
	}

	
	function get_items(){
		if(is_array($this->items)){
			return $this->items;	
		}
	}
	
	////
	function set_output_as_html($val=true){
		if($val){
			$val=true;	
		}else{
			$val=false;	
		}
		
		$this->output_as_html=$val;
		if($items=$this->get_items()){
			foreach($items as $i){
				$i->set_output_as_html($val);	
			}
				
		}
		
	}
	
	function set_disabled($val=true){
		if($val){
			$val=true;	
		}else{
			$val=false;	
		}
		$this->disabled=$val;
		if(is_array($this->items)){
			foreach($this->items as $i){
				$i->set_disabled($val);	
			}
		}
		
	}
	function set_readonly($val=true){
		if($val){
			$val=true;	
		}else{
			$val=false;	
		}
		$this->readonly=$val;
		if(is_array($this->items)){
			foreach($this->items as $i){
				$i->set_readonly($val);	
			}
		}
		
	}
	function set_required($val=true){
		if($val){
			$val=true;	
		}else{
			$val=false;	
		}
		$this->req=$val;
		if(is_array($this->items)){
			foreach($this->items as $i){
				$i->set_required($val);	
			}
		}
		
	}
	
	final function get_template(){
		$this->__init_template();
		return $this->template;	
	}
	function set_placeholder_from_lbl($omitlbl=true){
		$this->placeholder=$this->get_lbl();
		$this->omit_lbl=$omitlbl;
	}
	function get_html_bootstrap_children($bt_output_man){
		$r="";
		if(is_array($this->items)){
			foreach ($this->items as $i){
				$r.=$i->get_html_bootstrap($bt_output_man);	
			}
		}
		
		return $r;
		
	}
	function get_frm_field_id_plain(){
		if(!$str=$this->get_frm_field_id()){
			return "";	
		}
		$str=str_replace("[","_",$str);
		$str=str_replace("]","",$str);
		return $str;
		//return 
	}
	
	

	function get_html_bootstrap($bt_output_man){
		return $bt_output_man->get_html_def($this);
	}
	final function get_bootstrap_params(){
		if(!isset($this->_bootstrap_params)){
			$this->_bootstrap_params= new mwmod_mw_jsobj_obj();
		}
		return $this->_bootstrap_params;
	}

	
	
	//no usados
	function create_bootstrap_js(){
		$obj=new mwmod_mw_jsobj_newobject("mw_frmadv_frm_item");
		return $obj;
	}
	function create_bootstrap_init_args(){
		$obj=new mwmod_mw_jsobj_obj();
		return $obj;
	}
	function create_bootstrap_js_add(){
		$obj=new mwmod_mw_jsobj_obj();
		$obj->set_prop("cod",$this->get_cod());
		$obj->set_prop("item",$this->create_bootstrap_js());
		$obj->set_prop("args",$this->create_bootstrap_init_args());
		return $obj;
	}
	function add_2_bootstrap_js_items_list($list){
		$o=$this->create_bootstrap_js_add();
		//$o->set_prop("ss","f");
		$list->add_data($o);
	}
	//template
	function get_init_template(){
		if(	$this->datafields_creator){
			if($this->datafields_creator->frm){
				if($t=$this->datafields_creator->frm->get_template()){
					if($tt=$t->new_input_template($this)){
						return $tt;	
					}
				}
			}
		}
		if($man=$this->mainap->get_templates_man()){
			if($t=$man->create_template_datafields($this)){
				return $t;	
			}
		}

		
		return new mwmod_mw_datafield_template();
	}
	private function __init_template(){
		if(!isset($this->template)){
			if($t=$this->get_init_template()){
				$this->set_template($t);	
			}
		}
	}
	final function set_template($template){
		if(!$template){
			return false;	
		}
		if(!is_object($template)){
			return false;	
		}
		if(!is_a($template,"mwmod_mw_datafield_template")){
			return false;	
		}
		$this->template=$template;
		return true;
	}
	////
	function get_cod(){
		return $this->name;	
	}
	
	function set_datafield_creator($cr){
		$this->datafields_creator=$cr;	
	}
	function get_html_items_names_debug(){
		$r.="<div>".$this->get_frm_field_name()."</div>";
		if(is_array($this->items)){
			$r.="<ul>";
			foreach ($this->items as $i){
				
				$r.="<li>".$i->get_html_items_names_debug()."</li>";	
			}
			$r.="</ul>";
		}
		return $r;
			
	}
	//nombres e ids
	function get_frm_field_id(){
		return $this->get_frm_field_name();
	}
	function get_frm_field_name_pref_for_children(){
		return $this->get_frm_field_name();	
	}
	function get_frm_field_name_pref(){
		if($this->frm_name_pref){
			return 	$this->frm_name_pref;
		}
		if($this->parent_data_field){
			
			return $this->parent_data_field->get_frm_field_name_pref_for_children();
		}
		if($this->datafields_creator){
			return $this->datafields_creator->get_frm_field_name_pref_for_children();	
		}
		
		
	}
	function get_cod_for_name(){
		return $this->get_cod();	
	}
	function get_frm_field_name_ui(){
		return $this->get_frm_field_name();
	}

	function get_frm_field_name(){
		if($pref=$this->get_frm_field_name_pref()){
			return $pref."[".$this->get_cod_for_name()."]";
		}
		return $this->get_cod_for_name();
	}
	function get_frm_subfield_name($code){
		$list=explode(".",$code);
		$r=$this->get_frm_field_name();
		foreach ($list as $c){
			$r.="[".$c."]";	
		}
		return $r;
		
	}
	
	//por verificar
	
	function set_as_allways_req(){
		$this->set_required(true);
		$this->set_disabled(false);
		$this->allways_enable=true;
	}
	function add2jsreqclasseslist(&$list){
		
		if(!$c=$this->get_js_man_class()){
			return false;	
		}
		if(!is_array($list)){
			$list=array();	
		}
		$list[$c]=$c;
		

		
	}

	final function get_params_man_if_set(){
		if(!isset($this->_params_man)){
			return false;
		}
		return $this->get_params_man();
	}
	final function get_params_man(){
		if(!isset($this->_params_man)){
			$this->_params_man= new mwmod_mw_jsobj_obj();
		}
		return $this->_params_man;
	}
	function add_after_init_event_to_list_bt_mode($jsfnc=false){
		$events=$this->get_param("afteriniteventsbtmode");
		$create=true;
		if($events){
			if(is_object($events)){
				if(is_a($events,"mwmod_mw_jsobj_array")){
					$create=false;	
				}
			}
		}
		
		
		if($create){
			$events=new mwmod_mw_jsobj_array();	
			$this->set_param("afteriniteventsbtmode",$events);
		}
		if(!$jsfnc){
			$jsfnc=new mwmod_mw_jsobj_function("");
			$jsfnc->add_fnc_arg("inputman");
		}
		$events->add_data($jsfnc);
		return $jsfnc;
			
	}
	function set_js_validation_function($inner_code=false){
		$fnc=new mwmod_mw_datafield_js_validfnc($inner_code);
		return $this->add_after_init_event_to_list($fnc);
	}
	function add_after_init_event_to_list($jsfnc=false){
		return $this->add_after_init_event_to_list_bt_mode($jsfnc);
		//bt mode only
		/*
		$events=$this->get_param("afteriniteventslist");
		$create=true;
		if($events){
			if(is_object($events)){
				if(is_a($events,"mwmod_mw_jsobj_array")){
					$create=false;	
				}
			}
		}
		
		
		if($create){
			$events=new mwmod_mw_jsobj_array();	
			$this->set_param("afteriniteventslist",$events);
		}
		if(!$jsfnc){
			$jsfnc=new mwmod_mw_jsobj_function("");
			$jsfnc->add_fnc_arg("inputman");
		}
		$events->add_data($jsfnc);
		return $jsfnc;
		*/
	}
	
	function add_after_init_event($js){
		$events=$this->get_param("afterinitevents");
		$id=1;
		if(is_array($events)){
			$id=sizeof($events)+1;	
		}
		$jsfnc=new mwmod_mw_jsobj_function($js);
		$this->set_param("afterinitevents.".$id,$jsfnc);
		//echo $jsfnc->get_as_js_val()."<br>";
		return $jsfnc;
	}
	function set_def_params(){
		//	
	}
	final function set_param($key,$val){
		$man=$this->get_params_man();
		return $man->set_prop($key,$val);	
	}
	final function get_param($key=false){
		$man=$this->get_params_man();
		return $man->get_prop($key);	
	}
	///js
	function get_js_init_code_for_frm($frm=false){
		if($js=$this->get_js_init_code_for_frm_init($frm)){
			return $js;	
		}
		return false;
	}
	function get_js_man_class_bootstrap_mode($frm=false){
		
		return $this->get_js_man_class($frm);
	}
	
	function get_js_man_class($frm=false){
		
		return "mw_input_elem_def";	
		return false;	
	}
	function get_js_init_code_for_frm_init($frm=false){
		if(!$code=$this->get_js_init_code_for_frm_init_fnc($frm)){
			return false;	
		}
		return "function(frmman){".$code."}\n";
	}
	function get_js_init_code_for_frm_init_fnc_after_added($frm){
		return false;	
	}
	function get_js_input_manager_name(){
		return $this->get_frm_field_name();
	}
	function get_js_init_code_for_frm_init_fnc($frm=false){
		if(!$new=$this->get_js_create_man($frm)){
			return false;	
		}
		$js="var inputman= ".$new."\n";
		$js.="if(!inputman.check_preinit()){\n";
		$js.="return false;\n";
		$js.="}else{\n";
		$js.="frmman.add_input_manager('".$this->get_js_input_manager_name()."',inputman);\n";
		$js.=$this->get_js_init_code_for_frm_init_fnc_after_added($frm);
		$js.="}\n";
		
		return $js;
		
		
	}
	
	function get_js_create_params($frm=false){
		
		$r="frmman,'".$this->get_frm_field_name()."'";
		if($man=$this->get_params_man_if_set()){
			$r.=",".$man->get_as_js_val();	
		}
		
		return $r;
	}
	function get_js_create_man($frm=false){
		if(!$c=$this->get_js_man_class($frm)){
			return false;	
		}
		return "new $c(".$this->get_js_create_params().");";
		
	}
	///
	
	function get_full_inputs_children_html(){
	
		if(is_array($this->items)){
			foreach ($this->items as $i){
				$r.=$i->get_full_input_html();	
			}
		}
		
		return $r;
		
	}

	
	function get_full_input_html(){
		if(!$t=$this->get_template()){
			return false;	
		}
		return $t->get_full_input_html($this);
	}

	function set_frm_name_pref($pref=false){
		$this->frm_name_pref=$pref;
	}
	function add_disable_if_empty($item){
		if(!is_array($this->disable_if_empty_items)){
			$this->disable_if_empty_items=array();
		}
		if(!$this->get_value()){
			$item->set_disabled(true);	
		}
		$item->set_disabled_enable_if_empty_parent($this);	
		$this->disable_if_empty_items[]=$item;
		return $item;
	}
	function set_disabled_enable_if_empty_parent($item){
		$this->disabled_enable_if_empty_parent=$item;
	}
	function get_disabled_enable_if_empty_parent(){
		return $this->disabled_enable_if_empty_parent;
	}
	function add_enable_if_empty($item){
		if(!is_array($this->enable_if_empty_items)){
			$this->enable_if_empty_items=array();
		}
		if($this->get_value()){
			$item->set_disabled(true);	
		}
		$item->set_disabled_enable_if_empty_parent($this);	
		$this->enable_if_empty_items[]=$item;
		return $item;
	}
	function get_items_frm_names_as_str_list($items,$ui=true){
		if(!is_array($items)){
			return false;	
		}
		foreach($items as $item){
			if($ui){
				if($n=$item->get_frm_field_name_ui()){
					$r[]=$n;	
				}
			}else{
				if($n=$item->get_frm_field_name()){
					$r[]=$n;	
				}
			}
		}
		if($r){
			return implode(",",$r);	
		}
	}

	function add_sub_item_by_dot_cod($item,$parentdorcod=false){
		if(!$parentdorcod){
			return $this->add_item($item);
		}
		if(!$pitem=$this->get_or_add_groupitem_by_dot_cod($parentdorcod)){
			return false;	
		}
		return $pitem->add_item($item);
	}
	function get_or_add_groupitem_by_dot_cod($cod){
		if(empty($cod)){
			return false;
		}
		$coda=explode(".",$cod);
		$cod1=array_shift($coda);
		$cod2=false;
		if(sizeof($coda)){
			$cod2=implode(".",$coda);	
		}
		
		if(!$pitem=$this->get_item($cod1)){
			$ni=new mwmod_mw_datafield_group($cod1);
			$pitem=$this->add_item($ni);
		}
		if(!$pitem){
			return false;	
		}
		if(!$cod2){
			return 	$pitem;
		}else{
			return $pitem->get_or_add_groupitem_by_dot_cod($cod2);
		}
		
		
		
	}
	function get_item_by_dot_cod($cod){
		if(empty($cod)){
			return false;
		}
		$coda=explode(".",$cod);
		$cod1=array_shift($coda);
		$cod2=false;
		if(sizeof($coda)){
			$cod2=implode(".",$coda);	
		}
		
		if($i=$this->get_item($cod1)){
			if($cod2){
				return $i->get_item_by_dot_cod($cod2);	
			}
			return $i;
		}
		
	}

	function get_item($cod){
		if(empty($cod)){
			return false;
		}
		if($this->items[$cod]){
			return 	$this->items[$cod];
		}
	}
	function get_input_att_name_value(){
		return "value";	
	}
	function get_req_fail_msg(){
		return addslashes($this->get_msg("Dato requerido").": ".$this->get_lbl());	
		
	}

	function get_value(){
		return $this->value;	
	}
	final function set_allow_set_value($allow=true){
		if($allow){
			$this->_allow_set_value=true;	
		}else{
			$this->_allow_set_value=false;		
		}
	}
	final function allow_set_value(){
		return $this->_allow_set_value;
	}
	function set_value($value){
		if(!$this->allow_set_value()){
			return false;	
		}
		$this->value=$value;
	}
	function get_html_input_param($code,$val){
		$name=$this->get_frm_subfield_name($code);
		return "<input name='$name' value='$val' type='hidden'>";	
	}
	
	function set_value_for_children($value){
		if(!is_array($value)){
			$value=array();	
		}
		if(!is_array($this->items)){
			return false;
		}
		foreach ($this->items as $cod=>$item){
			$item->set_value($value[$cod]??null);	
		}
	}
	function set_parent($parent){
		$this->parent_data_field=$parent;	
	}
	function add_item($item){
		if($cod=$item->get_cod()){
			if(!is_array($this->items)){
				$this->items=array();	
			}
			$this->items[$cod]=	$item;
			$this->items[$cod]->set_parent($this);
			return $this->items[$cod];
		}
	}	
	function add_items($items){
		if(!$items){
			return false;
		}
		if(is_a($items,"mwmod_mw_datafield_creator")){
			return 	$this->add_items($items->items);
		}
		if(is_array($items)){
			
			foreach ($items as $cod=>$i){
				if($this->add_item($i)){
					
					$r[$cod]=$i;	
				}
			}
			return $r;
		}
		
		
	}
	final function init($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->set_mainap();
		
		$this->name=$name;	
		$this->lbl=$lbl;	
		
		$this->req=$req;
		if(!is_array($att)){
			$att=array();	
		}
		if(!is_array($style)){
			$style=array();	
		}
		$this->att=$att;	
		$this->style=$style;
		if($value){
			$this->set_value($value);	
		}
	}
	function get_input_style_att(){
		if(!is_array($this->style)){
			return false;	
		}
		if(!sizeof($this->style)){
			return false;	
		}
		foreach ($this->style as $cod=>$v){
			if($v){
				$r[]=$cod.":$v";	
			}
		}
		if($r){
			return implode(";",$r);	
		}
	}
	function setInputAtt($cod,$val){
		if(!$this->att){
			$this->att=array();	
		}
		if(!is_array($this->att)){
			return false;
		}
		$this->att[$cod]=$val;
		
	}

	function get_input_att_as_array(){
		$a=$this->att;
		if(!is_array($a)){
			$a=array();	
		}
		
		if($id=$this->get_frm_field_id()){
			$a["id"]=$id;	
		}
		if($name=$this->get_frm_field_name()){
			$a["name"]=$name;	
		}
		if($this->disabled){
			$a["disabled"]="disabled";	
		}
		if($this->readonly){
			$a["readonly"]="readonly";	
		}
		if($this->req){
			$a["required"]="required";	
		}
		if($s=$this->get_input_style_att()){
			$a["style"]=$s;		
		}
		$this->set_input_att_value($a);
		return $a;
	}
	function fix_slashes_and_quoutes_str($v=""){
		$man=new mwmod_mw_helper_text($v);
		return $man->fix_quotes_slashes();
	}
	function get_value_for_input(){
		//return addslashes($this->get_value());
		$v=$this->get_value();
		return htmlspecialchars($v."");
			
	}
	function set_input_att_value(&$a=array()){
		if(!is_array($a)){
			$a=array();	
		}
		$r=$this->get_value_for_input();
		$a["value"]=$r;
		
		return $r;
	}	
	function get_input_att(){
		if(!$a=$this->get_input_att_as_array()){
			return false;
		}
		foreach ($a as $cod=>$v){
			if(strlen($v)){
				$r[]=$cod."=\"$v\"";	
			}
		}
		if($r){
			return implode(" ",$r);	
		}
		
	}
	function get_value_as_html(){
		return $this->get_value_as_str();	
	}
	function get_value_as_str(){
		return $this->get_value();	
	}
	
	function get_input_html(){
		return $r="<input ".$this->get_input_att().">";
	}
	function get_lbl(){
		return $this->lbl;	
	}
	function __call($a,$b){
		return false;	
	}

}

?>