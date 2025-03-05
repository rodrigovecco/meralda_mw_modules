<?php
class mwmod_mw_ui_debug_mnus extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->get_msg("Menus"));
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		if(!$man=$this->maininterface->get_mnu_man()){
			echo "no hay";
			return;	
		}
		mw_array2list_echo($man->get_debug_data());
		
		
		/*
		if(!$uman=$this->mainap->get_user_manager()){
			echo "<p>".$this->get_msg("No hay manejador de usuarios.")."</p>";	
			return false;	
		}
		if($users=$uman->get_all_useres()){
			$d=array();
			foreach($users as $id=>$user){
				$d[$id]=array("user"=>$user->get_idname());
				$d[$id]["roles"]=$user->get_rols();	
				$d[$id]["permisions"]=$user->get_permissions();	
			}
			mw_array2list_echo($d,"usuarios");
		}
		if(!$rolesman=$uman->get_rols_man()){
			echo "<p>".$this->get_msg("No hay manejador de roles.")."</p>";	
			return false;	
		}
		mw_array2list_echo($rolesman->get_debug_data(),"roles");
		*/
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
	}
	
}
?>