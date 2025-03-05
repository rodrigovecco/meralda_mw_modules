<?php
abstract class mwmod_mw_ui_main_uimainabs extends mw_apsubbaseobj{
	private $___subinterfaces=array();
	private $___all_subinterfaces;
	private $___all_subinterfaces_loaded;
	private $current_subinterface;
	private $jsmanager;
	//var $ui_var_name="interface";
	//var $sub_ui_var_name="subinterface";
	var $ui_var_name="ui";
	var $sub_ui_var_name="sui";
	
	var $subinterface_def_code="def";
	private $template;
	var $mnu;
	var $lat_mnu;
	var $url_base_path="/admin/";
	private $mnu_man;
	public $sucods;
	public $su_cods_for_side;
	
	var $name;
	var $sessionCheckTimeout=0;
	
	var $js_ui_man_name="main_ui";
	var $js_ui_class_name="mw_main_ui";
	var $js_var_declaration;
	var $js_ui_obj;
	var $js_header_declaration;
	var $js_bottom;
	var $js_init_on_bottom;
	private $ui_js_init_params;
	var $jquery_enabled=true;
	var $dx_enabled=true;
	
	var $logout_script_file;
	
	
	
	private $uiSessionDataMan;
	function createUISessionDataMan(){
		return false;
		//return new mwmod_mw_data_session_man("mainui");	
	}
	final function  __get_priv_uiSessionDataMan(){
		if(!isset($this->uiSessionDataMan)){
			if(!$this->uiSessionDataMan= $this->createUISessionDataMan()){
				$this->uiSessionDataMan=false;	
			}
			
		}
		return $this->uiSessionDataMan;
	}

	function evaluateHTTPSendRedirect(){
		if(!$this->forceHTTPs()){
			return;	
		}
		if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off"){
			$redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $redirect);
			exit();
		}
	}
	
	function forceHTTPs(){
		//todo by app cfg
		return false;	
		
	}
	
	function get_logout_url(){
		if($this->logout_script_file){
			return 	 $this->get_url(false,$this->logout_script_file);
		}
		
		return $this->get_url(array("logout"=>"true"));
	}
	function exec_user_logout(){
		if($man=$this->get_admin_user_manager()){
			$man->logout();	
		}
		session_destroy();
		$url=$this->get_after_logout_url();
		
		ob_end_clean();
		header("Location: $url");
		die();

			
	}
	function get_after_logout_url(){
		return $this->get_url();	
	}

	
	function enable_session_check($timeout=5000){
		$this->sessionCheckTimeout=$timeout;
		
	}
	function create_js_man_ui_header_declaration_item(){
		$cod=$this->get_js_ui_man_name();
		$js=$this->get_js_header_declaration();
		
		
		$item= new mwmod_mw_html_manager_item_jscus($cod,$js);
		return $item;	
	}
	function get_js_header_declaration(){
		if(!isset($this->js_header_declaration)){
			$this->js_header_declaration=$this->create_js_header_declaration();
		}
		return $this->js_header_declaration;
	}
	
	function create_js_header_declaration(){
		$js= new mwmod_mw_jsobj_codecontainer();
		$vardec=$this->get_js_var_declaration();
		$js->add_cont($vardec);
		
		return $js;
		
		
		
		
			
	}
	
	function before_exec(){
		
		
		$this->add_main_js_scripts_and_aditional_css();	
	}
	function add_main_js_scripts_and_aditional_css(){
		$jsman=$this->__get_priv_jsmanager();
		if(($this->jquery_enabled)or($this->dx_enabled)){
			if($this->dx_enabled){
				$util=new mwmod_mw_devextreme_util();
				$util->preapare_ui_webappjs($this);	
			}
		}
		
		
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("mw_events.js");
		$jsman->add_item_by_cod_def_path("ui/mainui/mwmainui.js");
		$item=$this->create_js_man_ui_header_declaration_item();
		$jsman->add_item_by_item($item);
		$cod=$this->get_js_ui_man_name()."_init";
		$js=$this->get_js_bottom();
		$item= new mwmod_mw_html_manager_item_jscus($cod,$js);
		$item->bottom=true;
		$jsman->add_item_by_item($item);

		
		
		
	}
	
	
	function get_js_ui_man_name(){
		return $this->js_ui_man_name;	
	}
	function new_ui_js(){
		$js= new mwmod_mw_jsobj_newobject($this->js_ui_class_name);
		$this->set_info_main_ui_js($js);
		return $js;	
	}
	final function  __get_priv_ui_js_init_params(){
		if(!isset($this->ui_js_init_params)){
			$this->ui_js_init_params= new mwmod_mw_jsobj_obj();	
			$this->init_ui_js_init_params($this->ui_js_init_params);
		}
		return $this->ui_js_init_params;
	}
	function init_ui_js_init_params($jsobj){
		//extender
	}
	function create_js_init_on_bottom(){
		$varname=$this->get_js_ui_man_name();
		$args=$this->__get_priv_ui_js_init_params();
		$js= new mwmod_mw_jsobj_result($varname.".init",$args);
		return $js;
	}
	
	function get_js_init_on_bottom(){
		if(!isset($this->js_init_on_bottom)){
			$this->js_init_on_bottom=$this->create_js_init_on_bottom();
		}
		return $this->js_init_on_bottom;
	}
	
	function create_js_bottom(){
		$jscontainer= new mwmod_mw_jsobj_codecontainer();
		$init_line=$this->get_js_init_on_bottom();
		$jscontainer->add_cont($init_line);
		return $jscontainer;
		
		
		
			
	}
	
	function get_js_bottom(){
		if(!isset($this->js_bottom)){
			$this->js_bottom=$this->create_js_bottom();
		}
		return $this->js_bottom;
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
	function get_relogin_url(){
		return $this->get_url();	
	}
	function set_info_main_ui_js($js){
		$js->set_prop("url",$this->get_url_base());
		$js->set_prop("ui_var",$this->ui_var_name);
		$js->set_prop("sub_ui_var",$this->sub_ui_var_name);
		if($this->__accepts_exec_cmd_by_url()){
			$js->set_prop("xmlurlsubui",$this->get_exec_cmd_sxml_url_from_ui_full_cod(false,false));
			$js->set_prop("sessionCheckUrl",$this->get_exec_cmd_url("sessioncheck",false,"sessioncheck.xml"));
			
		}
		$js->set_prop("sessionCheckTimeout",$this->sessionCheckTimeout);
		if($uman=$this->get_admin_user_manager()){
			if($uman->current_user_cookie_enabled()){
				$js->set_prop("current_user_cookie_name",$uman->current_user_cookie_name);	
				if($this->sessionCheckTimeout){
					//$js->set_prop("session_expired_msg",$this->lng_common_get_msg_txt("session_has_expired","La sesión ha expirado")	
				}
			}
		}
		$js->set_prop("user.ok",false);
		if($user=$this->get_admin_current_user()){
			if($id=$user->get_id()){
				$js->set_prop("user.ok",true);
				$js->set_prop("user.id",$id);
				$js->set_prop("user.idname",$user->get_idname());
				$js->set_prop("user.name",$user->get_real_name());
			}
		}
		$this->set_info_main_ui_js_more($js);
		//$js->set_prop("xmlurl",$this->get_exec_cmd_xml_url(false,false));
		//$r["xmlurl"]=$this->get_exec_cmd_sxml_url_from_ui_full_cod(false,false);	
			
	}
	function set_info_main_ui_js_more($js){
			
	}

	//
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
	
	function get_name(){
		if($this->name){
			return $this->name;	
		}
		return $this->get_page_title();
	}
	function get_ui_js_info_for_child($sub=false){
		$r=array();
		$r["url"]=$this->get_url_base();
		$r["ui_var"]=$this->ui_var_name;
		$r["sub_ui_var"]=$this->sub_ui_var_name;
		$r["xmlurl"]=$this->get_exec_cmd_sxml_url_from_ui_full_cod(false,false);	
		$r["dlurl"]=$this->get_exec_cmd_dl_url_from_ui_full_cod(false,false);	

		$this->get_ui_js_info_for_child_sub($r,$sub);
		
		
		return $r;
			
	}
	function get_ui_js_info_for_child_sub(&$result,$sub=false){
		//	
	}

	function exec_full_output(){
		$out1 = ob_get_contents();
		if(!$template=$this->get_template()){
			echo "<div>No template</div>";
			echo $out1;
			return;	
		}
		ob_end_clean();
		$template->exec_full_output($this);
		echo $out1;

		/*
		echo "<!DOCTYPE HTML>\n";
		echo "<html>\n";
		echo "<head>\n";
		echo $this->get_page_html_head();
		echo "</head>\n";
		echo "<body id='page-top'>\n";
		$this->exec_page_body();
		echo $out1;
		if($this->jsmanager){
			echo $this->jsmanager->get_bottom_items_declaration();	
		}
		echo "\n</body>\n";
		echo "</html>\n";
		*/
	
	}
	
	
	function exec_page_body(){
		if($si=$this->get_current_subinterface()){
			if($si->is_allowed()){
				return 	$this->exec_page_body_subinterface_on_template($si);
			}
		}
		
	}
	function exec_sub_interface(){
		$this->before_exec();
		if($si=$this->set_current_subinterface_by_code($this->get_sub_insterface_request_code())){
			if($si->is_allowed()){
				$si->do_exec();	
			}
		}
		$this->after_exec();
	}
	function after_exec(){
			
	}
	function exec_page_body_subinterface_on_template($subinterface){
		if(!$template=$this->get_template()){
			return false;	
		}
		if(!$subinterface){
			return false;	
		}
		//echo get_class($template)."<br>";
		if($last=$subinterface->get_this_or_final_current_subinterface()){
			//echo get_class($last);
			if($last->is_single_mode()){
				$template->exec_page_full_body_sub_interface_single_mode($last);
			}else{
				
				$template->exec_page_full_body_sub_interface($subinterface);
			}
			
		}
	}
	function is_authentication_ui(){
		return false;	
	}
	function get_main_authentication_ui(){
		if($this->is_authentication_ui()){
			return false;	
		}
		if(!$man=$this->mainap->get_submanager("uiauthentication")){
			return false;	
		}
		if($man->is_authentication_ui()){
			return $man;	
		}
		return false;
	}
	function get_page_html_head(){
		$html='<meta charset="utf-8">'."\n";	
		$html.='<title>'.$this->get_page_title().'</title>'."\n";	
		$html.=$this->get_page_html_style();
		$html.=$this->get_page_html_head_js();
		return $html;
	}
	function get_ui_title_for_nav(){
		return $this->get_page_title();	
	}
	 
	function get_page_title(){
		if(!$lng=$this->mainap->get_current_lng_man()){
			return "ADMIN";
		}
		
		return $lng->get_cfg_data("pagetitle");
	}
	//tamplate
	final function __get_priv_template(){
		if(!isset($this->template)){
			$this->template=$this->create_template();
		}
		return $this->template;	
	}
	function create_template(){
		$t=new mwmod_mw_ui_main_uimaintemplate($this);
		return $t;
	}
	function get_template(){
		return $this->__get_priv_template(); 	
	}
	
	//css manager
	private $cssmanager;
	function create_cssmanager(){
		$m= new mwmod_mw_html_manager_css();
		return $m;
	}
	function add_default_css_sheets($cssmanager){
		if($t=$this->get_template()){
			$t->add_default_css_sheets($cssmanager);	
		}
	}
	final function init_ccmanager(){
		if(isset($this->cssmanager)){
			return;	
		}
		
		$this->cssmanager=$this->create_cssmanager();
		$this->add_default_css_sheets($this->cssmanager);
		
		
	}
	final function __get_priv_cssmanager(){
		$this->init_ccmanager();
		return $this->cssmanager;	
	}
	
	function get_page_html_style(){
		$this->init_ccmanager();
		return $this->cssmanager->declare_new_items(false);
	}
	
	

	//js manager
	function create_jsmanager(){
		$m= new mwmod_mw_html_manager_js();
		return $m;
	}
	function add_default_js_scripts($jsmanager){
		$jsmanager->add_jquery();
		$jsmanager->add_item_by_cod_def_path("main.js");
		//$jsmanager->add_item_by_cod_def_path("inputsman.js");
		//$jsmanager->add_item_by_cod_def_path("validator.js");
		//$jsmanager->add_item_by_cod_def_path("mw_tbl.js");
		$this->add_default_js_scripts_sub($jsmanager);

	}
	function add_default_js_scripts_sub($jsmanager){
			
	}
	final function init_jsmanager(){
		if(isset($this->jsmanager)){
			return;	
		}
		
		$jsmanager=$this->create_jsmanager();
		
		if($jsmanager){
			if(is_object($jsmanager)){
				if(is_a($jsmanager,"mwmod_mw_html_manager_js")){
					$this->jsmanager=$jsmanager;	
				}
			}
		}
		if(!$this->jsmanager){
			$this->jsmanager=new mwmod_mw_html_manager_js();
		}
		$this->add_default_js_scripts($this->jsmanager);
		if($template=$this->get_template()){
			$template->add_default_js_scripts_for_main($this,$this->jsmanager);
		}
		
		
	}
	final function __get_priv_jsmanager(){
		$this->init_jsmanager();
		return $this->jsmanager;	
	}

	function get_page_html_head_js(){
		$this->init_jsmanager();
		return $this->jsmanager->declare_new_items(false);
	
	}
	
	function get_abs_url($url=""){
		$r="";
		if($_SERVER['HTTPS']??null){
			$r="https://";	
		}else{
			$r="http://";	
		}
		$r.=$_SERVER['HTTP_HOST'];
		return $r.$url;
	}
	
	function get_url_base($file=false){
		if(!$file){
			$file="index.php";	
		}
		return $this->get_url_base_path().$file;	
	}
	function get_url($args=false,$file=false){
		$url=$this->get_url_base($file);
		if($args){
			if(is_array($args)){
				if($q=mw_array2urlquery($args)){
					$url.="?".$q;	
				}
			}
		}
		return $url;
	}
	
	
	function get_url_base_path(){
		return $this->url_base_path;
	}
	//menu
	function add_mnu_items($mnu){
		
		
		//$mnu->add_new_item("logout",$this->get_msg("Cerrar sesión"),"index.php?logout=true");	
		//$mnu->add_new_item("logout",$this->get_msg("Cerrar sesión"),$this->get_logout_url());	
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
	final function get_mnu_man(){
		if(!isset($this->mnu_man)){
			$this->mnu_man=$this->create_mnu_man();
			$this->mnu_man_on_create($this->mnu_man);
		}
		return $this->mnu_man;
	}
	function create_mnu_man(){
		$m=new mwmod_mw_mnu_man();
		$m->set_main_interface($this);
		return $m;
	}
	function mnu_man_on_create($mnu_man){
		$mnu=$mnu_man->get_item("mnu");
		$this->add_mnu_items($mnu);	
		$mnu=$mnu_man->get_item("lat");
		$this->add_lat_mnu_items($mnu);	
	}
	final function __get_priv_mnu_man(){
		return $this->get_mnu_man(); 	
	}
	
	function create_mnu(){
		$m=$this->get_mnu_man();
		return $m->get_item("mnu");
		//return new mwmod_mw_mnu_mainui($this);	
	}
	function get_mnu(){
		if(!isset($this->mnu)){
			if($this->mnu=$this->create_mnu()){
				//$this->add_mnu_items($this->mnu);	
			}
		}
		return $this->mnu;
	}
	
	function add_lat_mnu_items($mnu){
		
		//$mnu->add_new_item("logout",$this->get_msg("Cerrar sesión"),"index.php?logout=true");	
		$mnu->add_new_item("logout",$this->get_msg("Cerrar sesión"),$this->get_logout_url());	
	}
	
	function create_lat_mnu(){
		$man=$this->get_mnu_man();
		if($m=$man->get_item("lat")){
			$m->vertical=true;
			return $m;
				
		}
	}
	function get_lat_mnu(){
		if(!isset($this->lat_mnu)){
			if($this->lat_mnu=$this->create_lat_mnu()){
				//$this->add_lat_mnu_items($this->lat_mnu);	
			}
		}
		return $this->lat_mnu;
	}

	
	//subinterfaces
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

	function create_subinterface($cod){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;		
		}
		if(!$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		if(ctype_alpha($cod)){
			$method="create_subinterface_".$cod;
			if(method_exists($this,$method)){
				if($su=$this->$method()){
					$su->set_code($cod);
					return 	$su;	
				}else{
					return false;	
				}
			}
		}
		if($su=$this->create_subinterfacealt($cod)){
			$su->set_code($cod);
			return 	$su;	
		}


	}
	function create_subinterfacealt($cod){
		return false;	
	}
	function on_subinterface_not_allowed(){
		$si=false;
		if($user=$this->get_admin_current_user()){
			$si=$this->get_subinterface_not_allowed();	
		}else{
			$si=$this->get_subinterface_not_allowed_no_user();	
		}
		
		$this->set_current_subinterface($si);
		
		
		return $si;
			
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
	
	function get_subinterface_not_allowed_no_user(){
		$si= new mwmod_mw_ui_sub_uilogin("login",$this);
		return $si;
	}
	function get_subinterface_not_allowed(){
		$si= new mwmod_mw_ui_sub_uinotallowed("notallowed",$this);
		return $si;
	}
	function add_new_subinterface_by_class_name($class_name,$cod){
		if(!class_exists($class_name)){
			return false;	
		}
		$su=new $class_name($cod,$this);
		return $this->add_new_subinterface($su);
	}
	function add_new_subinterface($subinterface){
		return $this->add_subinterface($subinterface->code,$subinterface);
		
	}
	final function add_subinterface($cod,$subinterface){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;		
		}
		if(!$this->check_str_key_alnum_underscore($cod)){
			return false;		
		}
		if(!$subinterface){
			return false;	
		}
		if(!is_object($subinterface)){
			return false;	
		}
		if(!is_a($subinterface,"mwmod_mw_ui_sub_uiabs")){
			return false;	
		}
		if(isset($this->___subinterfaces[$cod])and($this->___subinterfaces[$cod])){
			if(get_class($subinterface)===get_class($this->___subinterfaces[$cod])){
				return 	$this->___subinterfaces[$cod];
			}
			return false;
		}else{
			$this->___subinterfaces[$cod]=$subinterface;
			$this->___subinterfaces[$cod]->set_code($cod);
			return 	$this->___subinterfaces[$cod];	
	
		}
	
			
	}
	
	final function set_current_subinterface($si=false){
		$this->current_subinterface=$si; 
		return $this->current_subinterface;
	}
	function get_subinterface_request_var(){
		return $this->ui_var_name;	
	}
	function get_url_sub_interface_by_dot_cod($dotcod,$file=false,$sep=".",$otherargs=false){
		$args=array();
		$list=explode($sep,$dotcod);
		$d=0;
		foreach($list as $c){
			$var=$this->get_subinterface_request_var_by_deep($d);
			$args[$var]=$c;
			$d++;
		}
		if(is_array($otherargs)){
			foreach($otherargs as $var=>$c){
				$args[$var]=$c;
			}
		}
		return $this->get_url($args,$file);
	}
	function get_subinterface_request_var_by_deep($deep=0){
		if(!$deep){
			return $this->get_subinterface_request_var();	
		}
		$d=$deep-1;
		if(!$d){
			return $this->sub_ui_var_name;	
		}
		return $this->sub_ui_var_name.$d;	
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
		return $this->set_current_subinterface($si);
	}
	function get_final_current_subinterface(){
		if($su=$this->get_current_subinterface()){
			return 	$su->get_this_or_final_current_subinterface();
		}
	}
	final function get_current_subinterface(){
		return $this->current_subinterface; 	
	}
	final function get_subinterface($cod){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;		
		}
		if(!$this->check_str_key_alnum_underscore($cod)){
			return false;		
		}
		$this->init_all_subinterfaces_once();
		
		if(isset($this->___subinterfaces[$cod])){
			return 	$this->___subinterfaces[$cod];
		}
		if($man=$this->create_subinterface($cod)){
			return 	$this->add_subinterface($cod,$man);
		}
	}
	function get_sub_insterface_request_code(){
		//fix here!!!!
		$key=$this->get_subinterface_request_var();
		if($key and isset($_REQUEST[$key])){
			if($code=$_REQUEST[$key]){
				return $code;
			}
		}

		
		return $this->subinterface_def_code;	
	}
	
	
	//user
	
	function exec_user_validation(){
		if($man=$this->get_admin_user_manager()){
			return $man->exec_user_validation();	
		}
		
	}
	function exec_login_and_user_validation(){
	
		if($man=$this->get_admin_user_manager()){
			return $man->exec_login_and_user_validation();	
		}
		
	}
	function get_admin_user_manager(){
		return $this->mainap->get_admin_user_manager();	
	}
	function get_admin_current_user(){
		if($man=$this->get_admin_user_manager()){
			return $man->get_current_user();	
		}
		
	}

	function admin_user_ok(){
		if($user=$this->get_admin_current_user()){
			return $user->allow("admininterfase");	
		}
	}
	function allow($action,$params=false){
		if($man=$this->get_admin_user_manager()){
			return $man->allow($action,$params);	
		}
	
	}

	function __call($a,$b){
		return false;	
	}
	
}
?>