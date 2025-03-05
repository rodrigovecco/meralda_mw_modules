<?php
class mwmod_mw_subui_rememberlogindata extends mwmod_mw_bootstrap_ui_sub_abs{
	var $panel_body;
	var $email="";
	var $use_captcha=true;
	
	var $alert_fail;
	
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_lngmsgsmancod("rememberlogindata");
		$this->set_def_title($this->lng_get_msg_txt("rememberlogindata","Recuperar datos de acceso"));
		//$this->use_captcha=false;

		
		
	}
	function get_related_user_man(){
		if($uman=$this->mainap->get_user_manager()){
			return $uman;
		}

	}
	function prepare_before_exec_no_sub_interface(){
		$p=new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
	}
	function do_change_user_pass($user,$inputman){
		if(!$this->is_enabled()){
			return false;	
		}
		if(!$this->can_change_password()){
			return false;	
		}
		if(!$token =$inputman->get_value_by_dot_cod("u.rptoken")){
			return false;	
		}
		if(!$user->can_reset_pass()){
			return false;	
		}
		if(!$user->check_reset_pass_token($token)){
			return false;	
		}
		if(!$uman=$this->get_related_user_man()){
			return false;
		}
		if(!$pass_policy=$uman->get_pass_policy()){
			return false;
		}
		$pass_input=$inputman->get_value_by_dot_cod_as_list("u.pass");
		//mw_array2list_echo($inputman->get_value_as_list());
		//mw_array2list_echo($pass_input);
		$msg=false;
		if(!$n_pass=$pass_policy->check_passpair_input_by_array($pass_input,$msg)){
			$this->alert_fail->add_cont_as_html($msg);
			return false;	
		}
		if($user->reset_password_set_new_pass($n_pass)){
			$msg=$this->lng_get_msg_txt("password_updated","Contraseña actualizada");
			$msg.="<br>";
			$url=$this->maininterface->get_relogin_url();
			$msg.="<a href='$url'>";
			$msg.=$this->lng_get_msg_txt("login","Iniciar sesión");
			$msg.="</a>";
			$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"success");
			$this->panel_body->add_cont($alert);
			return true;
	
		}
		/*
		if(!){
			
		}
		*/
		//
		
		//$np=$pass_policy->check_passpair_input_by_array($pass_input);
		return false;
		
		
		
	}
	
	function send_pasword_reset_email(){
		if(!$this->is_enabled()){
			$alert=$this->lng_get_msg_txt("this_funtion_is_disabled","Esta función está deshabilitada");
			$this->alert_fail->add_cont_as_html($alert);
			return false;
	
		}
		
		$uman=$this->get_related_user_man();
		$usermailer=$uman->get_user_mailer();
		
		$inputman=new mwmod_mw_helper_inputvalidator_request("resetpassdata",0);
		if(!$uname =$inputman->get_value_by_dot_cod("u.uname")){
			return false;	
		}
		if(!$token =$inputman->get_value_by_dot_cod("u.rptoken")){
			return false;	
		}
		if($this->use_captcha){
			if(!$mancaptcha=$this->mainap->get_submanager("captcha")){
				return false;	
			}
			$msg=false;
			//echo $mancaptcha->get_item_secret_by_cod("captcha")."<br>";
			//echo $inputman->get_value_by_dot_cod("captcha")."<br>";
			if(!$mancaptcha->validate($inputman->get_value_by_dot_cod("captcha"),"captcha",$msg)){
				$this->alert_fail->add_cont_as_html($msg);
				return false;	
				
			}
		}
		if(!$user=$uman->get_user_by_idname($uname)){
			$this->alert_fail->add_cont_as_html($this->lng_get_msg_txt("no_user","El usuario no existe"));
			return false;	
			
			//can_reset_pass
		}
		if(!$user->can_reset_pass()){
			$this->alert_fail->add_cont_as_html($this->lng_get_msg_txt("user_reset_pass_disabled","La función de restablecer contraseña está desactivada"));
			return false;	
		}
		if(!$user->check_reset_pass_token($token)){
			$this->alert_fail->add_cont_as_html($this->lng_get_msg_txt("invalid_reset_pass_code","Código de reestablecimiento de contraseña inválido"));
			return false;	
		}
		$usermailer->ui_reset_password=$this;
		$usermailer->last_error_html=false;
		if($this->can_change_password()){
			return $this->do_change_user_pass($user,$inputman);
		}else{
		
			if($usermailer->msg_reset_password_send($user)){
				$msg=$user->get_real_name_or_idname().",<br>".$this->lng_get_msg_txt("account_login_data_was_sent_to_your_email_X",
					"Se envió tus datos de acceso a tu correo: %user_email%",
					array("user_email"=>$user->get_email())).".";
				$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"success");
				$this->panel_body->add_cont($alert);
				return true;
					
			}else{
				$msg=$this->lng_get_msg_txt("unabled_to_send_msg_to_user_X",
					"No se pudo enviar el mensaje al usuario %user_full_name%",
					array("user_full_name"=>$user->get_real_name_or_idname())).".";
				if($usermailer->last_error_html){
					$msg.="<br>".$usermailer->last_error_html;
				}
				$this->alert_fail->add_cont_as_html($msg);
				return false;
			}
		}

	}
	
	
	function do_actions_reset_pass(){
		
		
		if($this->send_pasword_reset_email()){
			return true;	
		}
		
		$html=$this->get_resetpass_frm_html();
		$this->panel_body->add_cont($html);
		if($this->alert_fail){
			$this->panel_body->add_cont($this->alert_fail);
				
		}
		
		
	}
	
	function get_resetpass_frm_html(){
		if(!$uman=$this->get_related_user_man()){
			return false;
		}
		if(!$pass_policy=$uman->get_pass_policy()){
			return false;
		}
		
		$frm=$this->new_frm();
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$gr=$cr->add_item(new mwmod_mw_datafield_group("resetpassdata"));
		$gru=$gr->add_item(new mwmod_mw_datafield_group("u"));
		
		$inputman=new mwmod_mw_helper_inputvalidator_request("resetpassdata",0);
		$i=$gru->add_item(new mwmod_mw_datafield_input("uname",$this->lng_get_msg_txt("user","Usuario")));
		$i->set_required();
		if($_REQUEST["uname"]??null){
			$i->set_value($_REQUEST["uname"]);
		}
		if($this->can_change_password()){
			$grdatapass=$gru->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("pass"));
		
		
			$input_pass=$grdatapass->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass",$this->lng_get_msg_txt("new_password","Nueva contraseña")));
			$input_pass->set_required();
			$input_pass_confirm=$grdatapass->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass1",$this->lng_get_msg_txt("confirm_password","Confirmar contraseña")));
			//$pass_policy->confirm_pass_is_required=true;
			
			$input_pass_confirm->set_required();
			$pass_policy->prepare_new_pass_inputs($input_pass,$input_pass_confirm);

			//$submit=$cr->add_submit($this->lng_get_msg_txt("change_password","Canbiar contraseña"));	
		}else{
			//$submit=$cr->add_submit($this->lng_get_msg_txt("send","Enviar"));
		}
		
		
		$i=$gru->add_item(new mwmod_mw_datafield_input("rptoken",$this->lng_get_msg_txt("reset_pass_code","Código de reestablecimiento de contraseña")));
		$i->set_required();
		if($_REQUEST["rptoken"]??null){
			$i->set_value($_REQUEST["rptoken"]);
		}
		if($d=$inputman->get_value_by_dot_cod_as_list("u")){
			$gru->set_value($d);	
		}
		
		if($this->use_captcha){
			$i=$gr->add_item(new mwmod_mw_helper_captcha_input("captcha"));
			$i->captcha_cod="captcha";
		}
		if($this->can_change_password()){
			$submit=$cr->add_submit($this->lng_get_msg_txt("changepassword","Cambiar contraseña"));	
		}else{
			$submit=$cr->add_submit($this->lng_get_msg_txt("send","Enviar"));
		}
		
		//$submit=$cr->add_submit($this->lng_get_msg_txt("send","Enviar"));
		$params=$submit->get_bootstrap_params();
		$params->set_prop("btn.class","btn btn-lg btn-success btn-block");
		$frm->set_datafieldcreator($cr);
		return $frm->get_html();
	
	}
	function prepare_ph_src_new_pass($ds,$user){
		$dsitem=$ds->get_or_create_item("ui");
		$main=$this->maininterface;
		$url=$main->get_abs_url($main->get_url());
		$dsitem->add_item_by_cod($url,"login_url");
		
		
		
		
	}
	
	function prepare_ph_src($ds,$user){
		$dsitem=$ds->get_or_create_item("ui");
		$main=$this->maininterface;
		$url=$main->get_abs_url($main->get_url());
		$dsitem->add_item_by_cod($url,"login_url");
		$params=array("action"=>"resetpass");
		$url=$main->get_abs_url($this->get_url($params));
		$dsitem->add_item_by_cod($url,"reset_pass_url");
		
		$params["uname"]=$user->get_idname();
		$params["rptoken"]=$user->reset_pass_code;
		$url=$main->get_abs_url($this->get_url($params));
		$dsitem->add_item_by_cod($url,"reset_pass_url_full");
		
		
		
		
	}
	function send_access_data_email(){
		if(!$this->is_enabled()){
			$alert=$this->lng_get_msg_txt("this_funtion_is_disabled","Esta función está deshabilitada");
			$this->alert_fail->add_cont_as_html($alert);
			return false;
	
		}
		
		$uman=$this->get_related_user_man();
		$usermailer=$uman->get_user_mailer();
		
		$inputman=new mwmod_mw_helper_inputvalidator_request("reqdata",0);
		if(!$email=$inputman->get_value_by_dot_cod("user_email")){
			return false;	
		}
		$this->email=$email;
		if(!$users=$uman->get_users_with_mail($email, true)){
			$this->alert_fail->add_cont_as_html($this->lng_get_msg_txt("no_user_with_that_email","El correo electrónico ingresado no está asociado con ningún usuario del sistema"));
			return false;	
		}
		if($this->use_captcha){
			if(!$mancaptcha=$this->mainap->get_submanager("captcha")){
				return false;	
			}
			$msg=false;
			if(!$mancaptcha->validate($inputman->get_value_by_dot_cod("captcha"),"captcha",$msg)){
				
				$this->alert_fail->add_cont_as_html($msg);
				return false;	
				
			}
		}
		
		
		$usermailer->ui_reset_password=$this;
		$ok=0;
		foreach($users as $idu=>$user){
			$msg=false;
			$usermailer->last_error_html=false;
			if($usermailer->msg_reset_password_request_send($user)){
				$msg=$user->get_real_name_or_idname().",<br>".$this->lng_get_msg_txt("account_login_data_was_sent_to_your_email_X",
				"Se enviaron los datos de acceso a tu correo: %user_email%",
				array("user_email"=>$user->get_email())).".";
				$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"success");
				$this->panel_body->add_cont($alert);
				$ok++;
				
			}else{
				
				$msg=$this->lng_get_msg_txt("unabled_to_send_msg_to_user_X",
				"No se pudo enviar el mensaje al usuario %user_full_name%",
				array("user_full_name"=>$user->get_real_name_or_idname())).".";

				
				if($usermailer->last_error_html){
					$msg.="<br>".$usermailer->last_error_html;
				}
				$this->alert_fail->add_cont_as_html($msg);
			}
		}
		return $ok;
	}
	function do_actions(){
		if($this->send_access_data_email()){
			return true;	
		}
		$html=$this->get_request_frm_html();
		$this->panel_body->add_cont($html);
		if($this->alert_fail){
			$this->panel_body->add_cont($this->alert_fail);
				
		}
		
	}
	function is_single_mode(){
		return true;	
	}
	function is_enabled(){
		if($uman=$this->get_related_user_man()){
			if($bruteForceMan=$uman->bruteForceMan){
				if($bruteForceMan->isEnabled()){
					if($bfBlackList=$bruteForceMan->getCurrentIPBlacklistItem()){
						return false;
					}
					if(!$whilelistItem=$bruteForceMan->getCurrentIPWhitelistItem()){
						if($curretIPActivity=$bruteForceMan->getCurrentIPActivityItem()){
							if($lockTimeUntil=$curretIPActivity->isLocked()){
								return false;
							}
						}
						
					}

				}
			}


			if($mailer=$uman->get_user_mailer()){
				return $mailer->msg_reset_password_request_enabled();	
			}
		}
	}
	function do_exec_page_in(){

		$reset_pass_mode=false;
		//echo get_class($this);
		
		if(mw_array_get_sub_key($_REQUEST,"action")=="resetpass"){
		//if($_REQUEST["action"]=="resetpass"){
			$reset_pass_mode=true;
			$this->set_url_param("action","resetpass");
		}
		
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		$container=new mwmod_mw_bootstrap_html_grid_container();
		$row=new mwmod_mw_bootstrap_html_grid_row();
		$container->add_cont($row);
		$col= new mwmod_mw_bootstrap_html_grid_col(4);
		$row->add_cont($col);
		$col->set_offset(4);
		$panel=new mwmod_mw_bootstrap_html_def("login-panel card card-default");
		$col->add_cont($panel);
		
		
		$panel_head=new mwmod_mw_bootstrap_html_def("card-header");
		$panel->add_cont($panel_head);
		
		$panel_title=new mwmod_mw_bootstrap_html_def("card-title","h3");
		if($reset_pass_mode){
			$panel_title->add_cont($this->lng_get_msg_txt("reset_password","Restablecer contraseña"));
		}else{
			$panel_title->add_cont($this->lng_get_msg_txt("rememberlogindata","Recuperar datos de acceso"));
				
		}
		$panel_head->add_cont($panel_title);
		
		$panel_body=new mwmod_mw_bootstrap_html_def("card-body");
		
		$panel->add_cont($panel_body);
		
		$this->panel_body=$panel_body;
		$this->alert_fail=new mwmod_mw_bootstrap_html_specialelem_alert(false,"danger");
		$this->alert_fail->only_visible_when_has_cont=true;
		

		
		if($this->is_enabled()){
			if($reset_pass_mode){
				$this->do_actions_reset_pass();
			}else{
				$this->do_actions();
					
			}
		}else{
			$alert=new mwmod_mw_bootstrap_html_specialelem_alert($this->lng_get_msg_txt("this_funtion_is_disabled","Esta función está deshabilitada"),"danger");
			$this->panel_body->add_cont($alert);
				
		}
		
		echo $container->get_as_html();
		return;
	}
	function get_html_link_on_login(){
		if(!$this->is_allowed()){
			return false;	
		}
		$txt=$this->lng_get_msg_txt("link_to_ui_txt","No recuerdo mis datos de acceso");
		$html="<a href='".$this->get_url()."'>";
		$html.=$txt."</a>";
		return $html;
	}
	
	function get_request_frm_html(){
		//echo "qq";
		if(!$uman=$this->get_related_user_man()){
			return false;
		}
		if(!$pass_policy=$uman->get_pass_policy()){
			return false;
		}
		$frm=$this->new_frm();
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$gr=$cr->add_item(new mwmod_mw_datafield_group("reqdata"));
		$i=$gr->add_item(new mwmod_mw_datafield_input("user_email",$this->lng_get_msg_txt("email","Correo")));
		$i->set_required();
		$i->set_value($this->email);
		if($this->use_captcha){
			$i=$gr->add_item(new mwmod_mw_helper_captcha_input("captcha"));
			$i->captcha_cod="captcha";
		}
		/*
		if($this->can_change_password()){
			$grdatapass=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("pass"));
		
		
			$input_pass=$grdatapass->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass",$this->lng_get_msg_txt("new_password","Nueva contraseña")));
			$input_pass->set_required();
			$input_pass_confirm=$grdatapass->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass1",$this->lng_get_msg_txt("confirm_password","Confirmar contraseña")));
		
			$pass_policy->prepare_new_pass_inputs($input_pass,$input_pass_confirm);

			$submit=$cr->add_submit($this->lng_get_msg_txt("change_password","Canbiar contraseña"));	
		}else{
			$submit=$cr->add_submit($this->lng_get_msg_txt("send","Enviar"));
		}
		*/
		$submit=$cr->add_submit($this->lng_get_msg_txt("send","Enviar"));
		$params=$submit->get_bootstrap_params();
		$params->set_prop("btn.class","btn btn-lg btn-success btn-block");
		$frm->set_datafieldcreator($cr);
		return $frm->get_html();
	
	}
	function can_change_password(){
		//echo "q";
		if($uman=$this->get_related_user_man()){
			if($pol=$uman->get_pass_policy()){
				//echo "pp";
				return $pol->can_change_password_on_remember_ui();	
			}
			//if($mailer=$uman->get_user_mailer()){
		
		}
	}
	function is_rememberlogindata(){
		return true;
	}
	
	function is_allowed(){
		return true;
	}
	
}
?>