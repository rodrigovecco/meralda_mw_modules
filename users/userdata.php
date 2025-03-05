<?php
//
class mwmod_mw_users_userdata extends mw_apsubbaseobj{
	private $man;
	
	var $firstAndLastNameMode=false;
	var $pass_min_len=6;
	var $pass_def_len=10;
	//var $new_pass_secure=true;
	var $pass_secure_mode=1;//1: secure, 2: optional, 3: non secure
	
	var $create_new_user_input;
	public $admin_user_id_enabled=false;
	public $user_must_change_password_enabled=false;
	
	private $docTypes;
	
	
	
	function __construct($man){
		$this->init($man);
	}
	final function __get_priv_docTypes(){
		if(!isset($this->docTypes)){
			$this->docTypes=$this->load_docTypes();
		}
		return $this->docTypes; 	
	}
	function load_docTypes(){
		$list= new mwmod_mw_util_itemsbycod();
		$list->add_item(new mwmod_mw_util_itemsbycod_item("dni",$this->lng_get_msg_txt("DTdni","DNI")));
		$list->add_item(new mwmod_mw_util_itemsbycod_item("ce",$this->lng_get_msg_txt("DTce","C.E.")));
		$list->add_item(new mwmod_mw_util_itemsbycod_item("passport",$this->lng_get_msg_txt("DTpassport","Pasaporte")));
		
		return $list;
	}
	
	
	
	function setRealNameData(&$nd){
		if($this->firstAndLastNameMode){
			if(!$nd["complete_name"]){
				if($nd["firstname"]){
					if($nd["lastname"]){
						$nd["complete_name"]=$nd["firstname"]." ".$nd["lastname"];	
					}
				}
				
			}
		}
	}
	
	function allowNewUserAutoLogin(){
		return false;	
	}
	function createUserSelRegister($data,$jsResult=false,$checkexisting=true){
		//ver mwap_pastipan_clients_user_userdata
	}
	
	function add_inputs_groups($gr,$user=false){
		if(!$grman=$this->man->get_groups_man()){
			return false;	
		}
		if(!$items=$grman->get_all_active_items()){
			return false;	
		}
		if(!sizeof($items)){
			return false;	
		}
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle("groups",$this->lng_get_msg_txt("groups","Grupos")));
		foreach($items as $id=>$item){
			$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox( $id,$item->get_name()));
			if($user){
				if($item->contains_user($user)){
					$input->set_value(1);
				}
			}
		}
	}
	function save_user_groups($user,$input){
		if(!is_array($input)){
			return false;	
		}
		if(!$grman=$this->man->get_groups_man()){
			return false;	
		}
		foreach($input as $id=>$val){
			if($item=$grman->get_item($id)){
				$item->save_user_belongs($user,$val);	
			}
		}
		
	}
	
	function save_full_data($input,$user,$msgcontainer=false){
		if(!$msgcontainer){
			$msgcontainer=new mwmod_mw_html_elem();
			$msgcontainer->only_visible_when_has_cont=true;

		}	
		if(!$input){
			return false;	
		}
		if(!$input->is_req_input_ok()){
			return false;	
		}
		
		$ok=$this->savefromfrm_user_data($input,$user,$msg);
		$ok=$this->savefromfrm_user_rols($input,$user,$msg);

		$msg="";
		$ok=$this->savefromfrm_user_accessdata($input,$user,$msg);
		if($msg){
			$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"danger");
			if($ok){
				//$alert->set_display_mode("success");	
			}
			$msgcontainer->add_cont($alert);
		}
		
		
		if($input->get_value_by_dot_cod("pass.change")){
			$msg="";
			$ok=$this->savefromfrm_user_changepassfromadmin($input,$user,$msg);
			if($msg){
				$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"danger");
				if($ok){
					$alert->set_display_mode("success");	
				}
				$msgcontainer->add_cont($alert);
			}
				
		}
		if($mustchange=$input->get_item_by_dot_cod("pass.must_change_pass")){
			$mchnd=array(
				"must_change_pass"=>$mustchange->get_value()
			);
			$user->tblitem->do_update($mchnd);


		}
		$this->save_full_data_extra($input,$user,$msgcontainer);
		$this->save_user_groups($user,$input->get_value_by_dot_cod_as_list("groups"));
		/*
		if(!$nd=$input->get_value_by_dot_cod_as_list("accessdata")){
			return false;	
		}
		*/
	
	}
	function save_full_data_extra($input,$user,$msgcontainer=false){
			
	}
	function set_user_datafull_cr($user,$cr=false){
		if(!$cr){
			$cr=new mwmod_mw_datafield_creator();	
		}
		$pass_policy=$this->man->get_pass_policy();
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("usernd"));
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("accessdata"));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("name",$this->lng_get_msg_txt("user_name","Nombre de usuario")));
		$input->add_js_email_validation(true);
		$input->set_required();
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("active",$this->lng_get_msg_txt("active","Activo")));
		//$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("email",$this->lng_get_msg_txt("email","Correo")));
		
		$grdata->set_value($user->get_public_tbl_data());

		
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("data"));
		$this->add_inputs_user_data($grdata);
		if($this->admin_user_id_enabled){
			$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_users_util_seluserinput("admin_user_id",$this->lng_get_msg_txt("manager","Administrador")));

		}
		$grdata->set_value($user->get_public_tbl_data());
		
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle("pass",$this->lng_get_msg_txt("change_password","Modificar contraseña")));
		$input_p=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass",$this->lng_get_msg_txt("password","Contraseña")));
		$input_pc=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass1",$this->lng_get_msg_txt("confirm_password","Confirmar contraseña")));
		if($pass_policy->pass_secure_mode===2){
			if(!$user->is_main()){
				$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("secpass",$this->lng_get_msg_txt("secure_password","Contraseña segura")));
				$input->set_value($user->get_data("secpass"));
			}
		}
		if($this->user_must_change_password_enabled){
			$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("must_change_pass",$this->lng_get_msg_txt("must_change_password","Forzar cambio de contraseña en el siguiente log in del usuario")));
			$input->set_value($user->get_data("must_change_pass"));
		}
		$input_chpass=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("change",$this->lng_get_msg_txt("change_password","Modificar contraseña")));
		$pass_policy->prepare_new_pass_inputs($input_p,$input_pc,$input_chpass);
		
		if($rolsman=$this->man->get_rols_man()){
			$rolsman->fix_tbl_fields();
			if($rols=$rolsman->get_assignable_items()){
				if(sizeof($rols)){
					$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle("rols",$this->lng_get_msg_txt("rols","Roles")));
					foreach($rols as $rolcod=>$rol){
					
						$rname=$rol->get_name();
						if($d=$rol->description){
							$rname.=" - $d";
						}
						
						$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox( $rolcod,$rname));
						
						if($user->has_rol_code($rol->get_code())){
							$input->set_value(1);
						}
					}
				}
			}
		}
		$this->add_inputs_groups($gr,$user);
		return $cr;

	}

	
	
	function get_assignable_rols(){
		if($rolsman=$this->man->get_rols_man()){
			return $rolsman->get_assignable_items();
		}
			
	}
	function add_inputs_user_data($grdata){
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("complete_name",$this->lng_get_msg_txt("complete_name","Nombre completo")));
			
	}
	//nuevo usuario
	function create_new_user_from_admin_ui($input,$ui){
		//$input=mwmod_mw_helper_inputvalidator_request
		$alert= new mwmod_mw_bootstrap_html_specialelem_alert(false,"danger");
		$alert->only_visible_when_has_cont=true;
		$ui->set_bottom_alert_msg($alert);
		if(!$input){
			return false;	
		}
		$this->create_new_user_input=false;
		if(!$input->is_req_input_ok()){
			return false;	
		}
		$this->create_new_user_input=$input->get_value_as_list();
		
		
		
		if(!$nd=$input->get_value_by_dot_cod_as_list("data")){
			return false;	
		}
		if(!$insert=$this->set_allowed_change_user_data($nd)){
			return false;	
		}
		
		$msg=false;
		if(!$name=$this->check_user_name($nd["name"],$msg)){
			$alert->add_cont_as_html($msg);
			return false;
		}
		if($this->user_already_exists($name,false,$msg)){
			$alert->add_cont_as_html($msg);
			return false;
		}else{
			$insert["name"]=$name;	
		}
		if($nd["active"]){
			$insert["active"]=1;		
		}
		if($ndrols=$input->get_value_by_dot_cod_as_list("rols")){
		
			if($rols=$this->get_assignable_rols()){
				foreach($rols as $rolcod=>$rol){
					if($fieldcod=$rol->get_tbl_field_name()){
						if(isset($ndrols[$rolcod])){
							if($ndrols[$rolcod]){
								$insert[$fieldcod]=1;
							}else{
								$insert[$fieldcod]=0;
							}
						}
					}
				}
			}
		}
		if(!$nd=$input->get_value_by_dot_cod_as_list("pass")){
			return false;	
		}
		$msg=false;
		if(!$pass=$this->check_passpair_input_by_array($nd,$msg)){
			$alert->add_cont_as_html($msg);
			return false;
		}
		$sec=$this->new_pass_is_secure_by_input($nd["secpass"]??"");
		if($sec){
			$insert["pass"]=$this->man->crypt_password($pass);
			$insert["secpass"]=1;
		}else{
			$insert["pass"]=$pass;
			$insert["secpass"]=0;
		}
		$insert["admin_user_id"]=$nd["admin_user_id"]??0+0;	
		$insert["must_change_pass"]=$nd["must_change_pass"]??0+0;	
		//personal_email
		$sendemail=$nd["sendemail"]??null;
		//mw_array2list_echo($insert);
		
		//return;
		
		if(!$tblman=$this->man->get_tblman()){
			
			return false;	
		}
		
		//mw_array2list_echo($insert);
		if(!$tblitem=$tblman->insert_item($insert)){
			return false;	
		}
		if(!$user=$this->man->load_and_create_user_by_tblitem($tblitem)){
			return false;	
		}
		
		$this->save_user_groups($user,$input->get_value_by_dot_cod_as_list("groups"));
		
		$user->set_new_password($pass);
		$msg=$this->lng_get_msg_txt("user_created","Usuario creado");
		$alert->set_cont($msg);
		$this->create_new_user_input=false;
		$alert->set_display_mode("success");
		if($this->can_send_email_on_create()){
			if($sendemail){
				$this->send_mail_user_on_created($user);	
			}
		}
		return $user;
		
		
		
		
		//mw_array2list_echo($insert);
		
		
		
		
		
	}
	
	function set_new_user_cr($cr=false){
		if(!$cr){
			$cr=new mwmod_mw_datafield_creator();	
		}
		$pass_policy=$this->man->get_pass_policy();
		
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("usernd"));
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("data"));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("name",$this->lng_get_msg_txt("user_name","Nombre de usuario")));
		$input->add_js_email_validation();
		$input->set_required();
		
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("active",$this->lng_get_msg_txt("active","Activo")));
		//$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("email",$this->lng_get_msg_txt("email","Correo")));
		//$input->add_js_email_validation(true);
		//$input->set_required();
		
		
		//$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("complete_name",$this->lng_get_msg_txt("complete_name","Nombre completo")));
		$this->add_inputs_user_data($grdata);
		
		//
		if($this->admin_user_id_enabled){
			$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_users_util_seluserinput("admin_user_id",$this->lng_get_msg_txt("manager","Administrador")));
		}
		
		
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle("pass",$this->lng_get_msg_txt("password","Contraseña")));
		$input_pass=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass",$this->lng_get_msg_txt("password","Contraseña")));
		$input_pass_confirm=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass1",$this->lng_get_msg_txt("confirm_password","Confirmar contraseña")));
		if($pass_policy->pass_secure_mode===2){
			$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("secpass",$this->lng_get_msg_txt("secure_password","Contraseña segura")));
			$input->set_value(1);
		}
		if($this->can_send_email_on_create()){
			$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("sendemail",$this->lng_get_msg_txt("send_email","Enviar correo")));
		}
		
		$pass_policy->prepare_new_pass_inputs($input_pass,$input_pass_confirm);
		if($this->user_must_change_password_enabled){
			$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("must_change_pass",$this->lng_get_msg_txt("must_change_password","Forzar cambio de contraseña en el siguiente log in del usuario")));

		}
		if($rolsman=$this->man->get_rols_man()){
			$rolsman->fix_tbl_fields();
			if($rols=$rolsman->get_assignable_items()){
				$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle("rols",$this->lng_get_msg_txt("rols","Roles")));
				foreach($rols as $rolcod=>$rol){

					$rname=$rol->get_name();
					if($d=$rol->description){
						$rname.=" - $d";
					}

					$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox( $rolcod,$rname));
				}
			}
		}
		$this->add_inputs_groups($gr);
		if($this->create_new_user_input){
			$gr->set_value($this->create_new_user_input);	
		}

		return $cr;	
	}
	
	
	//forms
	
	function set_user_changepass_cr($user,$cr=false){
		if(!$cr){
			$cr=new mwmod_mw_datafield_creator();	
		}
		$pass_policy=$this->man->get_pass_policy();
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("usernd"));
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("pass"));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("currentpass",$this->lng_get_msg_txt("current_password","Contraseña actual")));
		$input->set_required();
		
		
		$input_pass=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass",$this->lng_get_msg_txt("password","Contraseña")));
		$input_pass->set_required();
		$input_pass_confirm=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass1",$this->lng_get_msg_txt("confirm_password","Confirmar contraseña")));
		
		$pass_policy->prepare_new_pass_inputs($input_pass,$input_pass_confirm);
		
		return $cr;

	}
	function savefromfrm_user_changepass($input,$user,$uimsgelem=false){
		//$input=mwmod_mw_helper_inputvalidator_request
		if(!$input){
			return false;	
		}
		if(!$input->is_req_input_ok()){
			return false;	
		}
		if(!$current=$input->get_value_by_dot_cod("pass.currentpass")){
			return false;	
		}
		if(!$user->check_login_pass($current)){
			if($uimsgelem){
				$alert=new mwmod_mw_bootstrap_html_specialelem_alert($this->lng_get_msg_txt("invalid_current_password","Contraseña actual no válida"),"danger");	
				$uimsgelem->add_cont($alert);
			}
			return false;
		}
		
		
		if(!$nd=$input->get_value_by_dot_cod_as_list("pass")){
			return false;	
		}
		if(!$pass=$nd["pass"]){
			return false;
		}
		$msg="";
		
		if(!$pass=$this->check_passpair_input_by_array($nd,$msg)){
			if($msg){
				if($uimsgelem){
					$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"danger");	
					$uimsgelem->add_cont($alert);
				}
			}
			return false;
		}
		if($user->is_main()){
			$sec=1;
		}else{
			$sec=$user->get_password_is_encrypted();
		}
		$update=array();
		//$new_pass=$this->man->crypt_password($pass);
		
		if($sec){
			$update["pass"]=$this->man->crypt_password($pass);
			$update["secpass"]=1;
		}else{
			$update["pass"]=$pass;
			$update["secpass"]=0;
		}
		$update["must_change_pass"]=0;
		//mw_array2list_echo($update);
		//return;
		if(!$tblitem=$user->tblitem){
			return false;	
		}
		$tblitem->do_update($update);
		$user->set_new_password($pass);
		$msg=$this->lng_get_msg_txt("password_updated","Contraseña actualizada");
		if($uimsgelem){
			$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg);	
			$uimsgelem->add_cont($alert);
		}
		
		return true;
		
		
	}
	
	function set_user_changepassfromadmin_cr($user,$cr=false){
		if(!$cr){
			$cr=new mwmod_mw_datafield_creator();	
		}
		$pass_policy=$this->man->get_pass_policy();
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("usernd"));
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("name",$this->lng_get_msg_txt("user_name","Nombre de usuario")));
		$input->set_value($user->get_idname());
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("pass"));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass",$this->lng_get_msg_txt("password","Contraseña")));
		$input->set_required();
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass1",$this->lng_get_msg_txt("confirm_password","Confirmar contraseña")));
		if($pass_policy->pass_secure_mode===2){
			if(!$user->is_main()){
				$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("secpass",$this->lng_get_msg_txt("secure_password","Contraseña segura")));
				$input->set_value($user->get_data("secpass"));
			}
		}
		if($this->can_send_email_on_pass_change()){
			$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("sendemail",$this->lng_get_msg_txt("send_email","Enviar correo")));
		}
		return $cr;

	}
	function savefromfrm_user_changepassfromadmin($input,$user,&$msg=""){
		//$input=mwmod_mw_helper_inputvalidator_request
		if(!$input){
			return false;	
		}
		if(!$input->is_req_input_ok()){
			return false;	
		}
		if(!$nd=$input->get_value_by_dot_cod_as_list("pass")){
			return false;	
		}
		if(!$pass=$nd["pass"]){
			return false;
		}
		
		
		if(!$pass=$this->check_passpair_input_by_array($nd,$msg)){
			return false;
		}
		if($user->is_main()){
			$sec=1;
		}else{
			$sec=$this->new_pass_is_secure_by_input($nd["secpass"]??null);	
		}
		$update=array();
		//$new_pass=$this->man->crypt_password($pass);
		
		if($sec){
			$update["pass"]=$this->man->crypt_password($pass);
			$update["secpass"]=1;
		}else{
			$update["pass"]=$pass;
			$update["secpass"]=0;
		}
		if(!$tblitem=$user->tblitem){
			return false;	
		}
		$tblitem->do_update($update);
		$user->set_new_password($pass);
		$msg=$this->lng_get_msg_txt("password_updated","Contraseña actualizada");
		if($this->can_send_email_on_pass_change()){
			if($nd["sendemail"]??null){
				$this->send_mail_user_new_password($user);
			}
		}
		return true;
		
		
	}
	
	function set_user_accessdata_cr($user,$cr=false){
		if(!$cr){
			$cr=new mwmod_mw_datafield_creator();	
		}
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("usernd"));
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("name",$this->lng_get_msg_txt("user_name","Nombre de usuario")));
		$input->set_value($user->get_idname());
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("accessdata"));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("name",$this->lng_get_msg_txt("user_name","Nombre de usuario")));
		//$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("email",$this->lng_get_msg_txt("email","Correo")));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("active",$this->lng_get_msg_txt("active","Activo")));
		
		$grdata->set_value($user->get_public_tbl_data());
		return $cr;

	}
	function savefromfrm_user_accessdata($input,$user,&$msg=""){
		//$input=mwmod_mw_helper_inputvalidator_request
		if(!$input){
			return false;	
		}
		if(!$input->is_req_input_ok()){
			return false;	
		}
		if(!$nd=$input->get_value_by_dot_cod_as_list("accessdata")){
			return false;	
		}
		$ndupdate=array();
		if($val=$nd["name"]??null){
			if($val!=$user->get_idname()){
				//$msg
				if($val=$this->check_user_name($val,$msg)){
					if(!$this->user_already_exists($val,$user,$msg)){
						$ndupdate["name"]=$val;	
					}
				}
			}
		}
		if($val=trim($nd["email"]??"")){
			if($val!=$user->get_data("email")){
				if(mw_checkemail($val)){
					$ndupdate["email"]=$val;	
				}else{
					$msg=	$this->lng_get_msg_txt("invalid_mail","Correo no válido");
				}
			}
		}
		if(isset($nd["active"])){
			$ndupdate["active"]=$nd["active"]+0;		
		}
		if(sizeof($ndupdate)){
			return $user->tblitem->do_update($ndupdate);
		}
		
	}
	
	function set_user_rols_cr($user,$cr=false){
		if(!$cr){
			$cr=new mwmod_mw_datafield_creator();	
		}
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("usernd"));
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("name",$this->lng_get_msg_txt("user_name","Nombre de usuario")));
		$input->set_value($user->get_idname());
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle("rols",$this->lng_get_msg_txt("rols","Roles")));
		if($rolsman=$this->man->get_rols_man()){
			$rolsman->fix_tbl_fields();
			if($rols=$rolsman->get_assignable_items()){
				foreach($rols as $rolcod=>$rol){
					$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox( $rolcod,$rol->get_name()));
					if($user->has_rol_code($rol->get_code())){
						$input->set_value(1);
					}
					/*
					if($c=$rol->get_tbl_field_name()){
						$notallowed[]=$c;	
					}
					*/
				}
			}
		}
		
		
		//$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("complete_name",$this->get_msg("Nombre completo")));
		//$gr->set_value($user->get_data_for_admin());
		//$gr->set_output_as_html();
		return $cr;

	}
	function savefromfrm_user_rols($input,$user,&$msg=""){
		//$input=mwmod_mw_helper_inputvalidator_request
		if(!$input){
			return false;	
		}
		if(!$input->is_req_input_ok()){
			return false;	
		}
		if(!$nd=$input->get_value_by_dot_cod_as_list("rols")){
			return false;	
		}
		if(!$rolsman=$this->man->get_rols_man()){
			return false;	
		}
		$ndupdate=array();
		if(!$rols=$rolsman->get_assignable_items()){
			return false;
		}
		foreach($rols as $rolcod=>$rol){
			if($fieldcod=$rol->get_tbl_field_name()){
				if(isset($nd[$rolcod])){
					if($nd[$rolcod]){
						$ndupdate[$fieldcod]=1;
					}else{
						$ndupdate[$fieldcod]=0;
					}
				}
			}
		}
		if(sizeof($ndupdate)){
			return $user->tblitem->do_update($ndupdate);
		
				
		}
	}
	
	
	
	function set_user_data_cr($user,$cr=false){
		if(!$cr){
			$cr=new mwmod_mw_datafield_creator();	
		}
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("usernd"));
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("name",$this->lng_get_msg_txt("user_name","Nombre de usuario")));
		$input->set_value($user->get_idname());
		//$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("email",$this->lng_get_msg_txt("email","Correo")));
		//$input->set_value($user->get_data("email"));
		
		
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("data"));
		
		$this->add_inputs_user_data($grdata);
		$grdata->set_value($user->get_public_tbl_data());
		if($idadmin=$user->get_data("admin_user_id")){
			if($adminu=$this->man->get_user($idadmin)){
				$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("admin_user",$this->lng_get_msg_txt("manager","Administrador")));
				$input->set_value($adminu->get_idname_and_real());
					
			}
		}
		
		
		return $cr;

	}
	function savefromfrm_user_data($input,$user,&$msg=""){
		//$input=mwmod_mw_helper_inputvalidator_request
		if(!$input){
			return false;	
		}
		if(!$input->is_req_input_ok()){
			return false;	
		}
		if(!$nd=$input->get_value_by_dot_cod_as_list("data")){
			return false;	
		}
		if($nd=$this->set_allowed_change_user_data($nd)){
			if(isset($nd["admin_user_id"])){
				$nd["admin_user_id"]=$nd["admin_user_id"]+0;	
			}
			return $user->tblitem->do_update($nd);
			//echo "ok";	
		}
	}
	function get_not_allowed_data_change_keys($allowed=false){
		$notallowedstr="id,name,pass,secpass,is_main,active,email,last_login_date,last_login_ip";
		$notallowedstr.=",reset_pass_code,reset_pass_enabled,reset_pass_expires";
		$notallowed=explode(",",$notallowedstr);
		if($rolsman=$this->man->get_rols_man()){
			if($rols=$rolsman->get_assignable_items()){
				foreach($rols as $rol){
					if($c=$rol->get_tbl_field_name()){
						$notallowed[]=$c;	
					}
				}
			}
		}
		$r=array();
		if($allowed){
			if(!is_array($allowed)){
				$a=explode(",",$allowed);
				$allowed=array();
				foreach($a as $c){
					$allowed[$c]=$c;	
				}
			}
		}
		reset($notallowed);
		foreach($notallowed as $c){
			$ok=true;
			if($allowed){
				if(is_array($allowed)){
					if(isset($allowed[$c])){
						if($allowed[$c]){
							$ok=false;	
						}
						
					}
				}
			}
			if($ok){
				$r[$c]=$c;
			}
		}
		return $r;
			
	}
	function set_allowed_change_user_data($nd,$allowed=false){
		if(!is_array($nd)){
			return false;
		}
		$notallowed=$this->get_not_allowed_data_change_keys($allowed);
		foreach($notallowed as $c){
			unset($nd[$c]);	
		}
		if(sizeof($nd)){
			return $nd;
		}

	}
	function set_user_info_cr($user,$cr=false){
		if(!$cr){
			$cr=new mwmod_mw_datafield_creator();	
		}
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("user"));
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("name",$this->get_msg("Nombre de usuario")));
		$grdata=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("data"));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("complete_name",$this->get_msg("Nombre completo")));
		//$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("email",$this->get_msg("Email")));
		$input=$grdata->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("active",$this->get_msg("Activo")));
		if($rolsman=$user->man->get_rols_man()){
			if($rols=$rolsman->get_assignable_items()){
				$rolsgr=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle("rols",$this->get_msg("Roles")));
				foreach($rols as $cod=>$rol){
					$rolsgr->add_item(new mwmod_mw_datafield_checkbox($cod,$rol->get_name()));
				}
			}
		}
		$gr->set_value($user->get_data_for_admin());
		$gr->set_output_as_html();
		return $cr;

	}
	
	//save data
	function save_from_admin_pass_user($user,$input,&$msg=""){
		if(!is_array($input)){
			return NULL;	
		}
		
		if(!$pass=$this->check_passpair_input_by_array($input,$msg)){
			return false;
		}
		if($user->is_main()){
			$sec=1;
		}else{
			$sec=$this->new_pass_is_secure_by_input($input["secpass"]);	
		}
		$update=array();
		//$new_pass=$this->man->crypt_password($pass);
		
		if($sec){
			$update["pass"]=$this->man->crypt_password($pass);
			$update["secpass"]=1;
		}else{
			$update["pass"]=$pass;
			$update["secpass"]=0;
		}
		$update["reset_pass_code"]="";
		$update["reset_pass_enabled"]="0";
		/*
		$nd=array(
		"reset_pass_code"=>$token,
		"reset_pass_enabled"=>1,
		"reset_pass_expires"=>$exp,
		*/
		
		if(!$tblitem=$user->tblitem){
			return false;	
		}
		
		
		$tblitem->do_update($update);
		$user->set_new_password($pass);
		$msg=$this->lng_get_msg_txt("password_updated","Contraseña actualizada");
		return true;
		
	
	}
	
	function new_pass_is_secure_by_input($input){
		$pass_policy=$this->man->get_pass_policy();
		return $pass_policy->new_pass_is_secure_by_input($input);
	}
	
	function check_passpair_input_by_array($input,&$msg=""){
		$pass_policy=$this->man->get_pass_policy();
		return $pass_policy->check_passpair_input_by_array($input,$msg);
		
	}
	function check_passpair_input($pass,$pass1,&$msg=""){
		$pass_policy=$this->man->get_pass_policy();
		return $pass_policy->check_passpair_input($pass,$pass1,$msg);
		
	}
	function check_new_pass($pass,&$msg=""){
		$pass_policy=$this->man->get_pass_policy();
		return $pass_policy->check_new_pass($pass,$msg);
	}
	
	function user_already_exists($name,$current=false,&$msg=""){
		if(!$user=$this->man->get_user_by_idname_case_insensitive($name)){
			return false;
		}
		if($current){
			if($user->get_id()===$current->get_id()){
				return false;	
			}
		}
		$msg=$this->lng_get_msg_txt("user_already_exists","El usuario ya existe");
		return $user;
		
		
	}
	function check_user_name($name,&$msg=""){
		if($r=trim($name)){
			if(!mw_checkemail($name)){
				return false;	
			}
			return strtolower($r);	
		}
		$msg=$this->lng_get_msg_txt("invalid_user_name","Nombre de usuario no válido");
		return false;
		
	}
	function create_main_admin_user($name,$passinput,$data,&$msg=""){
		$msg="";
		if($user=$this->man->get_main_admin_user()){
			$msg=$this->lng_get_msg_txt("admin_user_already_exists","Usuario principal ya existe");
			return false;	
		}
		
		if(!$name=$this->check_user_name($name,$msg)){
			$msg=$this->lng_get_msg_txt("admin_user_invalid_name","Nombre de usuario no válido");
			return false;
		}
		if($user=$this->user_already_exists($name,false,$msg)){
			return false;
		}
		if(!$pass=$this->check_passpair_input_by_array($passinput,$msg)){
			return false;
		}
		$update=array();
		$update["pass"]=$this->man->crypt_password($pass);
		$update["secpass"]=1;
		$update["id"]=1;
		$update["name"]=$name;
		if(is_array($data)){
			/*
			if(mw_checkemail($data["email"])){
				$update["email"]=$data["email"];
			}
			*/
			if($data["complete_name"]){
				$update["complete_name"]=$data["complete_name"];	
			}
			
		}
		$update["active"]=1;
		$update["is_main"]=1;
		if(!$tblman=$this->man->get_tblman()){
			return false;	
		}
		$sql="update ".$tblman->tbl;
		$sql.=" set is_main=0 where id<>1 ";
		$tblman->dbman->query($sql);
		//echo $sql;
		$this->man->unset_main_admin_user();
		if(!$tblitem=$tblman->insert_item_width_id($update)){
			$msg=$this->lng_get_msg_txt("unable_to_create_user","No se pudo crear usuario");
			//$msg.="<br>".$tblman->dbman->get_error();
			return false;	
				
		}
		$msg=$this->lng_get_msg_txt("user_created","Usuario creado");
		return $this->man->get_main_admin_user();
	}
	function can_send_email_on_pass_change(){
		if($man=$this->man->get_user_mailer()){
			return $man->msg_on_pass_change_enabled();	
		}
		return false;	
	}
	function can_send_email_on_create(){
		if($man=$this->man->get_user_mailer()){
			return $man->msg_on_user_created_enabled();	
		}
		return false;	
	}
	function send_mail_user_on_created($user){
		if(!$this->can_send_email_on_create()){
			return false;	
		}
		if($man=$this->man->get_user_mailer()){
			return $man->msg_on_user_created_send($user);	
		}
	}
	function send_mail_user_new_password($user){
		if(!$this->can_send_email_on_pass_change()){
			return false;	
		}
		if($man=$this->man->get_user_mailer()){
			return $man->msg_on_pass_change_send($user);	
		}
	}
	//imagenes
	function delete_profile_img($user){
		if(!$gr=$this->prepare_profile_imgs_group($user)){
			return false;	
		}
		if(!$gr->delete()){
			return false;	
		}
		$updatand=array(
					"image"=>""
				);
		$user->tblitem->do_update($updatand);
		$this->set_profile_imgs_urls($gr,$user);
		return true;
			
	}
	
	function upload_profile_imgs_from_input_crop($input,$user,$msgcontainer=false){
		if(!is_array($input)){
			return false;	
		}
		if(!$gr=$this->prepare_profile_imgs_group($user)){
			return false;	
		}
		/*
		if(!$ado=$user->get_treedata_item("profileimg")){
			return false;
		}
		*/
		
		$errorinfo=array();
		if($gr->check_upload_new_img_invalid_attemp($input,$errorinfo)){
			if($msgcontainer){
				$msg=$this->lng_get_msg_txt("invalid_image_file","Archivo de imagen no válido");
				$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg,"danger");
				$msgcontainer->add_cont($alert);
			}
				
		}elseif($new=$gr->upload_new_img_and_proc($input)){
			$updatand=array(
					"image"=>$new
				);
			$user->tblitem->do_update($updatand);
			$this->set_profile_imgs_urls($gr,$user);
			return true;
		}
		
	}

	function prepare_profile_imgs_group($user){
		if(!$gr=$this->get_profile_imgs_group($user)){
			return false;	
		}
		if($rel=$user->get_rel_path()){
			if($pm=$this->mainap->get_sub_path_man($rel."/profileimgtemp","userfilespublic")){
				
				$gr->set_temp_sub_path_man($pm);
			}
		}
		return $gr; 
	}
	function get_profile_imgs_group($user){
		if($gr=$user->profile_imgs_group){
			if($gr->is_enabled()){
				return $gr;	
			}
		}
		return false;	
			
	}

	function create_profile_imgs_group($user){
		return false;
		//$man= new mwmod_mw_helper_img_gr_imgsgr();
		//$this->init_profile_imgs_group($man,$user);
		//return $man;
		/*
		*/
	}
	function set_cfg_profile_imgs_group($group,$user){
		//extender, configuración de imagenes de usuario	
	}
	function set_profile_imgs_urls($group,$user){
		$filename=$user->tblitem->get_data("image");
		$title=$user->get_real_name();
		$url= $this->mainap->get_public_userfiles_url_path()."/".$user->get_rel_path()."/profileimg";
		$realpath=false;
		$pm=$this->mainap->get_sub_path_man($user->get_rel_path()."/profileimg","userfilespublic");
		$group->set_info_and_url_by_public_path($url,$filename,$title,$pm);
	
	}
	function init_profile_imgs_group($group,$user){
		$this->set_cfg_profile_imgs_group($group,$user);
		$this->set_profile_imgs_urls($group,$user);
	}
	////////////////////////////////
	final function __get_priv_man(){
		return $this->man; 	
	}
	
	//init
	final function init($man){
		$this->man=$man;
		$ap=$man->mainap;
		
		$this->set_mainap($ap);
		$this->set_lngmsgsmancod("user");

					
	}

	
}
?>