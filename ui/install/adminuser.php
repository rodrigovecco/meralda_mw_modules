<?php
class mwmod_mw_ui_install_adminuser extends mwmod_mw_ui_sub_withfrm{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_def_title($this->get_msg("Usuario principal"));
		
	}
	function do_exec_no_sub_interface(){
		if(!$this->allow_edit_user()){
			return false;	
		}
	}
	function do_exec_page_in_update($nd){
		if(!is_array($nd)){
			return false;
		}
		//mw_array2list_echo($nd);
		if(!$uman=$this->mainap->get_user_manager()){
			echo "<p>".$this->get_msg("No permitido.")."</p>";	
			return false;	
		}
		$userdataman=$uman->get_user_data_man();
		if(!$token=$nd["adminuser_token"]){
			echo "<p>".$this->get_msg("Contraseña maestra no válida.")."</p>";	
			return false;	
		}
		if($token!==$this->maininterface->get_cfg_data("setupmainuser.pass")){
			echo "<p>".$this->get_msg("Contraseña maestra no válida.")."</p>";	
			return false;	
		}
		$user=$uman->get_main_admin_user();
		
		
		if($user){
			if(is_array($nd["pass"])){
				if($nd["pass"]["change"]){
					$msg="";
					if(!$userdataman->save_from_admin_pass_user($user,$nd["pass"],$msg)){
						//echo "no";
							
					}
					echo "<p>".$msg."</p>";	
					/*
					$ndp=$nd["pass"];
					$ndp["secpass"]=1;
					if($user->save_from_admin_pass($ndp)){
						echo "<p>".$this->get_msg("Contraseña actualizada.")."</p>";		
					}else{
						echo "<p>".$this->get_msg("Contraseña no válida.")."</p>";			
					}
					*/
				}
			}
			if(is_array($nd["data"])){
				//mw_array2list_echo($nd["data"]);
				$user->save_from_install_data($nd["data"]);	
			}
			//save_from_admin_data
		}else{
			
			if(!$name=$nd["data"]["name"]){
				echo "<p>".$this->get_msg("Nombre de usuario no válido")."</p>";	
				return false;	
			}
			$msg="";
			if($user=$uman->create_main_admin_user($name,$nd["pass"],$nd["data"],$msg)){
				///
			}else{
				echo $uman->get_tblman()->dbman->get_error()."qqq";
			}
			echo "<p>".$msg."</p>";	
			return $user;
		}
		
	}
	function do_exec_page_in(){
		if(!$this->allow_edit_user()){
			echo "<p>".$this->get_msg("No permitido.")."</p>";	
			return false;	
		}
		if(!$uman=$this->mainap->get_user_manager()){
			echo "<p>".$this->get_msg("No permitido.")."</p>";	
			return false;	
		}
		//todo use input manager

		if(is_array($_REQUEST["nd"]?? false)){
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
		
		/*
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("email",$this->get_msg("Email")),"data");
		if($user){
			$input->set_value($user->get_email());	
		}
		*/
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
	function allow_edit_user(){
		if(!$this->is_allowed()){
			return false;	
		}
		return $this->maininterface->get_cfg_data("setupmainuser.allowed");
	}
	function is_allowed(){
		return $this->maininterface->install_credentials_ok();
	}
	
}
?>