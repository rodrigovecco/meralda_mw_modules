<?php
class mwmod_mw_facebook_ui_cfg extends mwmod_mw_ui_sub_uiabs{
	public $sucods="cfg,keys,test";
	public $fbMan;
	function __construct($cod,$parent,$fbMan){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title("Facebook");
		$this->fbMan=$fbMan;
		
		
		
	}
	
	function is_allowed(){
		return $this->allow("admin");	
	}
	function _do_create_subinterface_child_test(){
		$si=new mwmod_mw_facebook_ui_cfg_test("test",$this);
		return $si;
	}
	function _do_create_subinterface_child_cfg(){
		$si=new mwmod_mw_facebook_ui_cfg_cfg("cfg",$this);
		return $si;
	}
	
	
	function _do_create_subinterface_child_keys(){
		$si=new mwmod_mw_facebook_ui_cfg_keys("keys",$this);
		return $si;
	}
	function allowcreatesubinterfacechildbycode(){
		return true;	
	}
	
	
	function do_exec_page_in(){
		///////
		$container=$this->get_ui_dom_elem_container();
		
		$container->add_cont("Facebook");
		echo $container->get_as_html();
		

		
	}
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			foreach($subs as $su1){
				$su1->add_2_sub_interface_mnu($mnu);	
			}
		}
		
		
		return $mnu;
	}
	
	function getFBMan(){
		return $this->fbMan;	
	}
	
}
?>