<?php
class  mwmod_mw_uitemplates_sbadmin_template_main extends mwmod_mw_ui_main_uimaintemplate{
	public $css_sidebar="sb-sidenav-dark";
	public $css_topbar="navbar-dark bg-dark";
	function __construct($ui){
		$this->set_mainap();
		$this->set_main_ui($ui);
	}
	
	function exec_full_output($mainUI){
		echo "<!DOCTYPE HTML>\n";
		echo "<html>\n";
		echo "<head>\n";
		echo $mainUI->get_page_html_head();
		echo "</head>\n";
		echo "<body id='page-top' class='sb-nav-fixed'>\n";
		$mainUI->exec_page_body();
		
		if($mainUI->jsmanager){
			echo $mainUI->jsmanager->get_bottom_items_declaration();	
		}
		
		echo "\n</body>\n";
		echo "</html>\n";
	}
	function add_default_js_scripts_for_main($mainUI,$jsmanager){
		$item=new mwmod_mw_html_manager_item_jsexternal("sbadminscripts","/res/sbadmin/js/scripts.js");
		$jsmanager->add_item_by_item($item);
		$item->bottom=true;
	}
	function add_default_css_sheets($cssmanager){
		//$item= new mwmod_mw_html_manager_item_css("bootstrap","/res/bootstrap/css/bootstrap.min.css");
		//$item= new mwmod_mw_html_manager_item_css("bootstrap","/res/sbadmin2/bower_components/bootstrap/dist/css/bootstrap.min.css");
		//$cssmanager->add_item_by_item($item);
		$cssmanager->add_item_by_item(new mwmod_mw_html_manager_item_css("glyphicon","/res/icons/glyphicons/glyphicon.css"));
		$item= new mwmod_mw_html_manager_item_css("sbadmin","/res/sbadmin/css/styles.css");
		$cssmanager->add_item_by_item($item);
		$item= new mwmod_mw_html_manager_item_css("themestyles","/res/theme/styles.css");
		$cssmanager->add_item_by_item($item);
		$item= new mwmod_mw_html_manager_item_css("fontawesome","/res/icons/fontawesome-free/css/all.min.css");
		$cssmanager->add_item_by_item($item);
		$item= new mwmod_mw_html_manager_item_css("mw","/res/css/mw.css");
		$cssmanager->add_item_by_item($item);

		$item= new mwmod_mw_html_manager_item_css("cus","/res/css/cus.css");
		$cssmanager->add_item_by_item($item);
		
		
		
		
	}
	function exec_page_full_body_sub_interface_single_mode($subinterface){
		$subinterface->do_exec_page_single_mode();
	}

	function exec_page_nav_topbar($subinterface){
		$mnu_man=$this->main_ui->mnu_man;
		$mnu=$mnu_man->get_item("topbar");
		$nav=new mwmod_mw_html_elem("nav");
		$nav->addClass("sb-topnav navbar navbar-expand {$this->css_topbar}");

		$brand=new mwmod_mw_html_cont_varcont();
		$brand->add_cont("<!-- Navbar Brand-->");
		$a=new mwmod_mw_html_elem("a");
		$a->addClass("navbar-brand ps-3");
		$a->add_cont($this->main_ui->get_ui_title_for_nav()."");
		$brand->add_cont($a);
		$nav->add_cont($brand);

		$navE=new mwmod_mw_html_cont_varcont();
		$nav->add_cont($navE);
		$navE->add_cont("<!-- Sidebar Toggle-->");
		$navBtn=new mwmod_mw_html_elem("button");
		$navE->add_cont($navBtn);
		$navBtn->addClass("btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0");
		//$navBtn->addClass("btn-sm order-1 order-lg-0 me-4 me-lg-0 sidebarToggleBtn");
		$navBtn->set_att("id","sidebarToggle");
		$navBtn->set_att("href","#!");
		$navBtn->add_cont("<i class='fas fa-bars'></i>");

		$navCentral=new mwmod_mw_html_elem("div");
		$navCentral->addClass("d-md-inline-block ms-auto");
		$nav->add_cont($navCentral);

		
		$navUL=new mwmod_mw_html_elem("ul");
		$nav->add_cont($navUL);
		$navUL->addClass("navbar-nav ms-auto ms-md-0 me-3 me-lg-4");
		$mnu->addToHtmlTopNavULSbadmin($navUL);
		echo $nav;

	}
	function addContSidenavFooter($container){
		if($user=$this->main_ui->get_admin_current_user()){
			$div=$container->add_cont_elem();
			//$div->addClass("small");
			/*
			$div1=$div->add_cont_elem();
			$div1->addClass("small");
			$div1->add_cont($this->lng_get_msg_txt("currentUser","Usuario actual").":");
			*/
			$div1=$div->add_cont_elem();
			$div1->add_cont($user->get_real_name());
			$div1=$div->add_cont_elem();
			$div1->addClass("small");
			$div1->add_cont($user->get_idname());

		}
	}
	function exec_page_nav_sidebar($subinterface){
		$mnu_man=$this->main_ui->mnu_man;
		$mnu=$mnu_man->get_item("side");
		

		$nav=new mwmod_mw_html_elem("nav");
		$nav->addClass("sb-sidenav accordion {$this->css_sidebar}");
		$nav->set_att("id","sidenavAccordion");
		$div=$nav->add_cont_elem();
		$div->addClass("sb-sidenav-menu");
		$div1=$div->add_cont_elem();
		$div1->addClass("nav");
		$mnu->addToHtmlSideNav($div1);
		$div=$nav->add_cont_elem();
		$div->addClass("sb-sidenav-footer");
		$this->addContSidenavFooter($div);


		

		echo $nav;



	}
	function exec_page_body_sub_interface_bootstrap($subinterface){
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
			echo "<div class='mw-subinterface-header-titles'>\n";
			echo "<div class='mw-subinterface-header-title'>\n";
			echo $subinterface->get_selected_ui_header_title();
			echo "</div>\n";
			if($subtitle=$subinterface->get_selected_ui_header_subtitle()){
				echo "<div class='mw-subinterface-header-subtitle'>\n";
				echo $subtitle;
				echo "</div>\n";

			}
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
			echo "<div class='mw_ui_inner_space'>\n";
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
		echo "<button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarCurrentUI' aria-controls='#navbarCurrentUI' aria-expanded='false' aria-label='Toggle navigation'><span class='navbar-toggler-icon'></span></button>";

		echo "<div class='collapse navbar-collapse' id='navbarCurrentUI'>";
		echo $mnu->get_html_as_nav_current_ui();
		echo '</div>';
		echo '</nav>';
		echo $col->get_html_close_full();
		echo $row->get_html_close_full();
		
		
	
	}

	function addContMainUIFooter($container){
		$div=$container->add_cont_elem();
		$div->addClass("d-flex align-items-center justify-content-between small");
		$div1=$div->add_cont_elem();
		$div1->addClass("text-muted");
		$div1->add_cont("Copyright © ".$this->mainap->get_cfg()->get_value("site_name")." ".date("Y"));
	}
	function exec_page_full_body_sub_interface($subinterface){
		$this->exec_page_nav_topbar($subinterface);
		echo "<div id='layoutSidenav'>\n";
			echo "<div id='layoutSidenav_nav'>\n";
				$this->exec_page_nav_sidebar($subinterface);
			echo "</div>\n";
			echo "<div id='layoutSidenav_content'>\n";
				echo "<main>\n";
					$this->exec_page_topbar_sub_interface($subinterface);
					$this->exec_page_body_sub_interface_final($subinterface);
				echo "</main>\n";
				echo "<footer class='py-4 bg-light mt-auto'>\n";
					$div=new mwmod_mw_html_elem();
					$div->addClass("container-fluid px-4");
					$this->addContMainUIFooter($div);
					echo $div;
				echo "</footer>\n";
			echo "</div>\n";

		echo "</div>\n";
		$this->exec_page_body_admin_bot();
	}
	//////////hasta acá
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
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
	
	function exec_page_topbar_sub_interface($subinterface){
		return;
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
	

	
	function exec_page_body_admin_bot(){
		//
	}

	
	

	
	


	
}
?>