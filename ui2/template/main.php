<?php
/**
 * UI2 Template - Modern template with customizable CSS variables
 * 
 * Extends SBAdmin template but uses its own CSS structure for UI2.
 * CSS is organized with custom properties for easy theming.
 */
class mwmod_mw_ui2_template_main extends mwmod_mw_uitemplates_sbadmin_template_main {
	
	/** @var string Light topbar for modern UI */
	public $css_topbar = "navbar-light";
	
	/**
	 * Override default CSS sheets to use UI2's clean CSS structure.
	 * Loads Bootstrap 5 + custom UI2 styles with CSS variables.
	 * 
	 * @param mwmod_mw_html_manager_css $cssmanager
	 */
	function add_default_css_sheets($cssmanager) {
		// Icons - same as legacy
		$cssmanager->add_item_by_item(new mwmod_mw_html_manager_item_css("glyphicon", "/res/icons/glyphicons/glyphicon.css"));
		$cssmanager->add_item_by_item(new mwmod_mw_html_manager_item_css("fontawesome", "/res/icons/fontawesome-free/css/all.min.css"));
		$cssmanager->add_item_by_item(new mwmod_mw_html_manager_item_css("meraldaicons", "/res/css/meralda_icons.css"));
		
		// UI2 CSS - Bootstrap 5 + custom variables + components
		$cssmanager->add_item_by_item(new mwmod_mw_html_manager_item_css("ui2-bootstrap", "/res/meralda/ui2/css/bootstrap.min.css"));
		$cssmanager->add_item_by_item(new mwmod_mw_html_manager_item_css("ui2-fonts", "/res/meralda/ui2/css/fonts.css"));
		$cssmanager->add_item_by_item(new mwmod_mw_html_manager_item_css("ui2-variables", "/res/meralda/ui2/css/variables.css"));
		$cssmanager->add_item_by_item(new mwmod_mw_html_manager_item_css("ui2-layout", "/res/meralda/ui2/css/layout.css"));
		$cssmanager->add_item_by_item(new mwmod_mw_html_manager_item_css("ui2-components", "/res/meralda/ui2/css/components.css"));
		$cssmanager->add_item_by_item(new mwmod_mw_html_manager_item_css("ui2-theme", "/res/meralda/ui2/css/theme.css"));
	}
	
	/**
	 * Override JS loading to use UI2's independent scripts
	 * 
	 * @param object $mainUI
	 * @param mwmod_mw_html_manager_js $jsmanager
	 */
	function add_default_js_scripts_for_main($mainUI, $jsmanager) {
		$item = new mwmod_mw_html_manager_item_jsexternal("ui2scripts", "/res/meralda/ui2/js/scripts.js");
		$jsmanager->add_item_by_item($item);
		$item->bottom = true;
	}
	
	/**
	 * Override single mode rendering to use authentication layout
	 * Wraps content in #layoutAuthentication for glassmorphism styling
	 * 
	 * @param object $subinterface
	 */
	function exec_page_full_body_sub_interface_single_mode($subinterface) {
		echo '<div id="layoutAuthentication">';
		echo '<div id="layoutAuthentication_content">';
		$subinterface->do_exec_page_single_mode();
		echo '</div>';
		echo '</div>';
	}
}
?>
