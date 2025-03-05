<?php
class mwmod_mw_ui_debug_permissions extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Permisos");
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		if(!$this->is_allowed()){
			return false;	
		}
		if(!$uman=$this->mainap->get_user_manager()){
			echo "<p>".$this->get_msg("No hay manejador de usuarios.")."</p>";	
			return false;	
		}
		
		$registerenabled=false;
		if($this->mainap->cfg){
			if($this->mainap->cfg->get_value_boolean("register_permissions_requests")){
				$registerenabled=true;
			}
			
		}
		if($registerenabled){
			echo "<p>Registro de solicitudes de permisos activo</p>";	
		}else{
			echo "<p style='color:#F00'>Registro de solicitudes de permisos inactivo</p>";	
		}
		$url=$this->get_url(array("clear_log"=>"true"));
		echo "<p><a href='$url'>Vaciar registro</a></p>";	
		
		$solicitados=array();
		if($ado=$uman->get_treedata_item("permissionsrequest","logs")){
			if($_REQUEST["clear_log"]=="true"){
				$ado->set_data(array());
				$ado->save();	
			}
			$solicitados=$ado->get_data_as_list("requested_dates",false);
			mw_array2list_echo($ado->get_data(),"LOG");
		}
		
		if($permissions_man=$uman->get_permission_man()){
			$sinmanejador=array();
			foreach($solicitados as $cod=>$date){
				if(!$permissions_man->get_item($cod)){
					$sinmanejador[]=$cod;	
				}
			}
			mw_array2list_echo($sinmanejador,"Sin manejador");
			/*
			if($permissions=$permissions_man->get_items()){
						
			}
			*/
		}
		
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>