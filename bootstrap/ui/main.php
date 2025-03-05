<?php
abstract class mwmod_mw_bootstrap_ui_main extends mwmod_mw_ui_main_uimainabsajax{
	function create_template(){
		$t=new mwmod_mw_bootstrap_ui_template_main($this);
		return $t;
	}
	
	function get_page_html_sidebar_top(){
		$c=new mwmod_mw_html_elem("a");
		$c->set_att("href","index.php");
		$c->addClass("sidebar-brand d-flex align-items-center justify-content-center");
		$e=$c->add_cont_elem();
		$e->addClass("sidebar-brand-text mx-3");
	 	$e->add_cont($this->get_ui_title_for_nav()."");
		return $c->get_as_html();
			
	}
	
	
	function get_page_html_head(){
		$html='<meta charset="utf-8">'."\n";	
		$html.='<title>'.$this->get_page_title().'</title>'."\n";
		$html.="<meta http-equiv='X-UA-Compatible' content='IE=edge'/>\n";
		$html.="<meta name='viewport' content='width=device-width, initial-scale=1'/>\n";
		
		$html.=$this->get_page_html_style();
		$html.=$this->get_page_html_head_js();
		return $html;
	}
	
	function get_subinterface_not_allowed(){
		$si= new mwmod_mw_bootstrap_ui_sub_uinotallowed("notallowed",$this);
		return $si;
	}
	
	function get_subinterface_not_allowed_no_user(){
		$si= new mwmod_mw_bootstrap_ui_sub_uilogin("login",$this);
		return $si;
	}
	function add_mnu_items_side($mnu){
		/*
		if($user=$this->get_admin_current_user()){
			
			$mnuitem= new mwmod_mw_mnu_items_dropdown("user",$this->get_msg("Usuario"),$mnu);
			$mnuitem->add_param("class","dropdown-user");
			$mnu->add_item_by_item($mnuitem);
			$sub=$mnuitem->add_new_item("logout",$this->get_msg("Cerrar sesión"),"index.php?logout=true");	
			$sub->addInnerHTML_icon("fa fa-sign-out fa-fw");
		}
		*/
		
	}
	
	function add_default_js_scripts_sub($jsmanager){
		
		
		$jsmanager->add_jquery();
		
		$item=new mwmod_mw_html_manager_item_jsexternal("bootstrap","/res/bootstrap/js/bootstrap.min.js");
		$jsmanager->add_item_by_item($item);
		$jsmanager->add_item_by_cod_def_path("mwbootstrap.js");
		$item->bottom=true;
		//$item=new mwmod_mw_html_manager_item_jsexternal("fontawesome","/res/sbadmin2/vendor/fontawesome-free/css/all.min.css");
		//$jsmanager->add_item_by_item($item);
		//$item->bottom=true;

		//$item=new mwmod_mw_html_manager_item_jsexternal("metismenu","/res/sbadmin2/js/plugins/metisMenu/metisMenu.min.js");
		//$jsmanager->add_item_by_item($item);
		//$item->bottom=true;
		$item=new mwmod_mw_html_manager_item_jsexternal("sbadmin2","/res/sbadmin2/js/sb-admin-2.js");
		$jsmanager->add_item_by_item($item);
		$item->bottom=true;
		$item=new mwmod_mw_html_manager_item_jsexternal("jquery-easing","/res/sbadmin2/vendor/jquery-easing/jquery.easing.min.js");
		$jsmanager->add_item_by_item($item);
		$item->bottom=true;
		
		
		$item=new mwmod_mw_bootstrap_helper_jsie();
		$jsmanager->add_item_by_item($item);
		

	}
	
	function mnu_man_on_create($mnu_man){
		
		
		$mnu=$mnu_man->get_item("side");
		$mnu->allow_sub_menus=true;
		$this->add_mnu_items_side($mnu);	
		
		$mnu=$mnu_man->get_item("mnu");
		$this->add_mnu_items($mnu);	
		$mnu=$mnu_man->get_item("lat");
		$this->add_lat_mnu_items($mnu);	
		$mnu=$mnu_man->get_item("topbar");
		$this->add_mnu_items_topbar($mnu);	
	}
	function add_mnu_items_topbar($mnu){
		if($user=$this->get_admin_current_user()){
			
			//$mnuitem= new mwmod_mw_mnu_items_dropdown("user",$this->get_msg("Usuario"),$mnu);
			//$mnuitem= new mwmod_mw_mnu_items_dropdown_top("user",$this->get_msg("Usuario"),$mnu);
			$mnuitem= new mwmod_mw_mnu_items_dropdown_top("user",$user->get_real_name(),$mnu);
			$mnuitem->add_param("icon_class","fa fa-user fa-fw");
			$mnuitem->add_param("no-arrow",true);
			$mnu->add_item_by_item($mnuitem);
			if($myacc=$this->get_subinterface("myaccount")){
				$item=new mwmod_mw_mnu_mnuitem("myaccount",$this->lng_common_get_msg_txt("my_account","Mi cuenta"),$mnuitem,$myacc->get_url());
				$item->addInnerHTML_icon("fa fa-user fa-fw");
				$mnuitem->add_item_by_item($item);
			}

			
			
			$sub=$mnuitem->add_new_item("logout",$this->get_msg("Cerrar sesión"),$this->get_logout_url());	
			$sub->addInnerHTML_icon("fas fa-sign-out-alt mnuicon",true);
			//$sub->addInnerHTML_icon("fa fa-sign-out fa-fw");
		}
		
	}

	
}
?>