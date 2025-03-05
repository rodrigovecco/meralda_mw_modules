<?php
class mwmod_mw_facebook_ui_cfg_test extends mwmod_mw_facebook_ui_cfg_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("test","Pruebas"));
		$this->js_ui_class_name="mw_fb_test_ui";
		
	}
	function execfrommain_getcmd_dl_fblogin($params=array(),$filename=false){
		//mw_array2list_echo($_REQUEST);
	}
	function execfrommain_getcmd_sxml_fblogin($params=array(),$filename=false){
		$output=new mwmod_mw_helper_output_uidef();
		$output->setOutputXMLJSAlertMode($this->new_getcmd_sxml_answer(false));
		$output->alert->setMsgSuccess();
		if(!$this->is_allowed()){
			$output->alert->setMsgError("Ocurrió un error");
			$output->xml->root_do_all_output();
			return false;	
		}
		//$output->xml->set_prop("req",$_REQUEST);
		
		$input=new mwmod_mw_helper_inputvalidator_request("fba");
		if(!$input->is_req_input_ok()){
			$output->alert->setMsgError("Ocurrió un error");
			$output->xml->root_do_all_output();
			return false;	
		}
		
		
		if(!$man=$this->getFBMan()){
			$output->alert->setMsgError("Ocurrió un error");
			$output->xml->root_do_all_output();
			return false;	
		}
		if(!$helper=$man->fbApp->newFBhelperWithApptoken()){
			$output->alert->setMsgError("Ocurrió un error");
			$output->xml->root_do_all_output();
			return false;	
				
		}
		//get
		
		//if(!$info=$helper->gettokenInfo($input->get_value_by_dot_cod("accesstoken"))){
		if(!$info=$helper->get("me",array(),$input->get_value_by_dot_cod("accesstoken"))){
			$output->alert->setMsgError("Ocurrió un error");
			$output->xml->root_do_all_output();
			return false;	
		}
		//$output->xml->set_prop("infop",$info->getParams());
		$output->xml->set_prop("errors",$info->errors);
		
		
		$output->xml->set_prop("resp",$info->getResponseData());
		$output->xml->root_do_all_output();
		
		
			
		
	}
	
	function do_exec_page_in(){
		$container=$this->get_ui_dom_elem_container_empty();
		
		
		
		
		//$frmcontainer=$this->set_ui_dom_elem_id("frmcontainer");
		$container->add_cont("<div id='fbTest'>Test</div>");
		$container->add_cont("<div id='fbLogout'>Logout</div>");
		$container->add_cont("<div id='fbLogin'>Login</div>");
		$container->add_cont("<div id='fbloginbtn'></div>");
		$container->do_output();
		//echo "<div id='fb-root'></div>";
		$this->set_ui_js_params();
		
		if($man=$this->getFBMan()){
			
			
			$this->maininterface->ui_js_init_params->set_prop("managers.fb",$man->getJsFMManObj());
			
			//$man->debugMode=true;
			//$this->ui_js_init_params->set_prop("fb.url",$man->getjsSDKurl());
			//$this->ui_js_init_params->set_prop("fb.initparams",$man->getJsInitParams());
			/*
			$js=$man->getInitJS();
			$js->add_cont("function checkLoginState(){console.log('dddd')}");
			
			echo $js;
			*/
		}
		$jsui=$this->new_ui_js();
		//$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js=new mwmod_mw_jsobj_codecontainer();
		
		$var=$this->get_js_ui_man_name();
		$js->add_cont("var {$var}=".$jsui->get_as_js_val().";\n");
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		
		//$this->set_js_in_page($js);
		echo $js->get_js_script_html();
		
		
		
		//echo '<fb:login-button scope="public_profile,email" onlogin="checkLoginState();"></fb:login-button>';

	/*
	echo '<div class="fb-login-button" data-max-rows="1" data-size="large" data-button-type="continue_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false"></div>';
	*/
		//$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		//$this->set_js_in_page($js);
		//echo $js->get_js_script_html();
		
		

		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	function prepare_before_exec_no_sub_interface(){
		
		// header('X-Frame-Options: ALLOW');

		
		$p=new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("fb/mw_fb.js");
		$jsman->add_item_by_cod_def_path("fb/mw_fb_test_ui.js");

		
		//$item=$this->create_js_man_ui_header_declaration_item();
		//$util->add_js_item($item);

	}
	
}
?>