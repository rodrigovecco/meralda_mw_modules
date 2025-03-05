<?php

class mwmod_mw_datafield_frm extends mw_apsubbaseobj{
	private $name;
	//private $inputs_template;
	public $target="_self";
	public $method="post";
	public $action="index.php";
	public $title="";
	public $enctype="multipart/form-data";
	private $datafieldcreator;
	
	private $_inputs_js_init_code_list;
	private $_after_create_fncs_extra=array();
	
	private $template;
	var $style_class="";
	public $bootstrap_mode=false;
	public $inline=false;
	private $_bootstrap_output_man;
	private $_bootstrap_req_classes=array();
	private $_bootstrap_after_create;
	private $_bootstrap_after_create_add_inputs;
	private $_bootstrap_after_create_add_inputs_fnc;
	var $js_mw_datainput;
	function get_html_title(){
		return $this->title;	
	}
	function get_js_mw_datainput(){
		if(!$this->js_mw_datainput){
			$this->js_mw_datainput=$this->new_js_mw_datainput();
		}
		return $this->js_mw_datainput;	
		
	}
	function new_js_mw_datainput(){
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_frm");	
		$jsinput->set_prop("frm_id",$this->get_frm_id());
		$jsinput->set_prop("frm_name",$this->get_frm_name());
		$jsinput->set_prop("method",$this->method);
		$jsinput->set_prop("action",$this->get_action());
		$jsinput->set_prop("enctype",$this->enctype);
		$jsinput->set_prop("target",$this->target);
		if($this->datafieldcreator){
			if(is_array($this->datafieldcreator->items)){
				$children=new mwmod_mw_jsobj_array();
				$jsinput->set_prop("childrenList",$children);
				foreach ($this->datafieldcreator->items as $i){
					if($ch=$i->get_js_mw_datainput()){
						$children->add_data($ch);	
					}
				}
			}
			
		}
		return $jsinput;
		
	}
	

	
	function get_html_bootstrap_mode(){
		if(!$bt_output_man=$this->get_bootstrap_output_man()){
			return "";	
		}
		return $bt_output_man->get_html_frm($this);
	
	}
	
	
	function __construct($name="frm"){
		$this->init($name);
		$this->bootstrap_mode=true;
	}
	var $ask_before_submit_debug=false;
	var $js_lng_msgs;
	var $disable_on_submit=false;
	function get_js_lng_msgs(){
		if($this->js_lng_msgs){
			return $this->js_lng_msgs;	
		}
		$this->js_lng_msgs = new mwmod_mw_jsobj_obj();
		$this->js_lng_msgs->set_prop("field_required",$this->lng_common_get_msg_txt("field_required","Dato requerido"));
		return $this->js_lng_msgs;	
	}
	final function bootstrap_after_create_reset(){
		$this->_bootstrap_after_create=new mwmod_mw_jsobj_array();
		$fnc=new mwmod_mw_jsobj_function("");
		$lng_msg=$this->get_js_lng_msgs();
		$fnc->add_fnc_arg("frmman");
		$js_code_in="frmman.lng_msg.set_params(".$lng_msg->get_as_js_val().");\n";
		if($this->disable_on_submit){
			$js_code_in.="frmman.disable_on_submit=true;\n";
		}
		if($this->ask_before_submit_debug){
			$js_code_in.="frmman.ask_before_submit_debug=true;\n";	
		}
		$fnc->set_js_code_in($js_code_in);
		$this->_bootstrap_after_create->add_data($fnc);
		$this->_bootstrap_after_create_add_inputs_fnc=new mwmod_mw_jsobj_function("");
		$this->_bootstrap_after_create->add_data($this->_bootstrap_after_create_add_inputs_fnc);
		$this->_bootstrap_after_create_add_inputs=new mwmod_mw_jsobj_array();
		/*
		if($this->ask_before_submit_debug){
			$fnc=new mwmod_mw_jsobj_function("");
			$fnc->add_fnc_arg("frmman");
			$fnc->set_js_code_in("frmman.ask_before_submit_debug=true;\n");
			$this->_bootstrap_after_create->add_data($fnc);	
		}
		*/
		
	}
	private $_bootstrap_after_create_final_fncs_list=array();
	final function add_bootstrap_after_create_final_fnc($fnc=false){
		if(!$fnc){
			$fnc= new mwmod_mw_jsobj_functionext();	
			$fnc->add_fnc_arg("frmman");
		}
		$this->_bootstrap_after_create_final_fncs_list[]=$fnc;
		return $fnc;
	}
	final function get_bootstrap_after_create_final_fncs_list(){
		return $this->_bootstrap_after_create_final_fncs_list;
		
		//return $this->_bootstrap_after_create;
	}
	
	
	final function get_bootstrap_after_create(){
		return $this->_bootstrap_after_create;
	}
	
	final function bootstrap_req_classes_reset(){
		$this->_bootstrap_req_classes=array();	
	}
	final function bootstrap_req_class_add($cod,$src=false,$params=false){
		//$this->_bootstrap_req_classes=array();
		if(!$cod){
			return false;	
		}
		if(isset($this->_bootstrap_req_classes[$cod])){
			if($this->_bootstrap_req_classes[$cod]){
				return $this->_bootstrap_req_classes[$cod];
			}
		}
			
		if(!$src){
			$c=$cod;	
		}else{
			$c=new mwmod_mw_jsobj_obj();
			$c->set_prop("name",$cod);
			$c->set_prop("src",$src);
			if($params){
				$c->set_prop("params",$params);
			}
		}
		$this->_bootstrap_req_classes[$cod]=$c;
		return $this->_bootstrap_req_classes[$cod];
	}
	final function get_bootstrap_req_classes(){
		return $this->_bootstrap_req_classes;
	}
	final function get_bootstrap_after_create_add_inputs_fnc(){
		return $this->_bootstrap_after_create_add_inputs_fnc;	
	}
	final function get_bootstrap_after_create_add_inputs(){
		return $this->_bootstrap_after_create_add_inputs;	
	}
	final function bootstrap_create_inputs_add($js){
		if($this->_bootstrap_after_create_add_inputs){
			return 	$this->_bootstrap_after_create_add_inputs->add_data($js);
		}
	}
	
	final function get_bootstrap_output_man(){
		if(!$this->_bootstrap_output_man){
			$this->_bootstrap_output_man=new mwmod_mw_bootstrap_output_man();	
		}
		return $this->_bootstrap_output_man;
	}
	
	final function set_bootstrap_output_man($man){
		$this->_bootstrap_output_man=$man;
	}
	
	function get_html(){
		if($this->bootstrap_mode){
			return $this->get_html_bootstrap_mode();	
		}else{
			return $this->get_html_normal_mode();	
			
		}
		
	}
	function get_html_in(){
		if(!$this->datafieldcreator){
			return false;	
		}
		if(is_array($this->datafieldcreator->items)){
			foreach ($this->datafieldcreator->items as $i){
				$r.=$i->get_full_input_html();	
			}
		}
		
		return $r;

	}
	
	function set_display_normal(){
		$this->style_class="";	
	}
	function set_display_inline(){
		$this->style_class="form-inline";	
	}
	function set_display_horizontal(){
		$this->style_class="form-horizontal";	
	}
	
	function get_html_for_template($template){
		if($t=$this->set_template($template)){
			return $t->get_html_frm($this);	
		}
	}
	
	function set_enctype_urlencoded(){
		$this->enctype="application/x-www-form-urlencoded";	
	}
	//tamplate
	final function set_template($t){
		if($t){
			$this->template=$t;
			return $t;
		}
	}
	function create_template(){
		if($man=$this->mainap->get_templates_man()){
			if($t=$man->create_template_frm($this)){
				return $t;	
			}
		}
		
		return new mwmod_mw_templates_frmtemplate();	
	}
	final function get_template(){
		if(!isset($this->template)){
			$this->template=$this->create_template();
		}
		return $this->template;
	}
	
	//por verificar
	
	function create_datafieldcreator(){
		$cr=new mwmod_mw_datafield_creator();
		$cr->set_frm($this);
		return $cr;
	}
	final function set_datafieldcreator($cr){
		$cr->set_frm($this);
		$this->datafieldcreator=$cr;	
	}
	final function get_datafieldcreator(){
		return $this->__get_priv_datafieldcreator();	
	}
	private function _init_datafieldcreator(){
		if(!$this->datafieldcreator){
			$this->set_datafieldcreator($this->create_datafieldcreator());	
		}
		
	}
	final function reset_inputs_js_init_code_list(){
		$this->_inputs_js_init_code_list=array();	
	}
	final function add_inputs_init_code_to_list($code){
		if(!is_array($this->_inputs_js_init_code_list)){
			return false;	
		}
		if(!$code){
			return false;	
		}
		if(is_string($code)){
			$this->_inputs_js_init_code_list[]=$code;
			return true;	
		}
		if(is_array($code)){
			foreach ($code as $c){
				$this->add_inputs_init_code_to_list($c);	
			}
		}
		
	}
	final function _get_inputs_js_init_code_list(){
		if(!is_array($this->_inputs_js_init_code_list)){
			return false;	
		}
		if(sizeof($this->_inputs_js_init_code_list)){
			return 	$this->_inputs_js_init_code_list;
		}
		
	}
	function get_inputs_js_init_code_list(){
		$this->reset_inputs_js_init_code_list();
		
		if(!$this->datafieldcreator){
			return false;	
		}
		if($c=$this->datafieldcreator->get_js_init_code_for_frm($this)){
			$this->add_inputs_init_code_to_list($c);	
		}
		return $this->_get_inputs_js_init_code_list();
	
			
	}
	function get_js_req_input_classes(){
		$r=array();
		if(!$this->datafieldcreator){
			return "";	
		}
		$this->datafieldcreator->add2jsreqclasseslist($r);
		if(!sizeof($r)){
			return "";
		}
		foreach($r as $c){
			$rr[$c]=$c;	
		}
		return implode(",",$rr);
	}
	final function get_js_after_create_fncs_extra_code_array(){
		return $this->_after_create_fncs_extra;
	}
	final function add_js_after_create($fnc){
		$this->_after_create_fncs_extra[]=$fnc;	
		
	}
	function get_js_after_create_fncs_code_array(){
		
		
		if(!$l=$this->get_inputs_js_init_code_list()){
			$l=array();
		}
		if($ll=$this->get_js_after_create_fncs_extra_code_array()){
			foreach($ll as $f){
				$l[]=$f;	
			}
		}
		return $l;
	}
	function get_js_code(){
		$js="";
		$classes=$this->get_js_req_input_classes();
		
		
		
		$js.="___mw_main_frm_manager.create_frm_manager_load_scripts('".$this->get_frm_name()."','$classes'";
		if($l=$this->get_js_after_create_fncs_code_array()){
			$js.=",\n".implode("\n,\n",$l);
		
		}
		
		$js.=")";
		return $js;
		
		//mw_array2list_echo($l);
	}
	function get_htmltag_jsscript(){
		
		if(!$js=$this->get_js_code()){
			return false;	
		}
		$html.='<script language="javascript"  type="text/jscript">'."\n";
		$html.=$js;
		$html.='</script>'."\n";
		return $html;
	}
	function get_frm_id(){
		return $this->get_frm_name();	
	}
	function get_frm_name(){
		return $this->name;	
	}
	function get_action(){
		return $this->action;	
	}
	function get_html_close(){
		$r= "</form>\n";
		
		return $r;
	}
	function get_html_open(){
		$r= "<form  id='".$this->get_frm_id()."' name='".$this->get_frm_name()."'";
		$r.=" method='".$this->method."' action='".$this->get_action()."' enctype='".$this->enctype."' autocomplete='off' ";
		if($this->bootstrap_mode){
			if($this->inline){
				$r.=" class='form-inline' ";	
			}else{
				//$r.=" class='' ";	
			}
		}
		$r.=">\n";
		//$r.=" method='".$this->method."' action='".$this->get_action()."' enctype='".$this->enctype."'>\n";	//falta que sea configurable
		return $r;
	}
	final function set_input_template($template){
		if(!$template){
			return false;	
		}
		if(!is_object($template)){
			return false;	
		}
		if(!is_a($template,"mw_datafield_template")){
			return false;	
		}
		$this->inputs_template=$template;
		return true;
	}

	final function get_input_template(){
		if(isset($this->inputs_template)){
			return $this->input_template;
		}
		if($this->subinterface){
			if($r=$this->subinterface->get_input_template()){
				return $r;	
			}
		}
		if($this->maininterface){
			if($r=$this->maininterface->get_input_template()){
				return $r;	
			}
		}
		return $this->mainap->get_input_template();

		
	}

	function set_main_interface($maininterface){
		$this->maininterface=$maininterface;
			
	}

	function set_sub_interface($subinterface){
		$this->subinterface=$subinterface;
		$this->set_main_interface($this->subinterface->maininterface);
	}

	final function __get_priv_datafieldcreator(){
		$this->_init_datafieldcreator();
		return $this->datafieldcreator; 	
	}
	final function __get_priv_name(){
		return $this->name; 	
	}
	
	final function init($name){
		if(!$name){
			$name="frm";	
		}
		if(!is_string($name)){
			$name="frm";	
		}
		$this->name=$name;
	}
	function get_html_normal_mode(){
		
		if($t=$this->get_template()){
			//$r.=get_class($t)."<br>";
			if($t->can_do_html_frm()){
				return $t->get_html_frm($this);	
			}
		}
		
		$r.=$this->get_html_before();	
		$r.=$this->get_html_open();	
		$r.=$this->get_html_in();	
		$r.=$this->get_html_close();	
		$r.=$this->get_html_after();	
		$r.=$this->get_htmltag_jsscript();	
		return $r;
	}
	function get_html_before(){
		return "";	
	}
	function get_html_after(){
		return "";	
	}
	function __call($a,$b){
		return false;	
	}
	/*
	function get_html_bootstrap_mode(){
		
		$html.="<div id='".$this->get_frm_id()."_container'>dd</div>";
		$frm=new mwmod_mw_jsobj_newobject("mw_frmadv_frm");
		$frm->set_prop("id",$this->get_frm_id());
		$frm->set_prop("name",$this->get_frm_name());
		$frm->set_prop("method",$this->method);
		$frm->set_prop("action",$this->get_action());
		$frm->set_prop("enctype",$this->enctype);
		$frm->set_prop("autocomplete","off");
		
		$html.='<script language="javascript"  type="text/jscript">'."\n";
		$html.="mw_do_fnc(function(){\n";
		
		$html.="var f=".$frm->get_as_js_val().";\n";
		$itemslist=new mwmod_mw_jsobj_array();
		
		if($this->datafieldcreator){
			if(is_array($this->datafieldcreator->items)){
				foreach ($this->datafieldcreator->items as $i){
					$i->add_2_bootstrap_js_items_list($itemslist);
					//$r.=$i->get_full_input_html();	
				}
			}
			
		}
		$html.="f.addItems(".$itemslist->get_as_js_val().");\n";

		
		$html.="f.append2containerById('".$this->get_frm_id()."_container');\n";
		//$html.="alert('sss');\n";
		$html.="})";
		$html.='</script>'."\n";
		return $html;
		
		return "xxx";
		$r.=$this->get_html_before();	
		$r.=$this->get_html_open();	
		$r.=$this->get_html_in();	
		$r.=$this->get_html_close();	
		$r.=$this->get_html_after();	
		$r.=$this->get_htmltag_jsscript();	
		return $r;
	}
	*/
	/*
	function get_html_bootstrap_mode_js_items(){
		if(!$this->datafieldcreator){
			return false;	
		}
		if(is_array($this->datafieldcreator->items)){
			foreach ($this->datafieldcreator->items as $i){
				$r.=$i->get_full_input_html();	
			}
		}
		
		return $r;

	}
	*/
	
	/*
	function get_html_old_mode(){
		$r.=$this->get_html_before();	
		$r.=$this->get_html_open();	
		$r.=$this->get_html_in();	
		$r.=$this->get_html_close();	
		$r.=$this->get_html_after();	
		$r.=$this->get_htmltag_jsscript();	
		return $r;
	}
	*/

	
}
?>