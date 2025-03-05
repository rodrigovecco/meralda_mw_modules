<?php

class mwmod_mw_uitemplates_sbadmin_sub_uilogin extends mwmod_mw_uitemplates_sbadmin_sub_abs{
	var $login_direct_mode=false;
	var $allow_direct_mode=false;
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		if($msg_man=$this->mainap->get_msgs_man_common()){
			$this->set_def_title($msg_man->get_msg_txt("login","Iniciar sesión"));
		}
		//$this->debug_mode=true;
		
		$this->js_ui_class_name="mw_ui_login";
		

		
		
	}

	function is_allowed_for_get_cmd_no_user(){
		return true;	
	}
	function is_allowed_for_get_cmd($sub_ui_cods=false,$params=array(),$filename=false){
		return true;
	}
	function execfrommain_getcmd_dl_logindirect($params=array(),$filename=false){
		if(!$this->allow_direct_mode){
			echo "Not allowed";
			return;
		}
		$this->maininterface->exec_login_and_user_validation();
		if(!$user=$this->get_current_user()){
			$url=$this->maininterface->get_url(array("dm"=>"true"));
			ob_end_clean();
			header("Location: $url");
		}else{
			$url=$this->get_on_ok_url();
			ob_end_clean();
			header("Location: $url");
		}
		
			
	}
	function execfrommain_getcmd_dl_logintoken($params=array(),$filename=false){
		
		$xml=$this->new_getcmd_sxml_answer(false);
		$userman=$this->maininterface->get_admin_user_manager();
		if(!$userman->login_session_token_enabled()){
			$xml->root_do_all_output();
			return;
		}
		sleep(1);
		$xml->set_prop("ok",true);
		
		$xml->set_prop("chiwawa",$userman->get_login_session_token());

		
		$xml->root_do_all_output();
	}
	function execfrommain_getcmd_dl_login($params=array(),$filename=false){
		$html=new mwmod_mw_html_cont_fulldoc();
		$html->body->add_cont("");
		$paramsjs=new mwmod_mw_jsobj_obj();
		$this->maininterface->exec_login_and_user_validation();
		$userman=$this->maininterface->get_admin_user_manager();
		
		if(!$user=$this->get_current_user()){
			if($msg=$this->get_login_fail_msg()){
				$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"danger");
				$paramsjs->set_prop("msg",$alert->get_ui_devexpress_toast_options());
	
			}
			$paramsjs->set_prop("ok",false);
				
		}else{
			$paramsjs->set_prop("ok",true);	
		}
		$paramsjs->set_prop("result",$userman->login_js_response);
		//$paramsjs->set_prop("debug",$userman->get_login_security_sess_data());
		
		
		
		$js=new mwmod_mw_jsobj_codecontainer();
		$var=$this->get_js_ui_man_name();
		$js->add_cont("window.parent.".$var.".on_post_response(".$paramsjs->get_as_js_val().");\n");
		//$html->body->add_cont(nl2br($js->get_as_js_val()));
		
		$html->body->add_cont($js->get_js_script_html());
		
		
		$html->do_output();
			
	}

	function prepare_before_exec_no_sub_interface(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);

		$p=new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_login.js");
		
		
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);
		
		
	}
	function get_on_ok_url(){
		//modificar para ir a la io req
		return $this->maininterface->get_url();	
	}
	function do_exec_page_in(){
		$userman=$this->maininterface->get_admin_user_manager();
		$this->login_direct_mode=false;
		if($this->allow_direct_mode){
			if($_REQUEST["dm"]==="true"){
				$this->login_direct_mode=true;	
			}
		}
		
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		
		$maincontainer=$this->get_ui_dom_elem_container_empty();
		
		
		
		
		$container=new mwmod_mw_bootstrap_html_grid_container();
		$row=new mwmod_mw_bootstrap_html_grid_row();
		$container->add_cont($row);
		$col= new mwmod_mw_bootstrap_html_grid_col(4);
		$row->add_cont($col);
		$row->addClass("row justify-content-center");
		
		
		
		
		$panel=new mwmod_mw_bootstrap_html_def("card card-default shadow-lg rounded-lg mt-5");
		$col->add_cont($panel);
		
		
		$panel_head=new mwmod_mw_bootstrap_html_def("card-header");
		$panel->add_cont($panel_head);
		
		$fixcontent=new mwmod_mw_data_fixcontent_item("login/panelhead.html");
		if($fixcontentHtml=$fixcontent->getContentHTML()){
			$panel_head->add_cont($fixcontentHtml);
		}else{
			$panel_title=new mwmod_mw_bootstrap_html_def("card-title","h4");
			$panel_title->add_cont($msg_man->get_msg_txt("please_login","Por favor, iniciar sesión"));
			$panel_head->add_cont($panel_title);
		}
		


		

		
		$panel_body=new mwmod_mw_bootstrap_html_def("card-body");
		
		$panel->add_cont($panel_body);
		
		
		
		
		$frmcontainer=$this->set_ui_dom_elem_id("loginfrm");
		
		$frmcontainer->add_cont($this->get_login_frm_html());
		if($this->allow_direct_mode){
			if($this->login_direct_mode){
				$frmcontainer->add_cont("<p>".$msg_man->get_msg_txt("direct_mode","Modo directo").".</p>");	
			}else{
				$urldirect="index.php?dm=true";
				$frmcontainer->add_cont("<p style='display:none'><a href='$urldirect'>".$msg_man->get_msg_txt("direct_mode","Modo directo").".</a></p>");		
			}
		}
		
		
		
		$frmcontainer->set_style("display","none");
		$panel_body->add_cont($frmcontainer);
		$waitcontainer=$this->set_ui_dom_elem_id("wait");
		$waitcontainer->set_style("display","none");
		$panel_body->add_cont($waitcontainer);
		
		/*
		if($msg=$this->get_login_fail_msg()){
			$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"danger");
			$panel_body->add_cont($alert);

		}
		*/
		if($uiremember=$this->maininterface->get_subinterface("rememberlogindata")){
			if($uiremember->is_rememberlogindata()){
				if($uiremember->is_enabled()){
					if($html=$uiremember->get_html_link_on_login()){
						//$alert=new mwmod_mw_bootstrap_html_specialelem_alert($html,"info");
						$alert=new mwmod_mw_bootstrap_html_elem("div",false,$html);
						$panel_body->add_cont($alert);
							
					}
				}
			}
		}
		
		$maincontainer->add_cont($container);
		$iframaandfrm=$this->create_ui_dom_elem_iframe_and_frm_container();
		$maincontainer->add_cont($iframaandfrm);
		echo $maincontainer->get_as_html();
		
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		
		if(!$url=$_SERVER['REQUEST_URI']){
			$url=$this->get_on_ok_url();
		}
		//echo $url."<br>";
		
		//echo $this->get_on_ok_url();
		$this->ui_js_init_params->set_prop("onokurl",$url);
		$this->ui_js_init_params->set_prop("please_wait",$this->lng_common_get_msg_txt("please_wait","Por favor, espere"));
		$this->ui_js_init_params->set_prop("seconds",$this->lng_common_get_msg_txt("seconds_lc","segundos"));
		if($userman->login_session_token_enabled()){
			$this->ui_js_init_params->set_prop("requestTokenMode",true);
		}
		$var=$this->get_js_ui_man_name();
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");

		echo $js->get_js_script_html();


		return;
	}
	function get_login_frm_html(){
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		$userman=$this->maininterface->get_admin_user_manager();
		$frm=$this->new_frm();
		$fnc=$frm->add_bootstrap_after_create_final_fnc();
		$var=$this->get_js_ui_man_name();
		$fnc->add_cont("{$var}.set_frm_man(frmman);");
		if($this->login_direct_mode){
			$frm->action=$this->get_exec_cmd_dl_url("logindirect",array(),"login.html");
			
		}else{
			$frm->action=$this->get_exec_cmd_dl_url("login",array(),"login.html");
			$frm->target=$this->get_ui_elem_id_and_set_js_init_param("iframe");
		}
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$user_name_input=false;
		$pass_input=false;
		

		$user_name_input=$userman->login_input_user_name();
		$cr->add_item($user_name_input);
		$pass_input=$userman->login_input_password();
		$cr->add_item($pass_input);
		$user_name_input->setBTFloating();
		$pass_input->setBTFloating();
		if($userman->login_session_token_enabled()){
			$login_token_input=new mwmod_mw_datafield_hidden("login_token");
			//$login_token_input=new mwmod_mw_datafield_input("login_token");
			$cr->add_item($login_token_input);	
		}

		/*
		$userman->add_login_inputs2cr($cr,$user_name_input,$pass_input);
		$user_name_input->setBTFloating();
		$pass_input->setBTFloating();
		*/
		
		
		
		
		$submit=$cr->add_submit($msg_man->get_msg_txt("login","Iniciar sesión")."");
		$params=$submit->get_bootstrap_params();
		$params->set_prop("btn.class","btn btn-primary");
		$frm->set_datafieldcreator($cr);
		return $frm->get_html();
	
	}
	function is_debug_mode(){
		if($this->debug_mode){
			return true;	
		}
		return false;
	}
	
	function is_allowed(){
		return true;
	}

	/////////


	function is_single_mode(){
		return true;	
	}
	
	
	function get_login_fail_msg(){
		if(!$man=$this->maininterface->get_admin_user_manager()){
			return false;	
		}
		return $man->login_fail_msg;
	}
	function exec_log_frm(){
		echo $this->get_login_frm_html();
		
	
	}
	
	
	
}
?>