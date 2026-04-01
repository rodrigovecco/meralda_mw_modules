<?php
class mwmod_mw_ap_def2  extends mwmod_mw_ap_def{
	
	
	function create_submanager_user(){
		
		$subman= new mwmod_mw_users2_def_usersman($this,"users");
		$subman->set_disable_login_after_fail(true,5,3);
		$subman->enable_login_session_token(true);
		$rolsman=new mwmod_mw_users_rols_rolsman($subman);
		$subman->set_rols_man($rolsman);
		$rol=$rolsman->add_item(new mwmod_mw_users_rols_rolpublic("public","Público",$rolsman));
		$rol=$rolsman->add_item(new mwmod_mw_users_rols_rolall("all","Todos",$rolsman));
		$rol->name_for_permitions_option="Usuarios registrados";
		$rol=$rolsman->add_item(new mwmod_mw_users_rols_rol("user","Usuario",$rolsman));
		$rol=$rolsman->add_item(new mwmod_mw_users_rols_rol("admin","Administrador",$rolsman));
		$rol->allways_for_mainadmin=true;
		$rol->description="Con acceso a todas las funciones";



		$permissionsman=new mwmod_mw_users_permissions_permissionsman($subman,$rolsman);
		$subman->set_permissions_man($permissionsman);
		$permissionsman->add_item(new mwmod_mw_users_permissions_permission(
							"view","Acceder a la interface","admin,user",$permissionsman));
		$permissionsman->add_item(new mwmod_mw_users_permissions_permission(
							"editmydata","Editar datos propios","user,admin",$permissionsman));
		$permissionsman->add_item(new mwmod_mw_users_permissions_permission(
							"adminusers","Administrar usuarios","admin",$permissionsman));
		$permissionsman->add_item(new mwmod_mw_users_permissions_permission(
							"admin","Administrar","admin",$permissionsman));
		$permissionsman->add_item(new mwmod_mw_users_permissions_permission(
							"mainadmin","Administración","admin",$permissionsman));

		$permissionsman->add_item(new mwmod_mw_users_permissions_special_debug(
							"debug","Pruebas del sistema",false,$permissionsman));

		$permissionsman->init_rols();

		return $subman;
	}
	

	
}

?>