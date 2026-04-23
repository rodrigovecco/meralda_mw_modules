<?php
/**
 * Users2 - Users List (DataGrid)
 * 
 * Admin interface for listing and managing users.
 * Uses DevExtreme DataGrid with remote data loading.
 */
class mwmod_mw_users2_ui_users extends mwmod_mw_ui_sub_uiabs {
    
    /** @var mwmod_mw_devextreme_data_queryhelper */
    public $queryHelper;
    
    /** @var string|false Excel export name */
    public $excelExportName = false;
    
    public function __construct($cod, $parent) {
        $this->init_as_main_or_sub($cod, $parent);
        $this->set_def_title($this->lng_common_get_msg_txt("users", "Usuarios"));
        $this->js_ui_class_name = "mw_ui_grid_remote";
        $this->mnuIconClass = "fa fa-users";
    }
    
    /**
     * Get users manager
     * @return mwmod_mw_users2_usersman|null
     */
    public function getUman() {
        return $this->maininterface->get_admin_user_manager();
    }
    
    /**
     * Check permission
     */
    public function is_allowed() {
        return $this->allow("adminusers");
    }
    
    /**
     * Allow creating subinterfaces by code
     */
    public function allowcreatesubinterfacechildbycode() {
        return true;
    }
    
    /**
     * Build query for DataGrid
     */
    public function getQuery() {
        $this->queryHelper = new mwmod_mw_devextreme_data_queryhelper();
        
        if (!$man = $this->getUman()) {
            return false;
        }
        if (!$tblman = $man->get_tblman()) {
            return false;
        }
        if (!$query = $tblman->new_query()) {
            return false;
        }
        
        // Add all fields except password
        if ($tblfields = $tblman->getFields()) {
            foreach ($tblfields as $c => $f) {
                if ($c !== "pass") {
                    if ($item = $this->queryHelper->addFieldBySqlExp($c, $tblman->tbl . "." . $c)) {
                        $item->setOptionsByField($f);
                    }
                }
            }
        }
        
        $this->afterGetQuery($query);
        return $query;
    }
    
    /**
     * Hook for extending query
     */
    protected function afterGetQuery($query): void {
        // Override to add joins, conditions, etc.
    }
    
    /**
     * Load subinterfaces
     */
    public function load_all_subinterfases() {
        $this->add_new_subinterface(new mwmod_mw_users2_ui_newuser("new", $this));
        $this->add_new_subinterface(new mwmod_mw_users2_ui_user("user", $this));
        
        // Add groups admin if available
        if ($uman = $this->getUman()) {
            if ($grman = $uman->get_groups_man()) {
                $grman->add_admin_interface($this);
            }
        }
    }
    
    /**
     * Prepare before execution
     */
    public function do_exec_no_sub_interface() {
        $util = new mwmod_mw_devextreme_util();
        if ($this->excelExportName) {
            $util->preapare_ui_exportExcel($this);
        }
        $util->preapare_ui_webappjs($this);
        
        $jsman = $this->maininterface->jsmanager;
      
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_adv.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_cols.js");
		
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem.js");
		
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_rdata.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_data.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem/devextreme_datagrid.js");
        
		$this->add_req_js_scripts();	
		$this->add_req_css();
    }
    
    /**
     * Execute page
     */
    public function do_exec_page_in() {
        $MainContainer = $this->get_ui_dom_elem_container();
        $container = $MainContainer;
        
        if ($this->mainPanelEnabled) {
            if ($mainpanel = $this->createMainPanel()) {
                $MainContainer->add_cont($mainpanel);
                $container = $mainpanel->panel_body->add_cont_elem();
            }
        }
        
        $gridcontainer = $this->set_ui_dom_elem_id("datagrid_container");
        $body = $this->set_ui_dom_elem_id("datagrid_body");
        $loading = new mwcus_cus_templates_html_loading_placeholder();
        $body->add_cont($loading);
        $gridcontainer->add_cont($body);
        
        $container->add_cont($gridcontainer);
        
        $this->getBotHtml($container);
        
        echo $MainContainer->get_as_html();
        
        // JS initialization
        $js = new mwmod_mw_jsobj_jquery_docreadyfnc();
        $this->set_ui_js_params();
        $jsui = $this->new_ui_js();
        $var = $this->get_js_ui_man_name();
        $js->add_cont("var {$var}=" . $jsui->get_as_js_val() . ";\n");
        $js->add_cont($var . ".init(" . $this->ui_js_init_params->get_as_js_val() . ");\n");
        
        echo $js->get_js_script_html();
    }
    
    /**
     * AJAX: Load DataGrid configuration
     */
    public function execfrommain_getcmd_sxml_loaddatagrid($params = array(), $filename = false) {
        $xml = $this->new_getcmd_sxml_answer(false);
        $this->xml_output = $xml;
        $xml->set_prop("htmlcont", $this->lng_get_msg_txt("not_allowed", "No permitido"));
        
        if (!$this->is_allowed()) {
            $xml->root_do_all_output();
            return false;
        }
        
        if (!$uman = $this->getUman()) {
            $xml->root_do_all_output();
            return false;
        }
        
        $xml->set_prop("ok", true);
        $xml->set_prop("htmlcont", "");
        
        $var = $this->get_js_ui_man_name();
        
        $datagrid = new mwmod_mw_devextreme_widget_datagrid(false);
        $datagrid->setFilerVisible();
        $datagrid->js_props->set_prop("columnAutoWidth", true);
        $datagrid->js_props->set_prop("allowColumnResizing", true);
        $datagrid->js_props->set_prop("paging.pageSize", 20);
        $datagrid->js_props->set_prop("remoteOperations.paging", true);
        $datagrid->js_props->set_prop("remoteOperations.filtering", true);
        $datagrid->js_props->set_prop("remoteOperations.sorting", true);
        
        if ($this->excelExportName) {
            $datagrid->js_props->set_prop("export.enabled", true);
            $datagrid->js_props->set_prop("export.fileName", $this->excelExportName);
        }
        
        $gridhelper = $datagrid->new_mw_helper_js();
        $datagrid->mw_helper_js_set_rdata_mode_from_ui($this, $gridhelper);
        
        $this->add_cols($datagrid);
        $this->afterDatagridCreated($datagrid, $gridhelper);
        
        $columns = $datagrid->columns->get_items();
        $list = $gridhelper->get_array_prop("columns");
        foreach ($columns as $col) {
            $coljs = $col->get_mw_js_colum_obj();
            $list->add_data($coljs);
        }
        
        if ($d = $this->getUniqItemsIds()) {
            $gridhelper->set_prop("uniqItemsIds", $d);
        }
        
        $js = new mwmod_mw_jsobj_obj();
        $js->set_prop("datagridman", $gridhelper);
        $xml_js = new mwmod_mw_data_xml_js("jsresponse", $js);
        
        $xml->add_sub_item($xml_js);
        $xml->root_do_all_output();
    }
    
    /**
     * AJAX: Load data for DataGrid
     */
    public function execfrommain_getcmd_sxml_loaddata($params = array(), $filename = false) {
        $xml = $this->new_getcmd_sxml_answer(false);
        $this->xml_output = $xml;
        
        if (!$this->is_allowed()) {
            $xml->root_do_all_output();
            return false;
        }
        
        if (!$man = $this->getUman()) {
            $xml->root_do_all_output();
            return false;
        }
        
        if (!$query = $this->getQuery()) {
            $xml->root_do_all_output();
            return false;
        }
        
        $xml->set_prop("ok", true);
        $js = new mwmod_mw_jsobj_obj();
        
        $dataqueryhelper = $this->queryHelper;
        $dataqueryhelper->setLoadOptions($_REQUEST["lopts"]);
        $dataqueryhelper->aplay2Query($query);
        
        if (!$dataqueryhelper->sorted) {
            $this->setDefaultQuerySort($query);
        }
        
        $js->set_prop("totalCount", $query->get_total_regs_num());
        
        $dataoptim = new mwmod_mw_jsobj_dataoptim();
        $dataoptim->set_key("id");
        $js->set_prop("dsoptim", $dataoptim);
        
        if ($items = $man->get_users_by_query($query)) {
            foreach ($items as $id => $item) {
                $data = $this->get_item_data($item);
                $dataoptim->add_data($data);
            }
        }
        
        $xml_js = new mwmod_mw_data_xml_js("js", $js);
        $xml->add_sub_item($xml_js);
        $xml->root_do_all_output();
    }
    
    /**
     * Get display data for a user item
     * @param mwmod_mw_users2_userabs $user
     * @return array
     */
    protected function get_item_data($user): array {
        $data = $user->get_public_tbl_data();
        if ($user_rols = $user->get_rols()) {
            $data["rols"] = implode(",", array_keys($user_rols));
        }
        $data["groups"] = $user->get_groups_str_list();
        return $data;
    }
    
    /**
     * Add columns to DataGrid
     */
    protected function add_cols($datagrid): void {
        if (!$uman = $this->getUman()) {
            return;
        }
        
        $col = $datagrid->add_column_number("id", "ID");
        $col->js_data->set_prop("width", 60);
        $col->js_data->set_prop("visible", true);
        
        $col = $datagrid->add_column_string("name", $this->lng_common_get_msg_txt("user", "Usuario"));
        
        $col = $datagrid->add_column_string("complete_name", $this->lng_common_get_msg_txt("name", "Nombre"));
        
        $col = $datagrid->add_column_string("rols", $this->lng_common_get_msg_txt("rols", "Roles"));
        $col->js_data->set_prop("visible", true);
        $col->set_mw_js_colum_class("mw_devextreme_datagrid_column_concatdata");
        
        $rolsdoptiom = new mwmod_mw_jsobj_dataoptim();
        $rolsdoptiom->set_key();
        if ($rolsman = $uman->get_rols_man()) {
            if ($rols = $rolsman->get_items()) {
                foreach ($rols as $cod => $rol) {
                    $rolsdoptiom->add_data(array("id" => $cod, "name" => $rol->get_name()));
                }
            }
        }
        $col->get_mw_js_colum_obj();
        $col->mw_js_colum_obj_params->set_prop("dataitems", $rolsdoptiom);
        
        $col = $datagrid->add_column_boolean("active", $this->lng_common_get_msg_txt("active", "Activo"));
        //$col->js_data->set_prop("filterValue", true);
    }
    
    /**
     * Default sort order
     */
    protected function setDefaultQuerySort($query): void {
        $query->order->add_order("name");
    }
    
    /**
     * Add extra columns after DataGrid is created
     * @param mwmod_mw_devextreme_widget_datagrid $datagrid
     * @param mwmod_mw_jsobj_obj $gridhelper
     */
    protected function afterDatagridCreated($datagrid, $gridhelper): void {
        $list = $gridhelper->get_array_prop("columnsExtra");
        $coljs = new mwmod_mw_jsobj_obj();
        $list->add_data($coljs);
        $coljs->set_prop("type", "buttons");
        $btns = $coljs->get_array_prop("buttons");
        
        $btnjs = new mwmod_mw_jsobj_obj();
        $btns->add_data($btnjs);
        $btnjs->set_prop("icon", "tags");
        $fnc = new mwmod_mw_jsobj_functionext();
        $fnc->add_fnc_arg("e");
        if ($subui = $this->get_subinterface("user")) {
            $url = $subui->get_url();
            $surv = $subui->mainItemReqParam;
            $fnc->add_cont("var url='{$url}&{$surv}='+e.row.data.id;");
            $fnc->add_cont("window.location=url;");
        }
        $btnjs->set_prop("onClick", $fnc);
    }
    
    /**
     * Menu configuration
     */
    public function is_responsable_for_sub_interface_mnu() {
        return true;
    }
    
    public function create_sub_interface_mnu_for_sub_interface($su = false) {
        $mnu = new mwmod_mw_mnu_mnu();
        $this->add_2_mnu($mnu);
        
        if ($subs = $this->get_subinterfaces_by_code("new,admingroups", true)) {
            foreach ($subs as $su) {
                $su->add_2_sub_interface_mnu($mnu);
            }
        }
        
        return $mnu;
    }
    
    public function add_mnu_items($mnu) {
        $this->add_2_mnu($mnu);
        $this->add_sub_interface_to_mnu_by_code($mnu, "new");
    }
}
?>
