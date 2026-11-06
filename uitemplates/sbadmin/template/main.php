<?php
class  mwmod_mw_uitemplates_sbadmin_template_main extends mwmod_mw_ui_main_uimaintemplate{
	public $css_sidebar="sb-sidenav-dark";
	public $css_topbar="navbar-dark bg-dark";
	/** Renders the "collapse UI header" button in the topbar. */
	public $uiHeaderToggleEnabled=true;
	/** Cache of the collapsible state, keyed by subinterface id. */
	private $___ui_header_collapsible_cache=array();
	/** Cache of the sub-interface menu, keyed by subinterface id. */
	private $___sub_interface_mnu_cache=array();
	private $ui_header_toggle_assets_done=false;
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

		$item= new mwmod_mw_html_manager_item_css("meraldaicons","/res/css/meralda_icons.css");
		$cssmanager->add_item_by_item($item);
		
		
		
		
		
	}
	function exec_page_full_body_sub_interface_single_mode($subinterface){
		$subinterface->do_exec_page_single_mode();
	}

	function add_nav_toggle($nav){
		$navE=new mwmod_mw_html_cont_varcont();
		$nav->add_cont($navE);
		$navE->add_cont("<!-- Sidebar Toggle-->");
		$navBtn=new mwmod_mw_html_elem("button");
		$navE->add_cont($navBtn);
		// No order-1 on small screens: with order-1 the flex layout pushes this
		// button after the brand and the user menu, which on a 390px phone places
		// it at x=365 with 43px of width, i.e. outside the viewport. Left of the
		// brand is also where the SB Admin template puts it on desktop.
		$navBtn->addClass("btn btn-link btn-sm mw-topnav-sidebar-toggle me-2 me-lg-0");
		$navBtn->set_att("type","button");
		$navBtn->set_att("id","sidebarToggle");
		$navBtn->set_att("href","#!");
		$navBtn->add_cont("<i class='fas fa-bars'></i>");
	}

	/**
	 * Returns the sub-interface menu for a subinterface, building it only once.
	 *
	 * get_sub_interface_mnu_from_parent_responsable() hands back a brand new menu
	 * object on every call (the responsible parents just instantiate one and fill
	 * it), and this template needs the answer three times: to decide whether the
	 * header is collapsible, to render the inline toggle and to render the menu.
	 *
	 * @param mwmod_mw_ui_sub_uiabs $subinterface
	 * @return mwmod_mw_mnu_mnu|false The menu, or false when there is none.
	 */
	function get_sub_interface_mnu_for($subinterface){
		if(!$subinterface){
			return false;
		}
		$key=spl_object_id($subinterface);
		if(array_key_exists($key,$this->___sub_interface_mnu_cache)){
			return $this->___sub_interface_mnu_cache[$key];
		}
		$mnu=false;
		if(method_exists($subinterface,"get_sub_interface_mnu_from_parent_responsable")){
			$mnu=$subinterface->get_sub_interface_mnu_from_parent_responsable();
		}
		if($mnu){
			if(!$mnu->can_display()){
				$mnu=false;
			}
		}
		$this->___sub_interface_mnu_cache[$key]=$mnu;
		return $mnu;
	}

	/**
	 * Whether the given subinterface has a header/sub-menu that can be collapsed.
	 *
	 * @param mwmod_mw_ui_sub_uiabs $subinterface
	 * @return bool
	 */
	function is_ui_header_collapsible_for($subinterface){
		if(!$this->uiHeaderToggleEnabled){
			return false;
		}
		if(!$subinterface){
			return false;
		}
		$key=spl_object_id($subinterface);
		if(array_key_exists($key,$this->___ui_header_collapsible_cache)){
			return $this->___ui_header_collapsible_cache[$key];
		}
		$r=false;
		if(method_exists($subinterface,"is_ui_header_collapsible")){
			if(!$subinterface->is_ui_header_collapsible()){
				$this->___ui_header_collapsible_cache[$key]=false;
				return false;
			}
		}
		if(!$subinterface->omit_header()){
			$r=true;
		}elseif($this->get_sub_interface_mnu_for($subinterface)){
			$r=true;
		}
		$this->___ui_header_collapsible_cache[$key]=$r;
		return $r;
	}

	/**
	 * Renders the "collapse header" button, left of the sidebar toggler.
	 *
	 * @param mwmod_mw_html_elem      $nav          Topbar nav element.
	 * @param mwmod_mw_ui_sub_uiabs   $subinterface Current subinterface.
	 * @return bool
	 */
	function add_ui_header_toggle($nav,$subinterface){
		if(!$this->is_ui_header_collapsible_for($subinterface)){
			return false;
		}
		$collapsed=$this->is_ui_header_collapsed_by_default_for($subinterface);
		$btn=new mwmod_mw_html_elem("button");
		$btn->addClass("btn btn-link btn-sm mw-ui-header-toggle");
		$btn->set_att("type","button");
		$btn->set_att("id","mwUIHeaderToggle");
		$btn->set_att("aria-controls","mwUIHeaderZone");
		$btn->set_att("aria-expanded",$collapsed ? "false" : "true");
		$btn->set_att("data-mw-title-collapse",$this->lng_get_msg_txt("hideUIHeader","Ocultar encabezado"));
		$btn->set_att("data-mw-title-expand",$this->lng_get_msg_txt("showUIHeader","Mostrar encabezado"));
		$btn->set_att("title",$collapsed ? $this->lng_get_msg_txt("showUIHeader","Mostrar encabezado") : $this->lng_get_msg_txt("hideUIHeader","Ocultar encabezado"));
		$btn->add_cont("<i class='fas fa-chevron-".($collapsed ? "down" : "up")."'></i>");
		$nav->add_cont($btn);
		return true;
	}

	/**
	 * Whether the subinterface starts collapsed (server-side default only).
	 *
	 * @param mwmod_mw_ui_sub_uiabs $subinterface
	 * @return bool
	 */
	function is_ui_header_collapsed_by_default_for($subinterface){
		if(!$subinterface){
			return false;
		}
		if(method_exists($subinterface,"is_ui_header_collapsed_by_default")){
			return (bool)$subinterface->is_ui_header_collapsed_by_default();
		}
		return false;
	}

	/**
	 * Renders the sub-menu toggle.
	 *
	 * On narrow screens the sub-menu (the tabs bar) is collapsed, and until now
	 * the template put a second hamburger on a line of its own right below the
	 * title. Two identical hamburgers next to each other are confusing, so this
	 * button uses a list icon instead and sits on the title line, at the right.
	 *
	 * The button is delegated to Bootstrap's collapse, which keeps working after
	 * Ajax navigation because it listens on the document.
	 *
	 * @param mwmod_mw_ui_sub_uiabs $subinterface
	 * @param bool                  $inline Whether it goes inside the header row.
	 * @return string Empty when there is no sub-menu to toggle.
	 */
	function get_sub_interface_mnu_toggle_html($subinterface,$inline=true){
		if(!$this->get_sub_interface_mnu_for($subinterface)){
			return "";
		}
		$lbl=$this->lng_get_msg_txt("subMenu","Submenú");
		$cls="mw-subinterface-mnu-toggle".($inline ? " mw-subinterface-mnu-toggle-inline" : " mw-subinterface-mnu-toggle-block");
		$r="<button class='".$cls."' type='button' data-bs-toggle='collapse' data-bs-target='#navbarCurrentUI' aria-controls='navbarCurrentUI' aria-expanded='false' aria-label='".htmlspecialchars($lbl,ENT_QUOTES)."' title='".htmlspecialchars($lbl,ENT_QUOTES)."'>";
		$r.="<i class='fas fa-list-ul'></i>";
		$r.="</button>";
		return $r;
	}

	/**
	 * Opening tag of the wrapper that holds the breadcrumb, the title and the
	 * subinterface menu, so they can be collapsed together.
	 *
	 * @param mwmod_mw_ui_sub_uiabs $subinterface
	 * @return string Empty when the zone must not be created.
	 */
	function get_ui_header_zone_open_html($subinterface){
		if(!$this->is_ui_header_collapsible_for($subinterface)){
			return "";
		}
		$collapsed=$this->is_ui_header_collapsed_by_default_for($subinterface) ? "1" : "0";
		$key=htmlspecialchars($subinterface->get_full_cod("-"),ENT_QUOTES);
		return "<div id='mwUIHeaderZone' class='mw-ui-header-zone' data-mw-ui-collapsed='".$collapsed."' data-mw-ui-key='".$key."'>\n";
	}

	/**
	 * Emits the styles and the toggle script once per page.
	 *
	 * Kept inside the template (not in res/css, res/js) so the feature lives in a
	 * single submodule and cannot break a page that loads an older asset bundle.
	 *
	 * @return void
	 */
	function output_ui_header_toggle_assets(){
		if($this->ui_header_toggle_assets_done){
			return;
		}
		$this->ui_header_toggle_assets_done=true;
		echo "<style>\n";
		echo ".mw-ui-header-zone.mw-ui-header-collapsed{display:none}\n";
		echo ".mw-ui-header-toggle{color:inherit;opacity:.8;text-decoration:none}\n";
		echo ".mw-ui-header-toggle:hover{opacity:1}\n";
		/* Both topbar buttons stay at the left, in DOM order: header toggle then
		   sidebar toggle. sbadmin's .order-1 used to push the sidebar one off the
		   right edge of a phone screen. Margins and padding are zeroed so the two
		   icons sit side by side instead of drifting apart. The selector uses the
		   ids on purpose: ui2's layout.css sets `.sb-topnav #sidebarToggle`
		   padding with an id, which beats a class-based rule. */
		echo ".sb-topnav #mwUIHeaderToggle,.sb-topnav #sidebarToggle{order:0;margin:0;padding:.25rem .35rem;line-height:1;background:transparent;border:0}\n";
		echo "@media (max-width:991.98px){\n";
		echo "  .sb-topnav #mwUIHeaderToggle,.sb-topnav #sidebarToggle{padding:.2rem .25rem}\n";
		echo "}\n";
		/* Sub-menu toggle: list icon, on the title line, only while collapsed. */
		echo ".mw-subinterface-mnu-toggle{display:none}\n";
		echo "@media (max-width:991.98px){\n";
		/* The brand used to hold a fixed width, so on a phone it pushed the user
		   menu off the right edge. Let it shrink and ellipsize instead. */
		echo "  .sb-topnav .navbar-brand{display:block;width:auto;min-width:0;flex:1 1 auto;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;padding-left:.5rem;margin-right:0}\n";
		echo "  .sb-topnav .navbar-nav{flex:0 0 auto;flex-wrap:nowrap}\n";
		echo "  .mw-subinterface-mnu-toggle{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;padding:0;border:1px solid #ced4da;border-radius:6px;background:#fff;color:#495057;font-size:14px;line-height:1;cursor:pointer}\n";
		echo "  .mw-subinterface-mnu-toggle:hover{background:#e9ecef;color:#0d6efd}\n";
		echo "  .mw-subinterface-mnu-toggle-inline{position:absolute;top:0;right:0}\n";
		echo "  .mw-subinterface-mnu-toggle-block{float:right;margin:0 0 6px 6px}\n";
		echo "  .mw-subinterface-header-with-mnutoggle{position:relative;padding-right:40px}\n";
		echo "}\n";
		echo "</style>\n";
		echo "<script>\n";
		echo "(function(){\n";
		echo "var KEY='mwUIHeaderCollapsed:'+(window.mwUIHeaderKeyPrefix||'');\n";
		echo "function ready(fn){if(document.readyState!=='loading'){fn();}else{document.addEventListener('DOMContentLoaded',fn);}}\n";
		echo "ready(function(){\n";
		echo "var btn=document.getElementById('mwUIHeaderToggle');\n";
		echo "var zone=document.getElementById('mwUIHeaderZone');\n";
		echo "if(!btn||!zone){return;}\n";
		echo "var key=KEY+(zone.getAttribute('data-mw-ui-key')||'');\n";
		echo "var collapsed=(zone.getAttribute('data-mw-ui-collapsed')==='1');\n";
		echo "try{var st=window.localStorage.getItem(key);if(st==='1'){collapsed=true;}else if(st==='0'){collapsed=false;}}catch(e){}\n";
		echo "function apply(c,store){\n";
		echo "zone.classList.toggle('mw-ui-header-collapsed',c);\n";
		echo "btn.setAttribute('aria-expanded',c?'false':'true');\n";
		echo "var i=btn.querySelector('i');if(i){i.className='fas fa-chevron-'+(c?'down':'up');}\n";
		echo "btn.setAttribute('title',c?btn.getAttribute('data-mw-title-expand'):btn.getAttribute('data-mw-title-collapse'));\n";
		echo "if(store){try{window.localStorage.setItem(key,c?'1':'0');}catch(e){}}\n";
		echo "if(typeof window.mw_ui_header_on_toggle==='function'){try{window.mw_ui_header_on_toggle(c);}catch(e){}}\n";
		echo "window.setTimeout(function(){window.dispatchEvent(new Event('resize'));},80);\n";
		echo "}\n";
		echo "btn.addEventListener('click',function(ev){ev.preventDefault();collapsed=!collapsed;apply(collapsed,true);return false;});\n";
		echo "apply(collapsed,false);\n";
		echo "});\n";
		echo "})();\n";
		echo "</script>\n";
	}
	function exec_page_nav_topbar($subinterface){
		$mnu_man=$this->main_ui->mnu_man;
		$mnu=$mnu_man->get_item("topbar");
		$nav=new mwmod_mw_html_elem("nav");
		$nav->addClass("sb-topnav navbar navbar-expand {$this->css_topbar}");

		$this->output_ui_header_toggle_assets();
		$this->add_ui_header_toggle($nav,$subinterface);
		$this->add_nav_toggle($nav);
		
		
		$brand=new mwmod_mw_html_cont_varcont();
		$brand->add_cont("<!-- Navbar Brand-->");
		$a=new mwmod_mw_html_elem("a");
		$a->addClass("navbar-brand ps-3");
		$a->add_cont($this->main_ui->get_ui_title_for_nav()."");
		$brand->add_cont($a);
		$nav->add_cont($brand);
		/*
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
		*/

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
		// Header + sub-menu travel together so the topbar toggle can hide both.
		$header_zone=$this->get_ui_header_zone_open_html($subinterface);
		echo $header_zone;
		$row=new mwmod_mw_bootstrap_html_grid_row();
		echo $row->get_html_open_full();
		$col=new mwmod_mw_bootstrap_html_grid_col();
		echo $col->get_html_open_full();
		$mnu_toggle=$this->get_sub_interface_mnu_toggle_html($subinterface,true);
		if(!$subinterface->omit_header()){
			echo "<div class='mw-subinterface-header".($mnu_toggle ? " mw-subinterface-header-with-mnutoggle" : "")."'>\n";
			echo $mnu_toggle;
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
			echo "</div>\n";
		}else{
			// No header to attach to: the toggle gets its own row, above the menu.
			echo $this->get_sub_interface_mnu_toggle_html($subinterface,false);
		}
		echo $col->get_html_close_full();
		echo $row->get_html_close_full();
		$this->exec_sub_interface_mnu($subinterface);
		if($header_zone){
			echo "</div>\n";
		}
		
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
		
		if(!$mnu=$this->get_sub_interface_mnu_for($subinterface)){
			return false;	
		}
		//mw_array2list_echo($mnu->get_debug_data());
		$row=new mwmod_mw_bootstrap_html_grid_row();
		echo $row->get_html_open_full();
		$col=new mwmod_mw_bootstrap_html_grid_col(12);
		echo $col->get_html_open_full();
		

		echo "<nav class='currentUINavBar navbar navbar-expand-lg navbar-light bg-light' >\n";
		// The collapse toggle is NOT rendered here: get_sub_interface_mnu_toggle_html()
		// puts it on the title line with a list icon, so the page does not show a
		// second hamburger on a line of its own.
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
		$html= "<div class='toggleFullScreenBtnContainer' id='toggleFullScreenBtnContainer'>\n";
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