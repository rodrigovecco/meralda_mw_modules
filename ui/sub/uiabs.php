<?php
abstract class mwmod_mw_ui_sub_uiabs extends mw_apsubbaseobj{
	/**
    * MainUI
    * @access public
    * @var mwmod_mw_ui_main_def
    */
	private $maininterface;
	private $___subinterfaces=array();
	private $___all_subinterfaces;
	private $___all_subinterfaces_loaded;

	
	//private $_subinterfaces=array();
	private $parent_subinterface;
	private $current_sub_interface;

	private $current_parent_subinterface;
	private $code_for_parent;
	private $template;
	public $url_def_file;
	
	
	//private $subinterface_code;
	private $current_item;
	private $def_title;
	private $urlparams=array();
	private $cmdparams=array();
	private $code;
	public $sucods;
	public $xml_output;
	//var $order_on_main_mnu=99999999;
	//var $mnu_icon;
	public $mnuIconClass;
	
	//inicializar
	var $mnu;
	private $items_man;
	private $_items_man_cod;
	
	
	var $is_current=false;
	var $subinterface_def_code="def";
	var $bottom_alert_msg;
	
	var $js_ui_class_name="mw_ui";
	var $ui_elems_pref="uie_";
	var $selected_as_current=false;
	var $in_exec_chain=false;
	private $ui_js_init_params;
	
	var $ui_dom_elem_container;
	var $js_header_declaration;
	var $js_var_declaration;
	
	var $js_ui_obj;
	var $debug_mode=false;
	var $done=false;
	var $tooltip;
	private $___sub_ui_classes_names=array();
	private $uiSessionDataMan;
	public $omitUIGeneralContainer=false;
	public $xmlResponse;
	function get_lngmsgsmancod(){
		if($this->parent_subinterface){
			if(method_exists($this->parent_subinterface,"get_lngmsgsmancod")){
				if($code=$this->parent_subinterface->get_lngmsgsmancod()){
					return $code;
				}
			}
		}
		if(method_exists($this->mainap,"get_lngmsgsmancod")){
			if($code=$this->mainap->get_lngmsgsmancod()){
				return $code;
			}
		}
		return "def";

	}
	function createUISessionDataMan(){
		if($m=$this->maininterface->uiSessionDataMan){
			return $m->getItem("sui",$this->get_full_cod("-"));	
		}
		
		return false;
		//return new mwmod_mw_data_session_man("mainui");	
	}
	function omitUIGeneralContainer(){
		return $this->omitUIGeneralContainer;	
	}
	final function  __get_priv_uiSessionDataMan(){
		if(!isset($this->uiSessionDataMan)){
			if(!$this->uiSessionDataMan= $this->createUISessionDataMan()){
				$this->uiSessionDataMan=false;	
			}
			
		}
		return $this->uiSessionDataMan;
	}

	function do_create_subinterface_by_cod_from_class_name($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;
		}
		if(!$class_name=$this->getSubUIClassName($cod)){
			return false;	
		}
		$aman=mw_get_autoload_manager();
		if(!$aman->class_exists($class_name)){
			return false;	
		}
		$su=new $class_name($cod,$this);
		return $su;
		
		
		
		
	}
	final function addSubUIClass($cod,$className){
		$this->___sub_ui_classes_names[$cod]=$className;
		return true;
	}
	final function getSubUIClassName($cod){
		return $this->___sub_ui_classes_names[$cod]??null;
	}
	
	function get_logout_url(){
		return $this->maininterface->get_logout_url();	
	}
	function is_allowed_for_get_cmd_no_user(){
		return false;	
	}
	
	function get_sub_interface_by_dot_cod($dotcod,$sep="."){
		if(!$dotcod){
			return false;	
		}
		$list=explode($sep,$dotcod);
		if(sizeof($list)<=0){
			return false;
		}
		$cod = array_shift($list);
		if(!$su=$this->get_subinterface($cod)){
			return false;	
		}
		if(sizeof($list)<=0){
			return $su;
		}else{
			return $su->get_sub_interface_by_dot_cod(implode($sep,$list),$sep);	
		}
		
	}
	
	function get_user_ui_pref(){
		if(!$user=$this->get_current_user()){
			return false;
		}
		
		if(!$path=$this->get_full_cod("/")){
			return false;	
		}
		
		return $user->get_jsondata_item("uipref","ui/".$path);
	}
	function get_current_user(){
		return $this->get_admin_current_user();
	}
	
	
	function get_url_sub_interface_by_dot_cod($dotcod,$args=false,$file=false,$sep="."){
		if(!$full_cod=$this->get_full_cod($sep)){
			return false;
		}
		if($dotcod){
			$full_cod.=$sep.$dotcod;	
		}
		
		return $this->maininterface->get_url_sub_interface_by_dot_cod($full_cod,$file,$sep,$args);
	}
	function create_ui_dom_elem_container(){
		$container= new mwmod_mw_html_elem("div");
		$container->set_att("id",$this->get_ui_elem_id_and_set_js_init_param("container"));
		return $container;
	
	}
	
	//
	function create_ui_modal(){
		$modal= new mwmod_mw_bootstrap_html_template_modal($this->get_ui_elem_id_and_set_js_init_param("modal"),"...");
		if($modal_footer=$modal->get_key_cont("footer")){
			$modal_footer->only_visible_when_has_cont=false;	
		}
		return $modal;
	}
	function debugOutputEnabled(){
		return $this->is_debug_mode();
	}
	function is_debug_mode(){
		if(!$this->debug_mode){
			return false;	
		}
		if($this->allow("debug")){
			return true;	
		}
		return false;
	}
	function create_ui_dom_elem_iframe_and_frm_container(){
		$container= new mwmod_mw_html_elem("div");
		$id=$this->get_ui_elem_id_and_set_js_init_param("iframeandfrm");
		$idiframe=$this->get_ui_elem_id_and_set_js_init_param("iframe");
		$idfrm=$this->get_ui_elem_id_and_set_js_init_param("frmoniframe");
		$container->set_att("id",$id);
		if(!$this->is_debug_mode()){
			$container->set_style("display","none");	
		}
		$iframe= new mwmod_mw_html_elem("iframe");
		$iframe->set_att("id",$idiframe);
		$iframe->set_att("name",$idiframe);
		$iframe->set_att("width","800");
		$iframe->set_att("height","800");
		$container->add_cont($iframe);
		$container->set_key_cont("iframe",$iframe);
		
		$frm= new mwmod_mw_html_elem("form");
		$form= new  mwmod_mw_html_elem("form");
		$form->set_att("id",$idfrm);
		$form->set_att("name",$idfrm);
		$form->set_att("method","post");
		$form->set_att("target",$idiframe);
		$form->set_att("action",$this->get_exec_cmd_sxml_url());
		
		$form->set_att("enctype","multipart/form-data");

		$container->set_key_cont("form",$form);
		$container->add_cont($form);
		
		
		
		return $container;
	
	}
	
	
	function set_ui_dom_elem_id($cod,$elem=false){
		if(!$elem){
			
			$elem= new mwmod_mw_html_elem("div");
		}
		if(is_string($elem)){
			$elem= new mwmod_mw_html_elem($elem);	
		}
		
		$elem->set_att("id",$this->get_ui_elem_id_and_set_js_init_param($cod));
		return $elem;
	
	}
	
	function get_ui_dom_elem_container_empty($container=false){
		if(!$container){
			$container=	$this->create_ui_dom_elem_container();	
		}
		$this->ui_dom_elem_container=$container;
		return $this->ui_dom_elem_container;
		
	}
	function get_ui_dom_elem_container(){
		if(!$this->ui_dom_elem_container){
			$this->ui_dom_elem_container=$this->create_ui_dom_elem_container();	
		}
		return $this->ui_dom_elem_container;
	}
	
	
	function get_bt_operation_not_allowed_html_elem(){
		$msg=$this->lng_get_msg_txt("operation_not_allowed","Operación no permitida");
		$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"danger");
		return $alert;	
	}
	function output_bt_operation_not_allowed_html_elem(){
		$e=$this->get_bt_operation_not_allowed_html_elem();
		echo $e->get_as_html();	
	}
	
	
	//js
	function create_modal_js_inputs_footer_cancel_ok_fnc($js_modalpopulator=false){
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_fnc_arg("modalpopulator");
		$this->create_modal_js_inputs_footer_cancel_ok($fnc);
		if($js_modalpopulator){
			$js_modalpopulator->set_prop("create_footer_input",$fnc);	
		}
		return $fnc;
		//$this->create_new_doc_js_inputs_footer($fnc);
	
	}
	
	function create_modal_js_inputs_footer_cancel_ok($js){
		
		
		$jsinputgr=new mwmod_mw_jsobj_newobject("mw_datainput_item_btnsgroup");
		$js->add_cont("var grfooter=".$jsinputgr->get_as_js_val().";\n");
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_btn");
		$jsinput->set_prop("lbl",$this->lng_common_get_msg_txt("cancel","Cancelar"));
		$jsinput->set_prop("display_mode","danger");
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_cont($js->get_arg_by_index().".hide();\n");
		$jsinput->set_prop("onclick",$fnc);
		
		$js->add_cont("grfooter.addItem(".$jsinput->get_as_js_val().",'cancel');\n");
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_btn");
		$jsinput->set_prop("lbl",$this->lng_common_get_msg_txt("accept","Aceptar"));
		$jsinput->set_prop("display_mode","success");
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_cont($js->get_arg_by_index().".validate_and_submit_body_frm();\n");
		$jsinput->set_prop("onclick",$fnc);
		
		$js->add_cont("grfooter.addItem(".$jsinput->get_as_js_val().",'ok');\n");

		
		
		$js->add_cont($js->get_arg_by_index().".set_footer_input(grfooter);\n");
		
	}
	
	
	function create_js_man_ui_header_declaration_item(){
		$cod=$this->get_js_ui_man_name();
		$js=$this->get_js_header_declaration();
		$item= new mwmod_mw_html_manager_item_jscus($cod,$js);
		return $item;	
	}
	function create_js_header_declaration(){
		$js= new mwmod_mw_jsobj_codecontainer();
		$vardec=$this->get_js_var_declaration();
		$js->add_cont($vardec);
		
		return $js;
		
		
		
		
			
	}
	function get_js_header_declaration(){
		if(!isset($this->js_header_declaration)){
			$this->js_header_declaration=$this->create_js_header_declaration();
		}
		return $this->js_header_declaration;
	}
	
	function get_js_ui_man_name(){
		return "uiman_".$this->get_full_cod("_");	
	}
	function new_ui_js(){
		$js= new mwmod_mw_jsobj_newobject($this->js_ui_class_name,$this->get_ui_js_info());
		return $js;	
	}
	function get_js_var_declaration(){
		if(!isset($this->js_var_declaration)){
			$varname=$this->get_js_ui_man_name();
			$js_obj=$this->get_js_ui_obj();
			$this->js_var_declaration=new mwmod_mw_jsobj_vardeclaration($varname,$js_obj);
		}
		return $this->js_var_declaration;
	}
	
	function get_js_ui_obj(){
		if(!isset($this->js_ui_obj)){
			$this->js_ui_obj=$this->new_ui_js();
		}
		return $this->js_ui_obj;
	}
	
	
	
	function get_ui_elem_id_and_set_js_init_param($cod){
		$r=$this->get_ui_elem_id($cod);
		$js=$this->__get_priv_ui_js_init_params();
		$js->set_prop("uielemsids.".$cod,$r);
		return $r;
	}
	function get_ui_elem_id($cod){
		
		return $this->ui_elems_pref.$this->get_full_cod("_")."-".$cod;	
		//return $this->ui_elems_pref.$this->code."_".$cod;	
	}
	
	final function  __get_priv_ui_js_init_params(){
		if(!isset($this->ui_js_init_params)){
			$this->ui_js_init_params= new mwmod_mw_jsobj_obj();	
		}
		return $this->ui_js_init_params;
	}
	function get_ui_js_info(){
		$r=array();
		$r["cod"]=$this->get_code_for_parent();
		$r["full_cod"]=$this->get_full_cod(".");
		$r["title"]=$this->get_title();
		$r["url"]=$this->get_url();
		$r["xmlurl"]=$this->get_exec_cmd_sxml_url(false);
		$r["dlurl"]=$this->get_exec_cmd_dl_url(false);
		$r["mainui"]=$this->maininterface->get_ui_js_info_for_child($this);
		$r["debug_mode"]=$this->is_debug_mode();
		$r["uielemspref"]=$this->ui_elems_pref.$this->get_full_cod("_")."-";
		
		return $r;
			
	}
	
	
	///
	function execfrommain_getcmd_sxml_debug($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(true,"Debug");
		$xml->set_prop("class",get_class($this));
		$xml->set_prop("params",$params);
		$xml->set_prop("filename",$filename);
		$info=$this->get_ui_js_info();
		$xml->set_prop("info",$info);
		
		$xmljs=new mwmod_mw_data_xml_js("jsinfo",$info);
		
		$xml->add_sub_item($xmljs);
		
		//$xml->set_prop("post",$_POST);
		//$xml->set_prop("get",$_GET);
		
		$xml->root_do_all_output();
			
	}
	function new_getcmd_sxml_answer($ok=true,$msg=""){
		$xmlroot=new mwmod_mw_data_xml_root();
		$xml=$xmlroot->get_sub_root();
		$xml->set_prop("ok",$ok);
		$xml->set_prop("msg",$msg);
		$this->xmlResponse=$xml;
		return $xml;
	
	}
	//rvh 20240214
	function before_exec_get_cmd($params=array()){
		//extender. cargar acá objetos dependientes
	}
	
	function execfrommain_getcmd_sxml($cmdcod,$params=array(),$filename=false){
		$this->before_exec_get_cmd($params);
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			$xml=$this->new_getcmd_sxml_answer(false,"Invalid command");
			$xml->root_do_all_output();
			return false;	
		}
		$method="execfrommain_getcmd_sxml_$cmdcod";
		if(!method_exists($this,$method)){
			$xml=$this->new_getcmd_sxml_answer(false,"Method $method does not exist on ".get_class($this));
			$xml->root_do_all_output();
			return false;
				
		}
		return $this->$method($params,$filename);
	}
	function execfrommain_getcmd_dl_error($params=array(),$filename=false,$errmsg=false){
		if($this->is_debug_mode()){
			echo "Error";
			if($errmsg){
				echo " $errmsg";	
			}
			$data=array(
				"params"=>$params,
				"filename"=>$filename,
				"uifullcod"=>$this->get_full_cod(),
				"uiclass"=>get_class($this),
				
			);
			mw_array2list_echo($data);
			
		}
		return false;
	}
	function execfrommain_getcmd_dl_test($params=array(),$filename=false){
		$data=array(
			"params"=>$params,
			"filename"=>$filename,
			"uifullcod"=>$this->get_full_cod(),
			"uiclass"=>get_class($this),
			
		);
		mw_array2list_echo($data);
		return false;
	}
	
	function execfrommain_getcmd_dl($cmdcod,$params=array(),$filename=false){
		$this->before_exec_get_cmd($params);
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			$errmsg="invalid command";
			return $this->execfrommain_getcmd_dl_error($params,$filename,$errmsg);	
		}
		$method="execfrommain_getcmd_dl_$cmdcod";
		if(!method_exists($this,$method)){
			$errmsg=get_class($this)." has no method $method";
			return $this->execfrommain_getcmd_dl_error($params,$filename,$errmsg);	
				
		}
		return $this->$method($params,$filename);
	}
	
	function set_current_subinterface_for_getcmd($cods=false,$params=array(),$filename=false){
		if(!$cods){
			return false;	
		}
		if(!is_string($cods)){
			return false;	
		}
		$cods=explode("-",$cods,2);
		if(!$sub_ui=$this->get_subinterface($cods[0]??null)){
			return false;	
		}
		if(!$sub_ui->is_allowed_for_get_cmd($cods[1]??null,$params,$filename)){
			return false;	
		}
		if(!$this->set_current_subinterface($sub_ui,false)){
			return false;	
		}
		if(!isset($cods[1]) or !$cods[1]){
			return $sub_ui;	
		}
		return $sub_ui->set_current_subinterface_for_getcmd($cods[1],$params,$filename);
		

	}
	function is_allowed_for_get_cmd($sub_ui_cods=false,$params=array(),$filename=false){
		return $this->is_allowed();	
	}
	function getCmdXmlEnabled($cmdcod,$params=array()){
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			return false;	
		}
		$method="execfrommain_getcmd_sxml_$cmdcod";
		if(method_exists($this,$method)){
			return true;
				
		}
		return false;
	}
	function get_exec_cmd_sxml_url_ifEnabled($xmlcmd="debug",$params=array()){
		if(!$this->getCmdXmlEnabled($xmlcmd)){
			return false;	
		}
		return $this->get_exec_cmd_sxml_url($xmlcmd,$params);
	}
	//rvh20240214
	function get_exec_cmd_sxml_url($xmlcmd="debug",$params=array()){
		if($this->maininterface){
			$params=$this->get_cmd_params($params);//new
			return $this->maininterface->get_exec_cmd_sxml_url($xmlcmd,$this,$params);	
		}
		
		
	}
	function get_exec_cmd_dl_url($dlcmd="test",$params=array(),$realfilename=false){
		if($this->maininterface){
			$params=$this->get_cmd_params($params);//new
			return $this->maininterface->get_exec_cmd_dl_url($dlcmd,$this,$params,$realfilename);	
		}
		
		
	}
	function get_cmd_params($args=array()){
		$def=$this->get_cmd_param();
		if(!is_array($args)){
			return $def;	
		}
		return  mw_array_bydefault($args,$def);
	}

	final function reset_cmd_params(){
		$this->cmdparams=array();
		
	}
	final function get_cmd_param($key=false){
		if(!$key){
			return 	$this->cmdparams;
		}
		return mw_array_get_sub_key($this->cmdparams,$key);	
	}
	final function set_cmd_param($key,$val){
		mw_array_set_sub_key($key,$val,$this->cmdparams);	
	}
	////////

	
	function set_bottom_alert_msg($msg=false){
		$this->bottom_alert_msg=$msg;	
	}
	function output_bottom_alert_msg(){
		if(!$this->bottom_alert_msg){
			return false;
		}
		if(is_object($this->bottom_alert_msg)){
			if(method_exists($this->bottom_alert_msg,"get_as_html")){
				echo $this->bottom_alert_msg->get_as_html();
			}
			return;
		}else{
			echo $this->bottom_alert_msg;	
		}
	}
	//subinterface mnu
	//un menu que crea un padre con is_responsable_for_sub_interface_mnu true para sí y sus hijos
	function add_2_sub_interface_mnu($mnu){
		//ver mwmod_mw_ui_debug_htmltemplate
		$this->add_2_mnu($mnu);
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		//ver mwmod_mw_ui_debug_uidebug
	}
	function get_sub_interface_mnu_from_parent_responsable(){
		if($p=$this->get_parent_responsable_for_sub_interface_mnu()){
			return $p->create_sub_interface_mnu_for_sub_interface($this);
		}
	}
	function get_parent_responsable_for_sub_interface_mnu(){
		if($this->is_responsable_for_sub_interface_mnu()){
			return $this;	
		}
		if($this->parent_subinterface){
			return $this->parent_subinterface->get_parent_responsable_for_sub_interface_mnu();
		}
	}
	function is_responsable_for_sub_interface_mnu(){
		return false;	
	}
	//bootstrap
	function exec_page_body_sub_interface_on_main_template_bootstrap($main_ui_template){
		return $main_ui_template->exec_page_body_sub_interface_bootstrap($this);
	}
	function can_page_body_sub_interface_on_main_template_bootstrap(){
		return true;
	}
	
	function init($cod,$maininterface){
		$this->set_main_interface($maininterface);	
		$this->set_code($cod);
	}
	function is_current(){
		return $this->is_current;	
	}
	function is_in_exec_chain(){
		if($this->in_exec_chain){
			return true;	
		}
		return $this->is_current();	
	}
	
	function init_as_main_or_sub($cod,$parent){
		if(is_a($parent,"mwmod_mw_ui_main_uimainabs")){
			$this->init($cod,$parent);
		}else{
			$this->init_as_subinterface($cod,$parent);	
		}
	}
	function init_as_subinterface($cod,$parent){
		$maininterface=$parent->maininterface;
		$this->init($cod,$maininterface);
		$this->set_lngmsgsmancod_by_obj($parent);
		$this->set_parent_sub_interface($parent);
		$this->added_as_child($cod,$parent);
	}
	//items man
	function load_items_man(){
		if(!$cod=$this->get_items_man_cod()){
			return false;	
		}
		return $this->mainap->get_submanager($cod);
	}
	
	final function get_items_man(){
		if(isset($this->items_man)){
			return $this->items_man;
		}
		$this->items_man=false;
		if($man=$this->load_items_man()){
			$this->items_man=$man;	
		}
		return $this->items_man;
	}
	final function set_items_man($man){
		$this->items_man=$man;
		$this->after_set_items_man($man);
	}
	function after_set_items_man($man){
		$this->set_lngmsgsmancod($man->lngmsgsmancod);	
	}
	final function set_items_man_cod($cod=false){
		return $this->_items_man_cod=$cod;
	}
	final function get_items_man_cod(){
		return $this->_items_man_cod;
	}
	final function __get_priv_items_man(){
		return $this->get_items_man(); 	
	}
	
	//url
		
	function get_url_subinterface($code=false,$args=false,$file=false){
		if($code){
			if(!is_array($args)){
				$args=array();
			}
			$args[$this->get_subinterface_request_var()]=$code;
		}
		return $this->get_url($args,$file);
	}
	function get_url($args=false,$file=false){
		$url=$this->get_url_base($file);
		if($args1=$this->get_url_params($args)){
			if($q=mw_array2urlquery($args1)){
				$url.="?".$q;	
			}
		}
		return $url;
	}
	function get_url_params($args=false){
		$def=$this->get_url_param();
		if(!is_array($args)){
			return $def;	
		}
		return  mw_array_bydefault($args,$def);
	}
	function init_child_url_params($child_cod,$child){
		$args=array();
		$args[$this->get_subinterface_request_var()]=$child_cod;
		
		if(!$params=$this->get_url_params($args)){
			return false;
		}
		foreach($params as $cod=>$v){
			$child->set_url_param($cod,$v);	
		}
	}

	function init_url_params(){
		if($this->current_parent_subinterface){
			$this->current_parent_subinterface->init_child_url_params($this->code_for_parent,$this);
		}else{
			$this->set_url_param($this->maininterface->ui_var_name,$this->code);	
		}

		//$this->set_url_param("interface",$this->code);
	}
	final function reset_url_params(){
		$this->urlparams=array();
		$this->init_url_params();	
	}
	final function set_url_param($key,$val){
		mw_array_set_sub_key($key,$val,$this->urlparams);	
	}
	final function get_url_param($key=false){
		if(!$key){
			return 	$this->urlparams;
		}
		return mw_array_get_sub_key($this->urlparams,$key);	
	}
	function get_url_def_file(){
		return $this->url_def_file;	
	}
	function get_url_base($file=false){
		if(!$file){
			$file=$this->get_url_def_file();	
		}
		
		return $this->maininterface->get_url_base($file);	
	}

	//subinerfaces
	

	final function set_no_subinterface(){
		$this->current_sub_interface=false;
			
	}
	function go_to_parent(){
		if($this->parent_subinterface){
	
			$this->parent_subinterface->set_no_subinterface();	
		}
	}
	
	function on_subinterface_not_allowed(){
		$this->set_no_subinterface();
			
	}
	final function set_current_subinterface($item=false,$check_allowed=true){
		if(!$item){
			return false;	
		}
		if($check_allowed){
			if(!$item->is_allowed()){
				
				return false;
			}
		}
		$this->current_sub_interface=$item;
		$item->selected_as_current=true;
		
		
		return $this->current_sub_interface;
	}

	function set_current_subinterface_by_code($code=false,$check_allowed=true){
		if(!$code){
			return $this->on_subinterface_not_allowed();
		}
		if(!$si=$this->get_subinterface($code)){
			return $this->on_subinterface_not_allowed();
		}
		if($check_allowed){
			if(!$si->is_allowed()){
				return $this->on_subinterface_not_allowed();
			}
		}
		return $this->set_current_subinterface($si,false);
	}
	function get_sub_insterface_request_code(){
		//fix here!!!
		


		$key=$this->get_subinterface_request_var();
		if($key and isset($_REQUEST[$key])){
			if($code=$_REQUEST[$key]){
				return $code;
			}
			
		}
		return $this->subinterface_def_code;	
	}
	
	final function get_subinterface($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;
		}
		$this->init_all_subinterfaces_once();
		if(isset($this->___subinterfaces[$cod])){
			return 	$this->___subinterfaces[$cod];
		}
		if($su=$this->create_subinterface($cod)){
			return $su;
		}
		return false;
		
	}
	final function add_subinterface($item,$cod=false){
		if(!$cod){
			$cod=$item->code;	
		}
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;
		}
		//$this->__subinterfaces[$cod]=$item;
		$this->___subinterfaces[$cod]=$item;
		$item->added_as_child($cod,$this);
		return $item;
		
	}
	function add_new_subinterface($subinterface){
		
		return $this->add_subinterface($subinterface,$subinterface->code);
		
	}

	function load_all_subinterfases(){
		//for children
	}
	
	final function init_all_subinterfaces_once(){
		if(!$this->___all_subinterfaces_loaded){
			$this->get_all_subinterfaces();
		}
		
	}
	final function get_all_subinterfaces(){
		if($this->___all_subinterfaces_loaded){
			return $this->___all_subinterfaces;
		}
		$this->___all_subinterfaces_loaded=true;
		$this->load_all_subinterfases();	
		$this->___all_subinterfaces=array();
		if(!$su=$this->get_subinterfaces()){
			return $this->___all_subinterfaces;
		}
		foreach($su as $cod=>$item){
			$this->___all_subinterfaces[$cod]=$item;	
		}
		return $this->___all_subinterfaces;
	}
	final function get_subinterfaces(){
		return $this->___subinterfaces;
	}

	//formulario e inputs
	function get_frm_action($args=false,$file=false){
		$code=$this->get_subinterface_code();
		return $this->get_url_subinterface($code,$args,$file);	
	}
	function new_frm($name="frm"){
		$frm= new mwmod_mw_datafield_frm($name);
		if(!$t=$this->get_template()){
			$frm->set_template($t->new_frm_template());	
		}
		//$frm->set_sub_interface($this);
		$frm->action=$this->get_frm_action();
		return $frm;
	}
	function get_html_frm_from_datafieldcreator($cr){
		if(!$cr){
			return false;
		}
		if(!$frm=$this->new_frm()){
			return false;
		}
		$frm->set_datafieldcreator($cr);
		return $frm->get_html();
	
	}
	function new_datafield_creator(&$items=array()){
		$cr=new mwmod_mw_datafield_creator($items);
		//$cr->set_sub_interface($this);
		return $cr;
	}
	//usuario
	function get_admin_current_user(){
		return $this->maininterface->get_admin_current_user();	
	}
	//menu superior
	function is_allowed_on_mnu(){
		return $this->is_allowed();	
	}
	
	function add_as_sub_mnu_item($parent_mnu_item){
		$item=new mwmod_mw_mnu_mnuitem($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$parent_mnu_item,$this->get_url());
		if($this->is_current()){
			$item->set_active(true);
				
		}
		if($this->tooltip){
			$item->tooltip=	$this->tooltip;
		}
		return $parent_mnu_item->add_item_by_item($item);
			
	}
	function add_2_side_mnu($mnu,$checkallowed=true){
		return $this->add_2_mnu($mnu,$checkallowed);	
	}
	function add_2_mnu($mnu,$checkallowed=true){
		if(!$mnu){
			return false;	
		}
		if($checkallowed){
			if(!$this->is_allowed_on_mnu()){
				return false;
			}
		}
		if(!$item=$mnu->add_new_item($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$this->get_url())){
			
			return false;	
		}
		if($this->is_current()){
			$item->set_active(true);	
		}
		$this->prepare_mnu_item($item);
		
		
		if($mnu->allow_sub_menus()){
			$this->add_sub_mnus($item,$checkallowed);	
		}
		
		return $item;
	}
	function add_sub_mnus($mnuitem,$checkallowed=true){
		//agregar aca
	}
	function prepare_mnu_item($item){
		if($this->mnuIconClass){
			$item->addInnerHTML_icon($this->mnuIconClass);
		}
		//aca puede colocarse icono	
	}
	function get_cod_for_mnu(){
		return $this->get_code_for_parent();	
	}
	function get_mnu_lbl(){
		return $this->get_name();	
	}
	//menú interno
	private $mnu_man;
	
	final function get_mnu_man(){
		if(!isset($this->mnu_man)){
			$this->mnu_man=$this->create_mnu_man();
			$this->mnu_man_on_create($this->mnu_man);
		}
		return $this->mnu_man;
	}
	function create_mnu_man(){
		$m=new mwmod_mw_mnu_man();
		$m->set_sub_interface($this);
		return $m;
	}
	function mnu_man_on_create($mnu_man){
		$mnu=$mnu_man->get_item("sumnu");
		$this->add_mnu_items($mnu);	
	}
	final function __get_priv_mnu_man(){
		return $this->get_mnu_man(); 	
	}
	
	function create_mnu(){
		$m=$this->get_mnu_man();
		return $m->get_item("sumnu");
		
		
		//return new mwmod_mw_mnu_ui($this);	
	}
	function get_mnu(){
		if(!isset($this->mnu)){
			if($this->mnu=$this->create_mnu()){
				//$this->add_mnu_items($this->mnu);	
			}
		}
		return $this->mnu;
	}
	function add_mnu_items($mnu){
		//
	}
	function get_subinterfaces_by_code($code,$checkallowed=true){
		if(!$code){
			return false;
		}
		if(!is_array($code)){
			$code=explode(",",$code);
		}
		$r=array();
		foreach($code as $c){
			$cc=trim($c);
			if($su=$this->get_subinterface($cc)){
				if($checkallowed){
					if($su->is_allowed()){
						$r[$cc]=$su;	
					}
				}else{
					$r[$cc]=$su;		
				}
			}
		}
		if(sizeof($r)){
			return $r;	
		}
			
	}
	function add_sub_interface_to_mnu_by_code($mnu,$code){
		if(!$code){
			return false;
		}
		if(!is_array($code)){
			$code=explode(",",$code);
		}
		$r=array();
		foreach($code as $c){
			$cc=trim($c);
			$r[$cc]=$this->add_sub_interface_to_mnu_by_code_item($mnu,$cc);	
		}
		
		return $cc;
	}
	function add_sub_interface_to_mnu_by_code_item($mnu,$code){
		if(!$si=$this->get_subinterface($code)){
			return false;	
		}
		
		return $si->add_2_mnu($mnu);
	}

	//exec
	function do_exec_no_sub_interface(){
		//extender!!!;
	}
	function before_exec(){
		$this->add_req_js_scripts();	
		$this->add_req_css();
	}
	function add_req_js_scripts(){
		//ver mwmod_mw_ui_debug_frm	
		//altarnativa a esto es prepare_before_exec_no_sub_interface
	}
	function add_req_css(){
		//ver mwmod_mw_ui_debug_frm	
		//altarnativa a esto es prepare_before_exec_no_sub_interface
	}
	function prepare_before_exec_no_sub_interface(){
		//$p=new mwmod_mw_html_manager_uipreparers_default($this);
		//$p->preapare_ui();
	}
	function prepare_and_do_exec_no_sub_interface(){
		$this->prepare_before_exec_no_sub_interface();
		$this->do_exec_no_sub_interface();
	}
	function do_exec(){
		if(!$this->is_allowed()){
			return false;	
		}
		$this->in_exec_chain=true;
		$this->before_exec();
		if($si=$this->set_current_subinterface_by_code($this->get_sub_insterface_request_code())){
			if($si->is_allowed()){
				$si->do_exec();	
			}
		}else{
			$this->prepare_and_do_exec_no_sub_interface();	
		}
	}
	//exec output
	function do_exec_page_in_as_sub(){
		if(!$template=$this->get_template()){
			$this->do_exec_page_in();
			return;	
		}
		$template->exec_page_full_body_sub_interface();
		
	}
	function get_this_or_final_current_subinterface(){
		if($this->current_sub_interface){
			if($this->current_sub_interface->is_allowed()){
				if($r=$this->current_sub_interface->get_this_or_final_current_subinterface()){
					return $r;	
				}
			}
		}
		$this->is_current=true;
		return $this;
	}
	function do_exec_page_single_mode(){
		$this->do_exec_page();	
	}
	function do_exec_on_template($template){
		if($this->current_sub_interface){
			if($this->current_sub_interface->is_allowed()){
				//echo "ss";
				return $this->current_sub_interface->do_exec_page_in_as_sub();	
			}
		}
		$this->do_exec_page_in();
			
	}
	function omit_header(){
		return false;	
	}
	function do_exec_on_page_in_on_maintemplate($maintemplate){
		$this->do_exec_page_in();
			
	}
	function do_exec_page(){
		$this->do_exec_page_in();
	}
	function do_exec_page_in(){
		//extender
	}
	
	//permisos

	function is_allowed(){
		return false;
		//return $this->allow("admin");	
	}
	function allow($action,$params=false){
		return $this->maininterface->allow($action,$params);	
	}
	//template
	function create_template($main_interface_template=false){
		if(!$main_interface_template){
			$main_interface_template=$this->maininterface->get_template();	
		}
		$t=$main_interface_template->new_sub_interface_template($this);
		return $t;
	}
	final function get_template($main_interface_template=false){
		if(!isset($this->template)){
			$this->template=$this->create_template($main_interface_template);
		}
		return $this->template;
	}
	
	final function __get_priv_template(){
		return $this->get_template(); 	
	}
	//info
	function get_name(){
		if($r=$this->get_title()){
			return $r;	
		}
		return $this->code;
	}
	function __toString(){
		return $this->get_name()."";	
	}
	
	function get_title(){
		return $this->__get_priv_def_title();	
	}
	final function __get_priv_def_title(){
		if(!$this->def_title){
			return get_class($this);	
		}
		
		return $this->def_title; 	
	}
	final function set_def_title($tit){
		$this->def_title=$tit;
	}
	
	
	//por verificar
	
	
	/*
	function do_exec_page_direct(){
		$this->url_def_file="interface.php";
		$this->do_exec_page_in();	
	}
	*/
	function get_html_for_parent_chain_on_child_title(){
		$url=$this->get_url();
		return "<a href='$url'>".$this->get_title_for_box()."</a>";
	}
	function get_html_parents_chain(){
		$l=array();
		if($list=$this->get_parents_chain()){
			foreach($list as $p){
				$l[]=$p->get_html_for_parent_chain_on_child_title();	
			}
		}
		$l[]=$this->get_html_for_parent_chain_on_child_title();
		return implode(" - ",$l);
		
	}
	function get_html_parents_route($sep=" - "){
		$l=array();
		if($list=$this->get_parents_chain()){
			foreach($list as $p){
				if($h=$p->get_html_for_parent_chain_on_child_title()){
					$l[]=$h;
				}
				//$p->get_html_for_parent_chain_on_child_title();	
			}
		}
		if(!sizeof($l)){
			return "";
		}
		//$l[]=$this->get_html_for_parent_chain_on_child_title();
		return implode($sep,$l);
		
	}
	
	function get_title_for_box_html(){
		return $this->get_html_parents_chain();
			
	}
	function get_selected_ui_header_title_and_sub_title($title,$subtitle=false){
		if(!$subtitle){
			$subtitle=$this->get_title();	
		}
		return $title."<h5>$subtitle</h5>";
	}
	function isResponsableForSubInterfacesHeaderTitle(){
		return false;
	}
	function getHeaderTitleForSubUIWhenResponsable(){
		return $this->get_title();
	}
	function getUIResponsableForSubInterfacesHeaderTitle(){
		if($this->isResponsableForSubInterfacesHeaderTitle()){
			return $this;
		}
		if($this->parent_subinterface){
			return $this->parent_subinterface->getUIResponsableForSubInterfacesHeaderTitle();
		}
		return false;

	}
	function getParentUIResponsableForSubInterfacesHeaderTitle(){
		if($this->parent_subinterface){
			return $this->parent_subinterface->getUIResponsableForSubInterfacesHeaderTitle();
		}
		return false;
	}
	function get_selected_ui_header_title(){
		if($parent=$this->getParentUIResponsableForSubInterfacesHeaderTitle()){
			if($t=$parent->getHeaderTitleForSubUIWhenResponsable()){
				return $t;
			}
		}
		return $this->get_title();	
	}
	function get_selected_ui_header_subtitle(){
		if($parent=$this->getParentUIResponsableForSubInterfacesHeaderTitle()){
			return $this->get_title();	
		}
		return false;

		
	}

	function get_title_for_box(){
		//return $this->get_html_parents_chain();
		
		return $this->get_title();	
	}
	function childrenInheritPermissions(){
		return false;
	}
	function get_full_cod($sep="-"){
		if(!$sub_uis=$this->get_parents_chain(true,true)){
			return false;
		}
		$suicods=array();
		foreach($sub_uis as $ui){
			$suicods[]=$ui->get_code_for_parent();	
		}
		if(!sizeof($suicods)){
			return false;	
		}
		return implode($sep,$suicods);

	}

	function get_parents_chain($top2bot=true,$addthis=false){
		$r=array();
		if($addthis){
			$r[]=$this;	
		}
		if($p=$this->__get_priv_current_parent_subinterface()){
			$p->add2parents_chain($r);
		}
		if($top2bot){
			return array_reverse($r);	
		}
		return $r;
	}
	function add2parents_chain(&$list){
		if(!$list){
			$list=array();	
		}
		$list[]=$this;
		if($p=$this->__get_priv_current_parent_subinterface()){
			$p->add2parents_chain($list);
		}
		return $list;
	}

	/////
	//function get_
	final function set_parent_sub_interface($parent){
		$this->parent_subinterface=$parent;
		$this->current_parent_subinterface=$parent;	
	}
	final function set_current_parent_sub_interface($cod,$parent){
		$this->code_for_parent=$cod;
		$this->current_parent_subinterface=$parent;	
	}
	final function __get_priv_code_for_parent(){
		if(!$this->code_for_parent){
			return $this->code; 	
		}
		return $this->code_for_parent; 	
	}
	final function __get_priv_current_parent_subinterface(){
		if(!$this->current_parent_subinterface){
			return $this->parent_subinterface; 	
		}
		return $this->current_parent_subinterface; 	
	}
	final function __get_priv_parent_subinterface(){
		return $this->parent_subinterface; 	
	}
	function added_as_child($cod,$parent){
		$this->set_current_parent_sub_interface($cod,$parent);
		$this->reset_url_params();
		return true;	
	}
	function get_code_for_parent(){
		if($this->code_for_parent){
			return $this->code_for_parent;	
		}
		return $this->code;
	}

	final function __get_priv_current_sub_interface(){
		return $this->current_sub_interface; 	
	}
	

	
	function create_subinterface($cod){
		return $this->do_create_subinterface($cod);
		
	}
	function allowcreatesubinterfacechildbycode(){
		return false;	
	}
	
	function subinterface_child_created($cod,$item){
		$this->add_subinterface($item,$cod);
		
		return $item;	
	}
	function do_create_subinterface($cod){
		if(!$this->allowcreatesubinterfacechildbycode()){
			return false;	
		}
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;
		}
		$method="_do_create_subinterface_child_$cod";
		if(method_exists($this,$method)){
			if($item=$this->$method($cod)){
				return $this->subinterface_child_created($cod,$item);
			}
		}else{
			if($item=$this->do_create_subinterface_by_cod_from_class_name($cod)){
				return $this->subinterface_child_created($cod,$item);
			}
				
		}
		
	}

	
	
	
	function get_subinterface_request_var(){
		return $this->maininterface->get_subinterface_request_var_by_deep($this->get_deep()+1);
		/*
		if(!$deep=$this->get_deep()){
			return "subinterface";	
		}else{
			return "subinterface$deep";		
		}
		*/
	}
	function get_deep(){
		if(!$this->parent_subinterface){
			return 0;	
		}
		return $this->parent_subinterface->get_deep()+1;
	}
	
	
	function get_html_link($lbl,$subinterface=false,$args=false,$file=false){
		$url=$this->get_url_subinterface($subinterface,$args,$file);
		return "<a href='$url'>$lbl</a>";	
	}
	
	
	function get_input_template(){
		return $this->maininterface->get_input_template(); 	
	}
	function new_tbl_template(){
		$tm=$this->get_template();
		return $tm->new_tbl_template();	
	}

	
	final function after_code_ok(){
		
		$this->reset_url_params();
		
	}
	function after_code_ok_sub(){
		//para extender, se ejecuta después de 	after_code_ok
	}
	final function set_code($cod){
		if(isset($this->code)){
			return false;	
		}
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;		
		}
		if(!$this->check_str_key_alnum_underscore($cod)){
			return false;		
		}
		if(!$cod=basename($cod)){
			return false;		
		}
		$this->code=$cod;
		$this->after_code_ok();
		
	}
	final function set_main_interface($maininterface){
		$ap=$maininterface->mainap;
		$this->set_mainap($ap);	
		$this->maininterface=$maininterface;
	}
	
	
	function get_mnu_icon(){
		return $this->mnu_icon;	
	}
	final function __get_priv_code(){
		return $this->code; 	
	}
	/**
	* @return mwmod_mw_ui_main_def MainUI
	*/
	final function __get_priv_maininterface(){
		return $this->maininterface; 	
	}
	//current item
	final function set_current_item($item){
		$this->current_item=$item;	
	
	}
	final function get_current_item(){
		return $this->current_item; 	
	}
	final function __get_priv_current_item(){
		return $this->current_item; 	
	}

	function __call($a,$b){
		return false;	
	}
	
}
?>