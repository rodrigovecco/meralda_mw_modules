<?php
/**
 * Base subinterface class with admin-only permissions and permission inheritance.
 *
 * Provides a foundation for admin-restricted subinterfaces with permission
 * inheritance from parent subinterfaces and menu creation for child subinterfaces.
 */
abstract class mwmod_mw_ui_base_basesubui extends mwmod_mw_ui_sub_uiabs{
	/**
	 * Comma-separated string of subinterface codes to be included in the menu.
	 * @var string|null
	 */
	public $sucods;
	public $mnuStructure;
	
	
	/**
	 * Checks if the current user is allowed to access this subinterface.
	 *
	 * Delegates to parent subinterface if permission inheritance is enabled,
	 * otherwise requires admin permission.
	 *
	 * @return bool True if access is allowed, false otherwise.
	 */
	function is_allowed(){
		if($p=$this->permissionInheritEnabled()){
			return $p->is_allowed();
		}
		return $this->allow_admin();	
	}
	
	/**
	 * Checks if the current user has admin permission.
	 *
	 * Delegates to parent subinterface if permission inheritance is enabled,
	 * otherwise checks for admin role.
	 *
	 * @return bool True if user has admin permission, false otherwise.
	 */
	function allow_admin(){
		if($p=$this->permissionInheritEnabled()){
			return $p->allow_admin();
		}
		return $this->allow("admin");	
	}
	
	/**
	 * Checks if the current user has edit permission.
	 *
	 * Delegates to parent subinterface if permission inheritance is enabled,
	 * otherwise requires admin permission.
	 *
	 * @return bool True if user can edit, false otherwise.
	 */
	function allow_edit(){
		if($p=$this->permissionInheritEnabled()){
			return $p->allow_edit();
		}
		return $this->allow("admin");	
	}
	
	/**
	 * Checks if the current user has view permission.
	 *
	 * Delegates to parent subinterface if permission inheritance is enabled,
	 * otherwise requires admin permission.
	 *
	 * @return bool True if user can view, false otherwise.
	 */
	function allow_view(){
		if($p=$this->permissionInheritEnabled()){
			return $p->allow_view();
		}
		if($this->allow("admin")){
			return true;	
		}
		return false;
			
	}
	
	/**
	 * Gets the parent subinterface if permission inheritance is enabled.
	 *
	 * Checks if there is a parent subinterface that allows children to inherit permissions.
	 *
	 * @return mwmod_mw_ui_sub_uiabs|false The parent subinterface if inheritance is enabled, false otherwise.
	 */
	function permissionInheritEnabled(){
		if($this->parent_subinterface){
			if($this->parent_subinterface->childrenInheritPermissions()){
				return $this->parent_subinterface;
			}
		}
	}
	
	/**
	 * Indicates whether child subinterfaces inherit permissions from this subinterface.
	 *
	 * Override in child classes to disable permission inheritance.
	 *
	 * @return bool True to enable permission inheritance for children, false to disable.
	 */
	function childrenInheritPermissions(){
		return true;
	}
	
	/**
	 * Creates a menu for subinterfaces defined in the sucods property.
	 *
	 * Retrieves all subinterfaces by their comma-separated codes in sucods
	 * and asks each to add itself to the menu.
	 *
	 * @param mwmod_mw_ui_sub_uiabs|false $su Optional subinterface parameter (unused).
	 * @return mwmod_mw_mnu_mnu The created menu with subinterface items.
	 */
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		
		
		return $mnu;
	}

	
}
?>