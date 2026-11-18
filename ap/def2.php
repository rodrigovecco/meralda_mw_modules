<?php
class mwmod_mw_ap_def2  extends mwmod_mw_ap_def{

	/**
	 * Class used to build the users manager.
	 *
	 * A PROJECT OVERRIDES THIS, NOT create_submanager_user(). The construction
	 * below wires roles, permissions and brute-force protection, and a project
	 * that copied it to change one line would own a copy of all of it — the kind
	 * of copy that stops matching the framework the next time a permission is
	 * added here.
	 *
	 * The reason a project needs its own subclass at all: features the users
	 * manager hands out are created by protected factories that default to
	 * "disabled" (OAuth is the current example). Enabling one means overriding a
	 * factory, which needs the subclass, which needs this hook.
	 *
	 * @return string  a class name extending mwmod_mw_users2_usersmanabs
	 */
	function get_users_managers_class_name() {
		return "mwmod_mw_users2_def_usersman";
	}

	function create_submanager_user(){

		$class=$this->get_users_managers_class_name();
		$subman= new $class($this,"users");
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
		$permissionsman->add_item(new mwmod_mw_users_permissions_permission(
							"owntoken","Gestionar tokens de API propios","user,admin",$permissionsman));
		$permissionsman->add_item(new mwmod_mw_users_permissions_special_debug(
							"debug","Pruebas del sistema",false,$permissionsman));

		$permissionsman->init_rols();

		return $subman;
	}

	function createJwtMan() {
		return new mwmod_mw_users_jwt_man();
	}
	

	
}

?>