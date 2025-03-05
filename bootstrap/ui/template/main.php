<?php
class  mwmod_mw_bootstrap_ui_template_main extends mwmod_mw_ui_main_uimaintemplate{
	function __construct($ui){
		$this->set_mainap();
		$this->set_main_ui($ui);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function new_sub_interface_template($si){
		$t=new mwmod_mw_bootstrap_ui_template_subtemplate($si);
		$t->htmlclasspref="sys_inteface_sub";
		return $t;
	}
	
	function exec_page_body_sub_interface_final($subinterface){
		if(!$subinterface){
			return false;	
		}
		if($last=$subinterface->get_this_or_final_current_subinterface()){
			return $this->exec_page_body_sub_interface($last);	
		}
	}
	function exec_page_body_sub_interface_bootstrap($subinterface){
		echo "<!-- Begin Page Content -->\n";
		echo "<div class='container-fluid'>\n";
		$row=new mwmod_mw_bootstrap_html_grid_row();
		echo $row->get_html_open_full();
		$col=new mwmod_mw_bootstrap_html_grid_col();
		echo $col->get_html_open_full();
		if(!$subinterface->omit_header()){
			echo "<div class='mw-subinterface-header'>\n";
			if($html=$subinterface->get_html_parents_route()){
				echo "<div class='mw-subinterface-header-route' >\n";
				echo $html;
				echo "</div>\n";
					
			}
			echo $subinterface->get_selected_ui_header_title();
			echo "</div>\n";
		}
		echo $col->get_html_close_full();
		echo $row->get_html_close_full();
		$this->exec_sub_interface_mnu($subinterface);
		
		$row=new mwmod_mw_bootstrap_html_grid_row();
		echo $row->get_html_open_full();
		$col=new mwmod_mw_bootstrap_html_grid_col();
		echo $col->get_html_open_full();
		$gencontclose="";
		if(!$subinterface->omitUIGeneralContainer()){
			echo "<div class='card'>\n";
			$gencontclose="</div>";
		}

		
		
		$subinterface->do_exec_on_page_in_on_maintemplate($this);
		echo $gencontclose;
		echo $col->get_html_close_full();
		echo $row->get_html_close_full();
		echo "</div>";
			
	}
	function exec_sub_interface_mnu($subinterface){
		
		if(!$mnu=$subinterface->get_sub_interface_mnu_from_parent_responsable()){
			return false;	
		}
		if(!$mnu->can_display()){
			return false;	
		}
		//mw_array2list_echo($mnu->get_debug_data());
		$row=new mwmod_mw_bootstrap_html_grid_row();
		echo $row->get_html_open_full();
		$col=new mwmod_mw_bootstrap_html_grid_col(12);
		echo $col->get_html_open_full();

		echo "<nav class='currentUINavBar navbar navbar-expand-lg navbar-light bg-light' >\n";
		echo "<button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarCurrentUI' aria-controls='navbarCurrentUI' aria-expanded='false' aria-label='Toggle navigation'><span class='navbar-toggler-icon'></span></button>";
		echo "<div class='collapse navbar-collapse' id='navbarCurrentUI'>";
		echo $mnu->get_html_as_nav_current_ui();
		echo '</div>';
		echo '</nav>';
		echo $col->get_html_close_full();
		echo $row->get_html_close_full();
		
		
	
	}
	
	
	function exec_page_body_sub_interface($subinterface){
		
		if(!$subinterface){
			return false;	
		}
		if(method_exists($subinterface,"exec_page_body_sub_interface_on_main_template_bootstrap")){
			if($subinterface->can_page_body_sub_interface_on_main_template_bootstrap()){
				return 	$subinterface->exec_page_body_sub_interface_on_main_template_bootstrap($this);
					
			}
		}
		$this->exec_page_body_sub_interface_normal_mode($subinterface);
	}
	function exec_page_body_sub_interface_normal_mode($subinterface){
		if(!$subinterface){
			return false;	
		}
		if(!$template=$subinterface->get_template($this)){
			return false;	
		}
		//echo get_class($template);
		$template->exec_page_full_body_sub_interface();
		
	}
	

	
	
	function exec_page_nav_top_links($subinterface){
		$mnu_man=$this->main_ui->mnu_man;
		$mnu=$mnu_man->get_item("toplinks");
		echo $mnu->get_html_as_nav("nav navbar-top-links navbar-right");
		echo "<!-- /.navbar-top-links -->\n";
	}
	function exec_page_nav_sidebar($subinterface){
		$mnu_man=$this->main_ui->mnu_man;
		$mnu=$mnu_man->get_item("side");
		echo "<!-- Sidebar -->\n";
		echo "<ul class='navbar-nav bg-gradient-primary sidebar sidebar-dark accordion' id='accordionSidebar'>\n";
		echo $this->main_ui->get_page_html_sidebar_top();
		echo "<hr class='sidebar-divider my-0'>\n";
		echo $mnu->get_html_as_navlist_inner();
		echo "<hr class='sidebar-divider d-none d-md-block'>\n";
		echo "<div class='text-center d-none d-md-inline'>\n";
        echo "<button class='rounded-circle border-0' id='sidebarToggle'></button>\n</div>\n";
		echo "</ul>\n";
		echo "<!-- End of Sidebar -->\n";

	}
	
	function exec_page_nav($subinterface){
		//this mnu comes alway from main interface
		//echo '<!-- Sidebar -->'."\n";
		$this->exec_page_nav_sidebar($subinterface);
		
		//echo "<nav id='Navigation' class='navbar navbar-default navbar-static-top' role='navigation' style='width:100%; margin:0px; margin-bottom: 0px'>\n";
		//$this->exec_page_nav_header($subinterface);
		//$this->exec_page_nav_top_links($subinterface);

		//echo "</nav>";


	
	}
	function get_html_fullScrren_btn(){
		//	
		//$var=$this->main_ui->get_js_ui_man_name();
		$html.= "<div class='toggleFullScreenBtnContainer' id='toggleFullScreenBtnContainer'>\n";
		//$html.= "<div   class='toggleFullScreenBtn collapse' aria-expanded='false' id='toggleFullScreenBtn'>\n";
		$html.= "<div   class='toggleFullScreenBtn' id='toggleFullScreenBtn'>\n";
		$html.= "<span class='fa fa-navicon'>&nbsp;</span>\n";
		$html.= "</div>\n";
		$html.= "</div>\n";
		$html.= "<div class='toggleFullScreenBtnBar' style='display:none' id='toggleFullScreenBtnBar'></div>\n";
		
		
		return $html;
		
	}
	function exec_page_nav_header($subinterface){
		/*
		echo "<div class='navbar-header' id='NavbarHeader'>\n";
		echo $this->get_html_fullScrren_btn();
		echo "<button type='button'  class='navbar-toggle' data-toggle='collapse' data-target='.navbar-collapse'>\n";
		echo "<span class='sr-only'>Toggle navigation</span>\n";
		echo "<span class='icon-bar'></span>\n";
		echo "<span class='icon-bar'></span>\n";
		echo "<span class='icon-bar'></span>\n";
		echo "</button>\n";
		echo "<a class='navbar-brand' href='index.php'>";
		echo $this->main_ui->get_ui_title_for_nav();
		echo "</a>";
		echo "</div>";
		echo "<!-- /.navbar-header -->\n";
		*/	
	}
	function exec_page_topbar_sub_interface($subinterface){
		//obsolto con bt4
		//ver si su lo permite
		echo "<!-- Topbar -->\n";
		echo "<nav class='navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow'>\n";
		echo "<!-- Sidebar Toggle (Topbar) -->\n";
		//echo "<form class='form-inline'>\n";
		echo "<button id='sidebarToggleTop' class='btn btn-link d-md-none rounded-circle mr-3'>\n";
        echo "<i class='fa fa-bars'></i>\n";
		echo "</button>";
		//echo "</form>\n";
		
		$this->exec_page_nav_topbar($subinterface);
		echo  "</nav>";
		echo "<!-- End of Topbar -->\n";
	}
	function exec_page_nav_topbar($subinterface){
		$mnu_man=$this->main_ui->mnu_man;
		$mnu=$mnu_man->get_item("topbar");
		echo "<!-- Topbar Navbar -->\n";
		echo "<ul class='navbar-nav ml-auto mw_mnu_top'>\n";
		echo $mnu->get_html_as_topbar_inner();
		echo "</ul>\n";
		

	}

	function exec_page_full_body_sub_interface($subinterface){
		
		echo "<div id='wrapper'>\n";
		$this->exec_page_nav($subinterface);
		echo "<!-- Content Wrapper -->";
		echo "<div id='content-wrapper' class='d-flex flex-column'>\n";
		echo "<!-- Main Content -->";
		echo "<div id='content'>\n";
		$this->exec_page_topbar_sub_interface($subinterface);
		$this->exec_page_body_sub_interface_final($subinterface);
		echo "</div>";
		echo "</div>";
		$this->exec_page_body_admin_bot();
		echo "</div>";
		echo "<a class='scroll-to-top rounded' href='#page-top'>\n<i class='fas fa-angle-up'></i></a>";
	
	}
	function exec_page_body_admin_bot(){
		//
	}

	
	function exec_page_full_body_sub_interface_single_mode($subinterface){
		$subinterface->do_exec_page_single_mode();
	}

	function add_default_css_sheets($cssmanager){
		$item= new mwmod_mw_html_manager_item_css("bootstrap","/res/bootstrap/css/bootstrap.min.css");
		//$item= new mwmod_mw_html_manager_item_css("bootstrap","/res/sbadmin2/bower_components/bootstrap/dist/css/bootstrap.min.css");
		$cssmanager->add_item_by_item($item);
		$item= new mwmod_mw_html_manager_item_css("glyphicon","/res/bootstrap/css/glyphicon.css");
		$cssmanager->add_item_by_item($item);
		//$item= new mwmod_mw_html_manager_item_css("bootstraptheme","/res/bootstrap/css/bootstrap-theme.min.css");
		//$item= new mwmod_mw_html_manager_item_css("bootstraptheme","/res/sbadmin2/bower_components/bootstrap/dist/css/bootstrap-theme.min.css");
		//$cssmanager->add_item_by_item($item);
		//$item= new mwmod_mw_html_manager_item_css("metisMenu","/res/sbadmin2/css/plugins/metisMenu/metisMenu.min.css");
		//$cssmanager->add_item_by_item($item);
		$item= new mwmod_mw_html_manager_item_css("sbadmin2","/res/sbadmin2/css/sb-admin-2.css");
		$cssmanager->add_item_by_item($item);
		$item= new mwmod_mw_html_manager_item_css("fontawesome","/res/sbadmin2/vendor/fontawesome-free/css/all.min.css");
		$cssmanager->add_item_by_item($item);
		$item= new mwmod_mw_html_manager_item_css("mw","/res/css/mw.css");
		$cssmanager->add_item_by_item($item);
		$item= new mwmod_mw_html_manager_item_css("mwdxbootstrap","/res/css/mwdxbootstrap.css");
		$cssmanager->add_item_by_item($item);
		$item= new mwmod_mw_html_manager_item_css("mwbootstrap","/res/css/mwbootstrap.css");
		$cssmanager->add_item_by_item($item);
		$item= new mwmod_mw_html_manager_item_css("mwsbadmin2","/res/css/mw-sb-admin-2.css");
		$cssmanager->add_item_by_item($item);
		
		
		
	}
	


	
}
?>