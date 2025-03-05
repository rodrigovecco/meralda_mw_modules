<?php
class mwmod_mw_ui_debug_htmltemplate_alerts extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("alerts","Alertas"));
		
	}
	function prepare_js_and_css_mans(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();

		
		$jsman=$util->get_js_man();
		/*
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("mw_events.js");
		$jsman->add_item_by_cod_def_path("mw_objcol_adv.js");
		$jsman->add_item_by_cod_def_path("mw_nav_bar.js");
		$jsman->add_item_by_cod_def_path("inputs/date.js");
		$jsman->add_item_by_cod_def_path("inputs/dxnormal.js");
		$jsman->add_item_by_cod_def_path("inputs/experimental.js");
		//$jsman->add_item_by_cod_def_path("inputs/container.js");
		*/
		
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		
		
		
			
	}
	
	function do_exec_no_sub_interface(){
		$this->prepare_js_and_css_mans();
	}
	function do_exec_page_in(){
		//$msg="<strong></strong>";
		$alert=new mwmod_mw_bootstrap_html_specialelem_alert();
		$display_modes=$alert->get_avaible_display_modes();
		$js=$alert->getJsNotify();
		$e=new mwmod_mw_html_elem();
		$onclick=$e->setEventAtt();
		$onclick->getJs()->add_cont("mw_ui_helper_notify(");
		$onclick->getJs()->add_cont($js);
		$onclick->getJs()->add_cont(")");
		
		//$e->setAtts('onclick="mw_ui_helper_notify('.$js->get_as_js_val().')"');
		$e->add_cont("Click para ver en modo notificación");
		$alertContainer=new mwmod_mw_html_elem();
		$alertContainer->add_cont($alert);
		$alertContainer->add_cont($e);
		$alertContainer->add_cont("<hr>");

		
		foreach($display_modes as $display_mode){
			$alert->dismissible=false;
			$alert->setMsg("<strong>$display_mode</strong> algún texto.",$display_mode);
			
			//$alert->set_display_mode($display_mode);
			//$alert->set_cont();
			//echo $alert->get_as_html();	
			echo $alertContainer->get_as_html();	
			$alert->dismissible=true;
			//$alert->set_display_mode($display_mode);
			//$alert->set_cont("<strong>$display_mode</strong> algún texto. dismissible ");
			$alert->setMsg("<strong>$display_mode</strong> algún texto. dismissible ",$display_mode);
			//echo $alert->get_as_html();	
			echo $alertContainer->get_as_html();	
		}
		$alert->setMsg();
		echo "Alerta vacía";
		echo $alertContainer->get_as_html();	
		
		/*
		
		$html_template=new mwmod_mw_bootstrap_html_template_panel();
		$display_modes=$html_template->main_elem->get_avaible_display_modes();
		$title_container=$html_template->get_key_cont("title");
		if($footer=$html_template->get_key_cont("footer")){
			$footer->set_cont($this->lng_get_msg_txt("footer","Pie"));		
		}
		
		
		foreach($display_modes as $display_mode){
			if($title_container){
				$title_container->set_cont($this->lng_get_msg_txt("panel","Panel")." ".$display_mode);	
			}
			
			$html_template->set_display_mode($display_mode);
			echo $html_template->get_as_html();	
		}
		echo "<div id='panels_gr'>";
		$html_template=new mwmod_mw_bootstrap_html_template_panelcollapse("panels_gr","panelscol1");
		$display_modes=$html_template->main_elem->get_avaible_display_modes();
		$title_container=$html_template->get_key_cont("title");
		if($title_container){
			$title_container->set_cont($this->lng_get_msg_txt("collapse","Colapsar"));	
		}
		
		if($footer=$html_template->get_key_cont("footer")){
			$footer->set_cont($this->lng_get_msg_txt("footer","Pie"));		
		}
		echo $html_template->get_as_html();	
		
		echo "</div>";
		*/
		
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
	}
	
}
?>