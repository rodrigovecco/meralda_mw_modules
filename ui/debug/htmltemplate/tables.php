<?php
class mwmod_mw_ui_debug_htmltemplate_tables extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("tables","Tablas"));
		
	}
	
	function do_exec_no_sub_interface(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		/*
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_globalize();
		*/
		
	}
	function get_test_data(){
		$nombres=array("Rodrigo","Hernán","Jorge","Pedro");	
		$ciudades=array("Lima","La Habana","Santiago","Arica");	
		$fechas=array(date("Y-m-d H:i",time()),"2014-02-25 3:15");
		$r=array();
		$id=0;
		foreach($nombres as $nom){
			reset($ciudades);
			foreach($ciudades as $ciudad){
				reset($fechas);	
				foreach($fechas as $fecha){
					$id++;
					$d=array(
						"id"=>$id,
						"nom"=>$nom,
						"ciudad"=>$ciudad,
						"fecha"=>$fecha,
						"ok"=>false,
					);
					$r[$id]=$d;
					$id++;
					$d=array(
						"id"=>$id,
						"nom"=>$nom,
						"ciudad"=>$ciudad,
						"fecha"=>$fecha,
						"ok"=>true,
					);
					$r[$id]=$d;
				}
			}
		}
		return $r;
		
	}
	function do_exec_page_in(){
		$dd=$this->get_test_data();
		$datagrid=new mwmod_mw_devextreme_widget_datagrid("datagridtest");
		$datagrid->setFilerVisible();
		$col=$datagrid->add_column_number("id","ID");
		$col=$datagrid->add_column_string("nom","ID");
		$col=$datagrid->add_column_string("ciudad","Ciudad");
		$col=$datagrid->add_column_date("fecha","Fecha");
		$col=$datagrid->add_column_boolean("ok","OK");
		$datagrid->add_data_from_list($dd);
		echo $datagrid->get_html_container();
		$js=$datagrid->new_js_doc_ready();
		echo $js->get_js_script_html();
		//echo nl2br($js->get_as_js_val());
		
		/*
		
		$tits=array("id"=>"#","nom"=>"nombre","ciudad"=>"ciudad","fecha"=>"fecha","ok"=>"ok");

		$tbl=new mwmod_mw_bootstrap_ui_template_tbl();
		echo $tbl->get_tbl_open_header_and_set_cols_cods($tits);
		reset($dd);
		foreach ($dd as $id=>$d){
			echo $tbl->get_row_ordered($d);	
		}
		echo $tbl->get_tbl_close();
		*/
		/*
		
		$tbl=$this->new_tbl_template();
		echo "<p>".get_class($tbl);
		echo $tbl->get_tbl_open_header_and_set_cols_cods($tits);
		reset($dd);
		foreach ($dd as $id=>$d){
			echo $tbl->get_row_ordered($d);	
		}
		echo $tbl->get_tbl_close();
		echo "</p>";
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