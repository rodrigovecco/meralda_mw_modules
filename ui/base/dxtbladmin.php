<?php
/**
 * DevExtreme-based admin data table interface with full CRUD operations.
 *
 * Provides a complete data grid interface using DevExtreme with support for:
 * - Remote data loading with pagination, sorting, and filtering
 * - CRUD operations (Create, Read, Update, Delete)
 * - User preferences (column visibility, order, filters)
 * - Excel export functionality
 * - Toolbar customization
 * - Permission-based access control
 *
 * Child classes must implement:
 * - getItemsMan(): Return the items manager for data operations
 * - add_cols($datagrid): Define grid columns
 *
 * @property mwmod_mw_devextreme_data_queryhelper|null $queryHelper Query helper initialized in getQuery().
 * @property int $defPageSize Default page size for data grid pagination.
 * @property string $editingMode Editing mode for the data grid: "row", "cell", "batch", or "form".
 * @property string|null $excelExportName Name for Excel export file (without extension).
 * @property bool $columnsChooserEnabled Whether to enable the column chooser feature.
 * @property bool $addColCodsToExcel Whether to add column codes to Excel export.
 * @property bool $userColsOrderRememberEnabled Whether to remember column order in user preferences.
 * @property bool $userColsSelectedRememberEnabled Whether to remember column selection (visibility) in user preferences.
 * @property bool $userColsSelectedRememberEnabledVisible Whether to remember visible index of columns in user preferences.
 * @property bool $userColsPrefResetBtnEnabled Whether to show reset button for column preferences.
 * @property bool $clearFiltersBtnEnabled Whether to show clear filters button in toolbar.
 * @property bool $rememberFiltersBtnEnabled Whether to show remember filters button in toolbar.
 * @property bool $userFiltersRemember Whether to remember user filters in preferences.
 * @property bool $toolbarItemsExportButtonAdded Whether export button has been added to toolbar items.
 */
abstract class mwmod_mw_ui_base_dxtbladmin extends mwmod_mw_ui_base_basesubui{
	/**
	 * Query helper for DevExtreme data operations. Initialized in getQuery().
	 * @var mwmod_mw_devextreme_data_queryhelper|null
	 */
	public $queryHelper;
	
	/**
	 * Default page size for data grid pagination.
	 * @var int
	 */
	public $defPageSize=20;
	
	/**
	 * Editing mode for the data grid: "row", "cell", "batch", or "form".
	 * @var string
	 */
	public $editingMode="row";
	
	/**
	 * Name for Excel export file (without extension).
	 * @var string|null
	 */
	public $excelExportName;
	
	/**
	 * Whether to enable the column chooser feature.
	 * @var bool
	 */
	public $columnsChooserEnabled=false;
	
	/**
	 * Whether to add column codes to Excel export.
	 * @var bool
	 */
	public $addColCodsToExcel=false;

	/**
	 * Whether to remember column order in user preferences.
	 * @var bool
	 */
	public $userColsOrderRememberEnabled=false;

	/**
	 * Whether to remember column selection (visibility) in user preferences.
	 * @var bool
	 */
	public	$userColsSelectedRememberEnabled=false;
	
	/**
	 * Whether to remember visible index of columns in user preferences.
	 * @var bool
	 */
	public	$userColsSelectedRememberEnabledVisible=false;
	
	/**
	 * Whether to show reset button for column preferences.
	 * @var bool
	 */
	public	$userColsPrefResetBtnEnabled=false;
	
	/**
	 * Whether to show clear filters button in toolbar.
	 * @var bool
	 */
	public	$clearFiltersBtnEnabled=false;
	
	/**
	 * Whether to show remember filters button in toolbar.
	 * @var bool
	 */
	public	$rememberFiltersBtnEnabled=false;
	
	/**
	 * Whether to remember user filters in preferences.
	 * @var bool
	 */
	public $userFiltersRemember=false;

	/**
	 * Whether export button has been added to toolbar items.
	 * @var bool
	 */
	public $toolbarItemsExportButtonAdded=false;
	
	/**
	 * Enables user filter remembering mode.
	 *
	 * Activates clear filters button, filter memory, and remember filters button.
	 *
	 * @return void
	 */
	function setUserFilterRememberEnabledMode(){
		
		$this->clearFiltersBtnEnabled=true;
		$this->userFiltersRemember=true;
		$this->rememberFiltersBtnEnabled=true;

	}
	
	/**
	 * Enables user column selection remembering mode.
	 *
	 * Activates column chooser, column selection memory, visible index memory,
	 * reset button, and column order memory.
	 *
	 * @return void
	 */
	function setUserColsSelectedRememberEnabledMode(){
		
		$this->columnsChooserEnabled=true;
		$this->userColsSelectedRememberEnabled=true;
		$this->userColsSelectedRememberEnabledVisible=true;
		$this->userColsPrefResetBtnEnabled=true;
		//$this->userColsFiltersRememberEnabled=true;
		$this->userColsOrderRememberEnabled=true;
	}
	
	/**
	 * Returns whether column visible index should be remembered.
	 *
	 * @return bool True if visible index remembering is enabled.
	 */
	function userColsSelectedRememberEnabledVisibleIndex(){
		return $this->userColsSelectedRememberEnabledVisible;
	}
	
	/**
	 * Constructs the DevExtreme table admin interface.
	 *
	 * Initializes as main or sub interface, sets default title, JavaScript class,
	 * and row editing mode.
	 *
	 * @param string $cod Interface code identifier.
	 * @param mwmod_mw_ui_sub_uiabs|null $parent Parent interface or null if main interface.
	 */
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title("Some UI");
		$this->js_ui_class_name="mw_ui_grid_remote";
		$this->editingMode="row";
		
	}
	
	/**
	 * Checks if user is allowed to save column state preferences.
	 *
	 * @return bool|null True if allowed, null if column state remembering is disabled.
	 */
	function allowSaveColsState(){
		if($this->userColsSelectedRememberEnabled){
			return $this->is_allowed();	
		}
	}
	
	/**
	 * Checks if user is allowed to save filter preferences.
	 *
	 * @return bool|null True if allowed, null if filter remembering is disabled.
	 */
	function allowSaveFilters(){
		if($this->userFiltersRemember){
			return $this->is_allowed();	
		}
	}

	/**
	 * AJAX endpoint to save user filter preferences.
	 *
	 * Validates and stores column filter values in user UI data storage.
	 * Returns XML response with success status and notification message.
	 *
	 * @param array $params Request parameters (unused).
	 * @param bool|string $filename Filename parameter (unused).
	 * @return void|false Outputs XML response, returns false on permission failure.
	 */
	function execfrommain_getcmd_sxml_savefilters($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		if(!$this->allowSaveFilters()){
			$xml->root_do_all_output();
			return false;	
		}
		
		
		if(!$dataItem=$this->get_user_ui_data("filters")){
			$xml->root_do_all_output();
			return false;	
		}
		//$xml->set_prop("R",$_GET);
		$input=new mwmod_mw_helper_inputvalidator_request("filters");
		//todo: other filters
		if(!$input->is_req_input_ok()){
			$xml->set_prop("error","no input");
			$xml->root_do_all_output();
			return false;	
		}
		if(!$nd=$input->get_value_by_dot_cod_as_list("cols")){
			$xml->set_prop("error","no input cols");
			$xml->root_do_all_output();
			return false;	
		}
		$xml->set_prop("newdata",$nd);
		$chaged=false;
		foreach($nd as $cod=>$val){
			if(is_array($val)){
				if($this->check_str_key_alnum_underscore($cod)){
					
					$dataItem->set_data($val,"cols.".$cod."");
					$chaged=true;
					
					
					
					
				
				}
			}

			
		}
		if($chaged){
			$dataItem->set_data(date("Y-m-d H:i:s"),"updatedTime");

			$dataItem->save();
		}



		
		
		
		$xml->set_prop("ok",true);
		
		$xml->set_prop("filters",$dataItem->get_data());
		$xml->set_prop("notify.message",$this->lng_common_get_msg_txt("MSGfilteresSaved","Estado de filtros guardado correctamente."));
		$xml->set_prop("notify.type","success");
	
		
		$xml->root_do_all_output();

	}
	
	/**
	 * AJAX endpoint to reset column state preferences to defaults.
	 *
	 * Clears all saved column preferences (visibility, order, width) for the current user.
	 * Returns XML response with success notification and JavaScript reload instruction.
	 *
	 * @param array $params Request parameters (unused).
	 * @param bool|string $filename Filename parameter (unused).
	 * @return void|false Outputs XML response, returns false on permission failure.
	 */
	function execfrommain_getcmd_sxml_resetcolsstate($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		if(!$this->allowSaveColsState()){
			$xml->root_do_all_output();
			return false;	
		}
		
		
		if(!$dataItem=$this->get_user_ui_data("colsstate")){
			$xml->root_do_all_output();
			return false;	
		}
		$dataItem->set_data(array());
		$dataItem->save();

		
		$js=new mwmod_mw_jsobj_obj();
		$xml_js=new mwmod_mw_data_xml_js("js",$js);		
		$xml->add_sub_item($xml_js);



	
		
		$xml->set_prop("notify.message",$this->lng_common_get_msg_txt("MSGcolumnsPrefReseted","Se restablecieron las preferencias de columnas. Por favor, actualizar la página."));
		$xml->set_prop("notify.type","warning");
		
		$xml->set_prop("ok",true);
		
		$xml->set_prop("colsstate",$dataItem->get_data());
	
		
		$xml->root_do_all_output();

	}
	
	/**
	 * AJAX endpoint to save column state preferences.
	 *
	 * Validates and stores column properties (visibility, order, width) excluding filter values.
	 * Saves column state per column code in user UI data storage.
	 *
	 * @param array $params Request parameters (unused).
	 * @param bool|string $filename Filename parameter (unused).
	 * @return void|false Outputs XML response, returns false on permission/validation failure.
	 */
	function execfrommain_getcmd_sxml_savecolsstate($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		if(!$this->allowSaveColsState()){
			$xml->root_do_all_output();
			return false;	
		}
		
		
		if(!$dataItem=$this->get_user_ui_data("colsstate")){
			$xml->root_do_all_output();
			return false;	
		}
		$input=new mwmod_mw_helper_inputvalidator_request("cols");
		if(!$input->is_req_input_ok()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$nd=$input->get_value_as_list()){
			$xml->root_do_all_output();
			return false;	
		}
		$xml->set_prop("newdata",$nd);
		$chaged=false;
		foreach($nd as $cod=>$val){
			if(is_array($val)){
				if($this->check_str_key_alnum_underscore($cod)){
					unset($val["filterValue"]);
					unset($val["selectedFilterOperation"]);
					$dataItem->set_data($val,"cols.".$cod."");
					$chaged=true;
					
					
					
					
				
				}
			}

			
		}
		if($chaged){
			$dataItem->set_data(date("Y-m-d H:i:s"),"updatedTime");

			$dataItem->save();
		}



		
		
		
		$xml->set_prop("ok",true);
		
		$xml->set_prop("colsstate",$dataItem->get_data());
	
		
		$xml->root_do_all_output();

	}
	
	/**
	 * Gets the default page size for pagination.
	 *
	 * @return int The default page size.
	 */
	function getDefPageSize(){
		return $this->defPageSize;	
	}
	/**
	 * @return mwmod_mw_manager_man
	 */
	function getItemsMan(){
		return $this->items_man;
	}
	
	/**
	 * Checks if deletion is allowed.
	 *
	 * @return bool True if user has admin permission.
	 */
	function allowDelete(){
		return $this->allow_admin();
	}
	
	/**
	 * Checks if insertion is allowed.
	 *
	 * @return bool True if user has admin permission.
	 */
	function allowInsert(){
		return $this->allow_admin();
	}
	
	/**
	 * Checks if updates are allowed.
	 *
	 * @return bool True if user has admin permission.
	 */
	function allowUpdate(){
		return $this->allow_admin();
	}
	
	/**
	 * Checks if a specific item can be deleted.
	 *
	 * Override in child classes for item-specific delete permissions.
	 *
	 * @param mixed $item The item to check.
	 * @return bool True if deletion is allowed.
	 */
	function allowDeleteItem($item){
		return $this->allowDelete();
	}
	
	/**
	 * Checks if a specific item can be updated.
	 *
	 * Override in child classes for item-specific update permissions.
	 *
	 * @param mixed $item The item to check.
	 * @return bool True if update is allowed.
	 */
	function allowUpdateItem($item){
		return $this->allowUpdate();
	}
	/**
     * @return false|mwmod_mw_db_sql_query
     */
	function getQuery(){
		
		$this->queryHelper=new mwmod_mw_devextreme_data_queryhelper();
		if(!$man=$this->getItemsMan()){
			return false;
		}
		if(!$tblman=$man->get_tblman()){
			return false;	
		}
		if(!$query=$tblman->new_query()){
			return false;	
		}
		$this->queryHelper->addAllTblFields($tblman);
		$this->afterGetQuery($query);
		return $query;
	}
	/**
     * Hook to customize the query.
     * @param mwmod_mw_db_sql_query $query
     */
	function afterGetQuery($query){
		//extender	
	}
	
	/**
	 * Gets the query from request parameters.
	 *
	 * Convenience method that calls getQuery().
	 *
	 * @return mwmod_mw_db_sql_query|false The query object or false on failure.
	 */
	function getQueryFromReq(){
		if(!$query=$this->getQuery()){
			return false;	
		}
		return $query;
			
	}
	
	/**
	 * Converts an item object to array data for DevExtreme table.
	 *
	 * @param mixed $item The item object.
	 * @return array The item data formatted for DevExtreme.
	 */
	function get_item_data($item){
		$r=$item->getDataForDXtbl();
		return $r;
	}
	

	/**
	 * AJAX endpoint to load data for the DevExtreme grid.
	 *
	 * Processes DevExtreme load options (skip, take, sort, filter) and returns
	 * paginated data with total count. Handles permissions, query creation,
	 * and data transformation.
	 *
	 * @param array $params Request parameters (unused).
	 * @param bool|string $filename Filename parameter (unused).
	 * @return void|false Outputs XML response with data array and total count.
	 */
	function execfrommain_getcmd_sxml_loaddata($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		//$xml->set_prop("htmlcont",$this->lng_get_msg_txt("not_allowed","No permitido"));
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;
	
		}
		if(!$man=$this->getItemsMan()){
			$xml->root_do_all_output();
			return false;
		}
		if(!$query=$this->getQueryFromReq()){
			$xml->root_do_all_output();
			return false;
		}
		
		$xml->set_prop("ok",true);
		$js=new mwmod_mw_jsobj_obj();
		//$xml->set_prop("debug.sqlbefore",$query->get_sql());
		//$xml->set_prop("debug.loadoptions",$_REQUEST["lopts"]);
		$dataqueryhelper=$this->queryHelper;
		$dataqueryhelper->setLoadOptions($_REQUEST["lopts"]??null);
		//$xml->set_prop("debug.dataqueryhelper",$dataqueryhelper->getDebugData());
		$dataqueryhelper->aplay2Query($query);
		if(!$dataqueryhelper->sorted){
			$this->setDefaultQuerySort($query);
		}
		

		$totalCount=0;
		if($totaldata=$query->get_total_data()){
			if(isset($totaldata[$query->sql_count_name])){
				$totalCount=intval($totaldata[$query->sql_count_name]);
				unset($totaldata[$query->sql_count_name]);
				if(sizeof($totaldata)>0){
					$summary=$js->get_array_prop("summary");
					foreach($totaldata as $k=>$v){
						$summary->add_data($v);
					}
				}

			}
		}

		
		$js->set_prop("totalCount",$totalCount);
		
		
		//$js->set_prop("totalCount",$query->get_total_regs_num());
		
		
		
		$dataoptim=new mwmod_mw_jsobj_dataoptim();
		$dataoptim->set_key("id");
		$js->set_prop("dsoptim",$dataoptim);
		if($items=$man->get_items_by_query($query)){
			foreach($items as $id=>$item){
				//$xml->set_prop("debug.item.".$id,"d");
				$data=$this->get_item_data($item);
				$dataoptim->add_data($data);	
			}
			
		}
		if($this->debugOutputEnabled()){
			$xml->set_prop("debug.sql",$query->get_sql());
			if($query->isParameterizedMode()){
				$xml->set_prop("debug.parammode",true);
				if($query->currentParameterizedQuery){
					$xml->set_prop("debug.paramquery",$query->currentParameterizedQuery->getDebugData());
					
				}
			
				
			}
			//$xml->set_prop("debug.error",$query->dbman->get_error());
			//$xml->set_prop("debug.full",$query->get_parts_debug_data());
		}
		$xml_js=new mwmod_mw_data_xml_js("js",$js);
		
		
		$xml->add_sub_item($xml_js);
		$xml->root_do_all_output();
		
		
		//
		
			
	}
	
	/**
	 * Sets default sort order for the query.
	 *
	 * Override in child classes to define default sorting.
	 *
	 * @param mwmod_mw_db_sql_query $query The query object to modify.
	 * @return void
	 */
	function setDefaultQuerySort($query){
		//extender
	}
	
	/**
	 * Renders HTML content at the bottom of the grid container.
	 *
	 * Override in child classes to add bottom content.
	 *
	 * @param mixed $container The container element.
	 * @return void
	 */
	function getBotHtml($container){
		
	}
	
	/**
	 * Renders HTML content at the top of the grid container.
	 *
	 * Override in child classes to add header content.
	 *
	 * @param mixed $container The container element.
	 * @return void
	 */
	function getTopHtml($container){
		
	}
	
	
	/**
	 * Renders the main page with the DevExtreme grid container.
	 *
	 * Creates the HTML structure including optional main panel, top/bottom content,
	 * and the grid container element. Outputs the complete HTML.
	 *
	 * @return void Outputs HTML directly.
	 */
	function do_exec_page_in(){
		$MainContainer=$this->get_ui_dom_elem_container();
		$container=$MainContainer;
		if($this->mainPanelEnabled){
			if($mainpanel=$this->createMainPanel()){
				$MainContainer->add_cont($mainpanel);
				$container=$mainpanel->panel_body->add_cont_elem();
			}
		}





		//$container=$this->get_ui_dom_elem_container();

		$gridcontainer=$this->set_ui_dom_elem_id("datagrid_container");
		$body=$this->set_ui_dom_elem_id("datagrid_body");
		$loading=new mwcus_cus_templates_html_loading_placeholder();
		$body->add_cont($loading);
		$gridcontainer->add_cont($body);
		$this->getTopHtml($container);
		$container->add_cont($gridcontainer);
		
		$this->getBotHtml($container);

		echo $MainContainer->get_as_html();
		
		//
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		
		

		//$this->setDefaultJSinitParams();
		
		//$jsui=$this->new_ui_js();
		$var=$this->get_js_ui_man_name();
		//$js->add_cont("var {$var}=".$jsui->get_as_js_val().";\n");
		
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		
		echo $js->get_js_script_html();
		return;
		
		

		
	}
	
	/**
	 * Sets UI JavaScript initialization parameters.
	 *
	 * Calls setDefaultJSinitParams(). Override for custom parameters.
	 *
	 * @return void
	 */
	function set_ui_js_params(){
		$this->setDefaultJSinitParams();
	}
	
	/**
	 * Sets default JavaScript initialization parameters for the grid.
	 *
	 * Configures user preferences features and Excel export settings.
	 *
	 * @return void
	 */
	function setDefaultJSinitParams(){
		if($this->userColsSelectedRememberEnabled){
			$this->ui_js_init_params->set_prop("userColsSelectedRememberEnabled",true);
		}
		if($this->userFiltersAndOrderRememberEnabled){
			$this->ui_js_init_params->set_prop("userFiltersAndOrderRememberEnabled",true);
		}
		if($this->excelExportName){
			$this->ui_js_init_params->set_prop("excelExportName",$this->excelExportName);
		}
		///$this->ui_js_init_params->set_prop("excelExportName",$this->excelExportName);

	}
	
	/**
	 * Prepares resources before interface execution.
	 *
	 * Loads DevExtreme utilities, Excel export support, JavaScript libraries,
	 * and CSS resources required for the grid.
	 *
	 * @return void
	 */
	function before_exec(){
		$util=new mwmod_mw_devextreme_util();
		if($this->excelExportName){
			$util->preapare_ui_exportExcel($this);
		}
		$util->preapare_ui_webappjs($this);
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_adv.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_cols.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_rdata.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_data.js");

		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem/devextreme_datagrid.js");
		
		$this->add_req_js_scripts();	
		$this->add_req_css();
		$item=$this->create_js_man_ui_header_declaration_item();
		$jsman->add_item_by_item($item);

	}
	
	/**
	 * AJAX endpoint to create a new item.
	 *
	 * Validates input data, checks permissions, creates the item via create_new_item(),
	 * and returns the new item data in XML response.
	 *
	 * @param array $params Request parameters (unused).
	 * @param bool|string $filename Filename parameter (unused).
	 * @return void|false Outputs XML response with new item data or error.
	 */
	function execfrommain_getcmd_sxml_newitem($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$this->allowInsert()){
			$xml->root_do_all_output();
			return false;	
		}
		
		if(!$man=$this->items_man){
			$xml->root_do_all_output();
			return false;	
		}
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		if(!$input->is_req_input_ok()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$nd=$input->get_value_as_list()){
			$xml->root_do_all_output();
			return false;	
		}
		//$xml->set_prop("nd",$nd);
		unset($nd["id"]);
		if(!$this->check_before_create_item($nd,$xml)){
			$xml->root_do_all_output();
			return false;
		}
		if(!$item=$this->create_new_item($nd)){
			$xml->set_prop("notify.message",$this->lng_get_msg_txt("unableToCreateElement","No se pudo crear el elemento."));
			$xml->set_prop("notify.type","error");
			$xml->root_do_all_output();
			return false;	
		}
		$xml->set_prop("ok",true);
		$xml->set_prop("itemid",$item->get_id());
		$xml->set_prop("itemdata",$this->get_item_data($item));
		$xml->set_prop("notify.message",$item->get_name()." ".$this->lng_get_msg_txt("LCcreated","creado"));
		$xml->set_prop("notify.type","success");
		
		$xml->root_do_all_output();

	}
	
	/**
	 * Hook to validate data before creating a new item.
	 *
	 * Override in child classes to implement validation logic.
	 *
	 * @param array $nd The new item data.
	 * @param mixed $xml The XML response object.
	 * @return bool True if validation passes, false to prevent creation.
	 */
	function check_before_create_item($nd,$xml){
		//false if data is missing or there is a duplicate key
		return true;
	}
	
	/**
	 * Creates a new item in the database.
	 *
	 * Delegates to the items manager's create_new_item() method.
	 *
	 * @param array $nd The new item data.
	 * @return mixed|null The created item object or null on failure.
	 */
	function create_new_item($nd){
		if($man=$this->items_man){
			
			return $man->create_new_item($nd);	
		}
	}
	
	/**
	 * AJAX endpoint to delete an item.
	 *
	 * Validates permissions, retrieves item by ID from request, calls delete_item(),
	 * and returns success/error XML response.
	 *
	 * @param array $params Request parameters (unused).
	 * @param bool|string $filename Filename parameter (unused).
	 * @return void|false Outputs XML response with deletion result.
	 */
	function execfrommain_getcmd_sxml_deleteitem($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$this->allowDelete()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$man=$this->__get_priv_items_man()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$item=$this->getOwnItem($_REQUEST["itemid"]??null)){
			$xml->root_do_all_output();
			return false;	
				
		}
		if(!$this->allowDeleteItem($item)){
			$xml->set_prop("notify.message",$this->lng_get_msg_txt("cant_eliminate","No se pudo eliminar")." ".$item->get_name());
			$xml->set_prop("notify.type","error");
			$xml->root_do_all_output();
			return false;	
		}

		
		$this->delete_item($item,$xml);
		if($d=$this->getUniqItemsIds()){
			$xml->set_prop("uniqItemsIds",$d);
		}
		
		$xml->root_do_all_output();
		

	}
	
	/**
	 * Deletes an item after checking for related objects.
	 *
	 * Validates that no related objects exist, performs deletion, and sets
	 * appropriate success/error messages in the XML response.
	 *
	 * @param mixed $item The item to delete.
	 * @param mixed $xmlresponse The XML response object for messages.
	 * @return void|false Returns false if item has related objects, void on success.
	 */
	function delete_item($item,$xmlresponse){
		$xmlresponse->set_prop("itemid",$item->get_id());
		if($relman=$item->get_related_objects_man()){
			if($relman->get_rel_objects_num()){
				if($msg=$relman->get_relations_msg_plain()){
					$msg.="\n".$this->lng_get_msg_txt("cant_eliminate","No se pudo eliminar")." ".$item->get_name();
					$xmlresponse->set_prop("notify.message",$msg);
					$xmlresponse->set_prop("notify.type","error");
					$xmlresponse->set_prop("notify.multiline",true);
					
					
						
				}else{
					$xmlresponse->set_prop("notify.message",$this->lng_get_msg_txt("cant_eliminate","No se pudo eliminar")." ".$item->get_name());
					$xmlresponse->set_prop("notify.type","error");
						
				}
				return false;	
			}
				
		}
		$item->do_delete();
		$xmlresponse->set_prop("ok",true);
		$xmlresponse->set_prop("notify.message",$item->get_name()." ".$this->lng_get_msg_txt("LCdeleted","eliminado"));
		$xmlresponse->set_prop("notify.type","success");
		return true;

			
	}
	
	/**
	 * AJAX endpoint to save/update an existing item.
	 *
	 * Validates permissions, retrieves item by ID, validates input data,
	 * calls saveItem(), and returns updated item data in XML response.
	 *
	 * @param array $params Request parameters (unused).
	 * @param bool|string $filename Filename parameter (unused).
	 * @return void|false Outputs XML response with updated item data or error.
	 */
	function execfrommain_getcmd_sxml_saveitem($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$this->allow_admin()){
			$xml->root_do_all_output();
			return false;	
		}
		
		if(!$man=$this->__get_priv_items_man()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$item=$this->getOwnItem($_REQUEST["itemid"]??null)){
			$xml->root_do_all_output();
			return false;	
				
		}
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		if(!$input->is_req_input_ok()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$nd=$input->get_value_as_list()){
			$xml->root_do_all_output();
			return false;	
		}
		if(!$this->allowUpdateItem($item)){
			$xml->root_do_all_output();
			return false;	
		}
		
		//$item->do_save_data($nd);
		$this->saveItem($item,$nd);
		$xml->set_prop("ok",true);
		$xml->set_prop("itemid",$item->get_id());
		$xml->set_prop("itemdata",$this->get_item_data($item));
		
		if($d=$this->getUniqItemsIds()){
			$xml->set_prop("uniqItemsIds",$d);
		}
		
		$xml->root_do_all_output();
		
		//$item->tem

	}
	
	/**
	 * Saves/updates an item with new data.
	 *
	 * Removes ID field, handles empty name field, and delegates to item's do_save_data().
	 *
	 * @param mixed $item The item to update.
	 * @param array $nd The new data array.
	 * @return void
	 */
	function saveItem($item,$nd){
		unset($nd["id"]);
		if(isset($nd["name"])and(!$nd["name"])){
			unset($nd["name"]);	
		}
		$item->do_save_data($nd);
		
	}
	
	/**
	 * Retrieves an item by ID if it's allowed for current user.
	 *
	 * @param int|string|null $id The item ID.
	 * @return mixed|false The item object if found and allowed, false otherwise.
	 */
	function getOwnItem($id){
		if($man=$this->__get_priv_items_man()){
			if($item=$man->get_item($id)){
				if($this->isItemAllowed($item)){
					return $item;	
				}
			}
			
			
		}
	

	}
	
	/**
	 * Checks if an item is allowed for the current user.
	 *
	 * Override in child classes to implement item-level access control.
	 * Default implementation allows all items.
	 *
	 * @param mixed $item The item to check.
	 * @return bool|mixed True/item if allowed, false otherwise.
	 */
	function isItemAllowed($item){
		return $item;
	}

	/**
	 * AJAX endpoint to load the DevExtreme datagrid configuration.
	 *
	 * Creates and configures the datagrid JavaScript object with columns, user preferences,
	 * toolbar items, and editing options. Returns the grid configuration as JavaScript.
	 *
	 * @param array $params Request parameters (unused).
	 * @param bool|string $filename Filename parameter (unused).
	 * @return void|false Outputs XML response with datagrid JavaScript configuration.
	 */
	function execfrommain_getcmd_sxml_loaddatagrid($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		$this->xml_output=$xml;
		$xml->set_prop("htmlcont",$this->lng_get_msg_txt("not_allowed","No permitido"));
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;
	
		}
		if(!$this->items_man){
			$xml->root_do_all_output();
			return false;
		}
		
		$xml->set_prop("ok",true);
		$xml->set_prop("htmlcont","");
		
		$var=$this->get_js_ui_man_name();

		$datagrid=new mwmod_mw_devextreme_widget_datagrid(false);
		$datagrid->setFilerVisible();
		if($this->excelExportName){
			$datagrid->js_props->set_prop("export.enabled",true);	
			$datagrid->js_props->set_prop("export.fileName",$this->excelExportName);
			
			
			
			
		}
		
		$datagrid->js_props->set_prop("columnAutoWidth",true);	
		$datagrid->js_props->set_prop("allowColumnResizing",true);
		if($this->allowUpdate()){
			$datagrid->js_props->set_prop("editing.allowUpdating",true);
			$datagrid->js_props->set_prop("editing.mode",$this->editingMode);
		}
		if($this->allowInsert()){
			$datagrid->js_props->set_prop("editing.allowAdding",true);
		}
		if($this->allowDelete()){
			$datagrid->js_props->set_prop("editing.allowDeleting",true);
		}
		$datagrid->js_props->set_prop("editing.useIcons",true);
		
		//$datagrid->js_props->set_prop("editing.mode","row");
		$datagrid->js_props->set_prop("paging.pageSize",$this->getDefPageSize());
		$datagrid->js_props->set_prop("remoteOperations.paging",true);
		$datagrid->js_props->set_prop("remoteOperations.filtering",true);
		$datagrid->js_props->set_prop("remoteOperations.sorting",true);
		
		
		$gridhelper=$datagrid->new_mw_helper_js();
		
		//$datagrid->mw_helper_js_set_editrow_mode_from_ui($this,$gridhelper,true,true,true);
		$datagrid->mw_helper_js_set_rdata_mode_from_ui($this,$gridhelper);
		$gridhelper->set_prop("dataSourceMan.dataKey","id");//new!!
		$gridhelper->set_fnc_name("mw_devextreme_datagrid_man_rdataedit");

		if($this->addColCodsToExcel){
			$gridhelper->set_prop("addColCodsToExcel",true);	
		}
		

		$this->add_cols($datagrid);
		$this->setColsUserPrefs($datagrid);
		
		//$dataoptim=$datagrid->new_dataoptim_data_man();
		//$dataoptim->set_key("id");
		/*
		if($items=$this->items_man->get_all_items()){
			foreach($items as $id=>$item){
				$data=$this->get_item_data($item);
				$dataoptim->add_data($data);	
			}
		}
			*/
		

		$columns=$datagrid->columns->get_items();

		$list=$gridhelper->get_array_prop("columns");
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$list->add_data($coljs);
			
		}
		
		$list->add_data($coljs);
		
		
		if($d=$this->getUniqItemsIds()){
			$gridhelper->set_prop("uniqItemsIds",$d);
			
		}
		
		
		$this->afterDatagridCreated($datagrid,$gridhelper);
		
		$js=new mwmod_mw_jsobj_obj();
		$js->set_prop("datagridman",$gridhelper);
		$xml_js=new mwmod_mw_data_xml_js("jsresponse",$js);
		
		
		$xml->add_sub_item($xml_js);
		$xml->root_do_all_output();
		
		
		
		
			
	}
	function setColsUserPrefs($datagrid){
		$this->setColsUserPrefsColsOptions($datagrid);
		$this->setColsUserPrefsFilters($datagrid);

		

	}
	function setColsUserPrefsFilters($datagrid){
		if(!$this->userFiltersRemember){
			return false;	
		}
		if(!$dataItem=$this->get_user_ui_data("filters")){
			
			return false;	
		}
		$cols=$datagrid->columns->get_items();
		//var_dump($dataItem->get_data());
		//die("setColsUserPrefs");
		foreach($cols as $col){
			if($col->userColsFiltersRememberEnabled){

				$cod=$col->cod;
				$datacod="cols.".$cod;
				
				
				if($dataItem->is_data_defined($datacod.".filterValue")){
						
					if($col->isDate()){
						if($filterValue=$dataItem->get_data($datacod.".filterValue")){
							if(is_string($filterValue)){
								if($filterValueTime=strtotime($filterValue)){
									$filterValue=date("Y-m-d",$filterValueTime);
									$filterValueObj=new mwmod_mw_jsobj_date($filterValue);
									$col->js_data->set_prop("filterValue",$filterValueObj);
								}
							}
						}

					}else{
						$col->js_data->set_prop("filterValue",$dataItem->get_data($datacod.".filterValue"));
					}
						

					if($dataItem->is_data_defined($datacod.".selectedFilterOperation")){
						$col->js_data->set_prop("selectedFilterOperation",$dataItem->get_data($datacod.".selectedFilterOperation"));

					}
				}
					


					

				
			}
			
			
		}
		

	}
	function setColsUserPrefsColsOptions($datagrid){
		if(!$this->userColsSelectedRememberEnabled){
			return false;	
		}
		if(!$dataItem=$this->get_user_ui_data("colsstate")){
			
			return false;	
		}
		$cols=$datagrid->columns->get_items();
		//var_dump($dataItem->get_data());
		//die("setColsUserPrefs");
		foreach($cols as $col){
			if($col->userColsSelectedRememberEnabled){

				$cod=$col->cod;
				$datacod="cols.".$cod;
				
				if($this->userColsOrderRememberEnabled){
					if($dataItem->is_data_defined($datacod.".sortIndex")){
						if($dataItem->is_data_defined($datacod.".sortOrder")){
							$col->js_data->set_prop("sortIndex",$dataItem->get_data($datacod.".sortIndex"));
							$col->js_data->set_prop("sortOrder",$dataItem->get_data($datacod.".sortOrder"));

						}

					}
					if($dataItem->is_data_defined($datacod.".width")){
						$col->js_data->set_prop("width",$dataItem->get_data($datacod.".width"));

					}
					
				}


				if($this->userColsSelectedRememberEnabledVisible){
					
					
					if($dataItem->is_data_defined($datacod.".visible")){
						//die($datacod);
						if($dataItem->get_data($datacod.".visible")){
							$col->js_data->set_prop("visible",true);
						}else{
							$col->js_data->set_prop("visible",false);
						}
						
					}
					
				}

				if($this->userColsSelectedRememberEnabledVisibleIndex()){
					if($dataItem->is_data_defined($datacod.".visibleIndex")){
						$vv=$dataItem->get_data($datacod.".visibleIndex");
						if(is_numeric($vv)){
							$col->js_data->set_prop("visibleIndex",$vv+0);
						}
						
						
					}

				}
			}
			
			
		}
		

	}
	//20210217
	 /**
     * @param mwmod_mw_devextreme_widget_datagrid $datagrid
     * @param mwmod_mw_devextreme_widget_datagrid_helper_dgman $gridhelper
     */
	function afterDatagridCreatedAddDefaultToolbarItems($datagrid,$gridhelper){
		$var=$this->get_js_ui_man_name();
		
		if($this->columnsChooserEnabled){
			//$datagrid->set_prop("columnsChooserEnabled",true);
			$datagrid->js_props->set_prop("columnChooser.enabled",true);
			$datagrid->js_props->set_prop("columnChooser.mode","select");
			$datagrid->js_props->set_prop("allowColumnReordering",true);
			
		}

		$cusBtnsEnabled=false;
		if($this->userColsSelectedRememberEnabled){
			$cusBtnsEnabled=true;
		}
		if($this->clearFiltersBtnEnabled){
			$cusBtnsEnabled=true;
		}
		
		if($this->rememberFiltersBtnEnabled){
			$cusBtnsEnabled=true;
		}
		if($this->excelExportName){
			$cusBtnsEnabled=true;
		}
			


		
		if($cusBtnsEnabled){
			$tollbarItems=$datagrid->js_props->get_array_prop("toolbar.items");
			if($this->clearFiltersBtnEnabled){
				$this->addBtnClearFilters($datagrid,$gridhelper);
			}
			if($this->rememberFiltersBtnEnabled){
				$this->addBtnSaveFilters($datagrid,$gridhelper);
			}

			if($this->allowInsert()){
				$tollbarItems->add_data("addRowButton");
			}			
			if($this->columnsChooserEnabled){
				$tollbarItems->add_data("columnChooserButton");
			}
			if($this->userColsPrefResetBtnEnabled){
				$this->addBtnColsPrefReset($datagrid,$gridhelper);
			}			
			if($this->excelExportName){

				if(!$this->toolbarItemsExportButtonAdded){
					$tollbarItems->add_data("exportButton");
					$this->toolbarItemsExportButtonAdded=true;
					
				}
			}
			

			

		}

	}
	/**
	 * @param mwmod_mw_devextreme_widget_datagrid $datagrid
	 * @param mwmod_mw_devextreme_widget_datagrid_helper_dgman $gridhelper
	 */
	function afterDatagridCreated($datagrid,$gridhelper){
		$this->afterDatagridCreatedAddDefaultToolbarItems($datagrid,$gridhelper);
			
			
	}
	function addBtnClearFilters($datagrid,$gridhelper){
		$var=$this->get_js_ui_man_name();
		$tollbarItems=$datagrid->js_props->get_array_prop("toolbar.items");
		
		$btn=$tollbarItems->add_data_obj();
		
		$btn->set_prop("locateInMenu","auto");
		$btn->set_prop("widget","dxButton");
		$btn->set_prop("options.text",$this->lng_get_msg_txt("clearFilters","Quitar filtros"));
		$btn->set_prop("options.icon","meralda-icon meralda-icon-filter-clear");
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_cont("{$var}.clearFilters();");
		$btn->set_prop("options.onClick",$fnc);

	}
	function addBtnSaveFilters($datagrid,$gridhelper){
		$var=$this->get_js_ui_man_name();
		$tollbarItems=$datagrid->js_props->get_array_prop("toolbar.items");
		
		$btn=$tollbarItems->add_data_obj();
		
		$btn->set_prop("locateInMenu","auto");
		$btn->set_prop("widget","dxButton");
		$btn->set_prop("options.text",$this->lng_get_msg_txt("rememberFilters","Recordar filtros"));
		$btn->set_prop("options.icon","meralda-icon meralda-icon-filter-save");
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_cont("{$var}.saveFilters();");
		$btn->set_prop("options.onClick",$fnc);

	}
	function addBtnColsPrefReset($datagrid,$gridhelper){
		$var=$this->get_js_ui_man_name();
		$tollbarItems=$datagrid->js_props->get_array_prop("toolbar.items");
		
		$btn=$tollbarItems->add_data_obj();
		$btn->set_prop("locateInMenu","auto");
		$btn->set_prop("widget","dxButton");
		$btn->set_prop("options.text",$this->lng_get_msg_txt("resetColumns","Restablecer columnas"));
		$btn->set_prop("options.icon","undo");
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_cont("{$var}.resetUserPrefCols();");
		$btn->set_prop("options.onClick",$fnc);

	}
	 /**
     * @param mwmod_mw_devextreme_widget_datagrid $datagrid
     */
	function add_cols($datagrid){
		$col=$datagrid->add_column_number("id","ID");
		$col->js_data->set_prop("width",60);
		$col->js_data->set_prop("allowEditing",false);
		$col->js_data->set_prop("visible",false);
		$col=$datagrid->add_column_string("name",$this->lng_common_get_msg_txt("name","Nombre"));
		
		
			
	}
	
	/**
	 * Indicates whether this interface is responsible for managing subinterface menus.
	 *
	 * @return bool Always returns false for data table interfaces.
	 */
	function is_responsable_for_sub_interface_mnu(){
		return false;	
	}
	

	/**
	 * Executes when no subinterface is active.
	 *
	 * Override in child classes to handle non-subinterface execution.
	 *
	 * @return void
	 */
	function do_exec_no_sub_interface(){
		

	}
	

}
?>