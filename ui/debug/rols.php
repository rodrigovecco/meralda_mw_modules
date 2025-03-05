<?php
class mwmod_mw_ui_debug_rols extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->get_msg("Roles"));
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		echo $_SERVER['REMOTE_ADDR'];
		
		if(!$uman=$this->mainap->get_user_manager()){
			echo "<p>".$this->get_msg("No hay manejador de usuarios.")."</p>";	
			return false;	
		}
		$permissions=false;
		if($permissions_man=$uman->get_permission_man()){
			if($permissions=$permissions_man->get_items()){
				$d=array();
				foreach($permissions as $id=>$permission){	
					$d[$id]=$permission->get_debug_data();
				}
				mw_array2list_echo($d,"permisos");
			}
		}
		if($users=$uman->get_all_useres()){
			$d=array();
			foreach($users as $id=>$user){
				$d[$id]=array("user"=>$user->get_idname());
				$d[$id]["roles"]=$user->get_rols();	
				$d[$id]["permisions"]=$user->get_permissions();
				if($permissions){
					reset($permissions);
					$d[$id]["permisions_effective"]=array();
					foreach($permissions as $cod=>$permission){
						if($user->allow($cod)){
							$pd="YES";	
						}else{
							$pd="NO";	
						}
						$d[$id]["permisions_effective"][$cod]=$pd;	
					}
				}
			}
			mw_array2list_echo($d,"usuarios");
		}
		
		
		if(!$rolesman=$uman->get_rols_man()){
			echo "<p>".$this->get_msg("No hay manejador de roles.")."</p>";	
			return false;	
		}
		mw_array2list_echo($rolesman->get_debug_data(),"roles");
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>