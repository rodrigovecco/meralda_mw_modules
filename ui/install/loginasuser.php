<?php
class mwmod_mw_ui_install_loginasuser extends mwmod_mw_ui_sub_withfrm{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_def_title($this->get_msg("Iniciar sesión de usuario"));
		
	}
	function do_exec_no_sub_interface(){
		if(!$this->is_allowed()){
			return false;	
		}
		if($uman=$this->mainap->get_user_manager()){
			if(is_array($_REQUEST["nd"])){
				if($idu=$_REQUEST["nd"]["user_id"]){
					if($user=$uman->get_user($idu)){
						$uman->login_user($user);
						return;
					}
				}
			}
			$uman->exec_user_validation();
		}
		
	}
	function do_exec_page_in(){
		if(!$this->is_allowed()){
			return false;	
		}
		if(!$uman=$this->mainap->get_user_manager()){
			echo "<p>No hay manejador de usuarios</p>";	
			return false;	
		}
		
		if($user=$this->mainap->get_current_user()){
			echo "<p>Usuario actual: ".$user->get_idname()." ".$user->get_real_name()."</p>";
				
		}
		$frm=$this->new_frm();
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$cr->items_pref="nd";
		
		$sel=$cr->add_item(new mwmod_mw_datafield_select("user_id",$this->get_msg("Usuario")));
		$sel->create_optionslist($uman->get_all_useres());
		
		$cr->add_submit($this->get_msg("Login"));
		$frm->set_datafieldcreator($cr);
		//$frm->ask_before_submit_debug=true;
		echo $frm->get_html();
		if($adminui=$this->mainap->admin_ui){
			echo "<p><a href='".$adminui->get_url()."' target='_blank'>".$adminui->get_name()."</a></p>";	
		}
		
		
		echo "ddd";
	}
	/*
	function do_exec_page_in(){
		if(!$this->allow_edit_user()){
			echo "<p>".$this->get_msg("No permitido.")."</p>";	
			return false;	
		}
		if(!$uman=$this->mainap->get_user_manager()){
			echo "<p>".$this->get_msg("No permitido.")."</p>";	
			return false;	
		}

		if(is_array($_REQUEST["nd"])){
			$this->do_exec_page_in_update($_REQUEST["nd"]);
		}
		$pass_policy=$uman->get_pass_policy();
		$frm=$this->new_frm();
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$cr->items_pref="nd";
		
		if(!$user=$uman->get_main_admin_user()){
			echo "<p>".$this->lng_get_msg_txt("create_main_user","Crear usuario principal")."</p>";	
		}else{
			echo "<p>".$this->lng_get_msg_txt("edit_main_user","Editar usuario principal")."</p>";	
		}
		
		$cr->add_item(new mwmod_mw_datafield_password("adminuser_token",$this->get_msg("Contraseña maestra")));
		
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("name",$this->get_msg("Usuario")),"data");
		if($user){
			$input->set_value($user->get_idname());	
			//$input->set_readonly();
		}else{
			//$input->set_value("admin");		
		}
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("complete_name",$this->get_msg("Nombre completo")),"data");
		if($user){
			$input->set_value($user->get_real_name());	
		}
		
		$input_chpass=false;
		if($user){
			$input_chpass=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox("change",$this->get_msg("Cambiar contraseña")),"pass");
		}
		$input_p=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass",$this->get_msg("Contraseña")),"pass");
		$input_pc=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_password("pass1",$this->get_msg("Confirmar contraseña")),"pass");
		$pass_policy->prepare_new_pass_inputs($input_p,$input_pc,$input_chpass);
		
		
		$cr->add_submit($this->get_msg("Enviar"));
		$frm->set_datafieldcreator($cr);
		//$frm->ask_before_submit_debug=true;
		echo $frm->get_html();

		
	}
	*/
	function is_allowed(){
		return $this->maininterface->install_credentials_ok();
	}
	
}
?>