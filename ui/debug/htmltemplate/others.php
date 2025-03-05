<?php
class mwmod_mw_ui_debug_htmltemplate_others extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("others","Otros"));
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		echo "<p>";
		$elem=new mwmod_mw_bootstrap_html_specialelem_btn();
		$display_modes=$elem->get_avaible_display_modes();
		foreach($display_modes as $display_mode){
			$elem->outline=false;
			$elem->set_display_mode($display_mode);
			$elem->set_cont("$display_mode");
			echo $elem->get_as_html()." ";	
			
			$elem->outline=true;
			$elem->set_display_mode($display_mode);
			$elem->set_cont("$display_mode outline");
			echo $elem->get_as_html()." ";	
			
		}
		echo "</p>";
		echo "falta modales";
		
		/*
		$alert=new mwmod_mw_bootstrap_html_specialelem_alert();
		$display_modes=$alert->get_avaible_display_modes();
		foreach($display_modes as $display_mode){
			$alert->dismissible=false;
			$alert->set_display_mode($display_mode);
			$alert->set_cont("<strong>$display_mode</strong> algún texto.");
			echo $alert->get_as_html();	
			$alert->dismissible=true;
			$alert->set_display_mode($display_mode);
			$alert->set_cont("<strong>$display_mode</strong> algún texto. dismissible ");
			echo $alert->get_as_html();	
		}
		*/
		
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