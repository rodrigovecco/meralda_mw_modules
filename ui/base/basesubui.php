<?php
abstract class mwmod_mw_ui_base_basesubui extends mwmod_mw_ui_sub_uiabs{
	public $sucods;
	
	
	/*Todos los permisos configurados solo para admin*/
	function is_allowed(){
		if($p=$this->permissionInheritEnabled()){
			return $p->is_allowed();
		}
		return $this->allow_admin();	
	}
	function allow_admin(){
		if($p=$this->permissionInheritEnabled()){
			return $p->allow_admin();
		}
		return $this->allow("admin");	
	}
	function allow_edit(){
		if($p=$this->permissionInheritEnabled()){
			return $p->allow_edit();
		}
		return $this->allow("admin");	
	}
	function allow_view(){
		if($p=$this->permissionInheritEnabled()){
			return $p->allow_view();
		}
		if($this->allow("admin")){
			return true;	
		}
		return false;
			
	}
	function permissionInheritEnabled(){
		if($this->parent_subinterface){
			if($this->parent_subinterface->childrenInheritPermissions()){
				return $this->parent_subinterface;
			}
		}
	}
	function childrenInheritPermissions(){
		return true;
	}
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