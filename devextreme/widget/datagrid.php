<?php
/**
 * Class representing a DevExtreme DataGrid widget wrapper.
 * Provides methods to configure columns, data, and interaction
 * with Meralda UI and backend.
 */
class mwmod_mw_devextreme_widget_datagrid extends mwmod_mw_devextreme_widget_widget{
	private $columns;
	private $_data=array();

	public $nextHidingPriority=0;
	var $dont_rewrite_dataSourceProp=false;
	var $dont_rewrite_columnsProp=false;
	/**
	 * Initializes the DataGrid widget with an optional container ID.
	 *
	 * @param string $name Optional container ID for the DataGrid.
	 */
	function __construct($name="dxdatagrid"){
		$this->set_container_id($name);
		if($f=$this->get_formatter()){
			$f->format_datagrid($this);	
		}
	}
	/**
	 * Generates a JavaScript helper for the DataGrid using a query.
	 *
	 * @param object $query The query object.
	 * @param bool $add_cols Whether to include columns from the query.
	 * @return object JavaScript helper object.
	 */
	function new_mw_helper_js_from_query($query,$add_cols=true){
		$this->set_cols_and_data_from_query($query,$add_cols);
		$gridhelper=$this->new_mw_helper_js();
		if($add_cols){
			$this->add_cols2helper($gridhelper);	
		}
		$this->add_data2helper($gridhelper);
		return $gridhelper;
		
	}
	/**
	 * Adds internal data to a helper JavaScript object.
	 *
	 * @param object $helper Helper object to receive data.
	 * @return bool True if data was added.
	 */
	function add_data2helper($helper){
		if(!$doptiom=$this->get_data_as_doptim()){
			return false;	
		}
		$helper->set_prop("dsoptim",$doptiom);
		return true;
	}
	/**
	 * Adds column definitions to the JavaScript helper.
	 *
	 * @param object $helper Helper object to receive column definitions.
	 * @return bool True if columns were added.
	 */
	function add_cols2helper($helper){
		$list=$helper->get_array_prop("columns");
		$this->__get_priv_columns();
		if(!$columns=$this->columns->get_items()){
			return false;	
		}
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$list->add_data($coljs);
		}
		return true;

	}
	function set_cols_and_data_from_query($query,$add_cols=true){
		//just for debugging
		if(!$rows=$query->get_array_data_from_sql_by_index()){
			return false;	
		}
		foreach($rows as $row){
			if($add_cols){
				foreach($row as $c=>$v){
					$this->add_column_string($c,$c);	
				}
				reset($row);
				
				$add_cols=false;	
			}
			$this->add_data($row);
		}
		return true;
		
		
	}

	
	function new_dataoptim_data_man($man=false){
		if(!$man){
			$man=new mwmod_mw_jsobj_dataoptim();	
		}
		$this->__get_priv_columns();
		if(!$columns=$this->columns->get_items()){
			return $man;
		}
		foreach($columns as $col){
			if($f=$col->new_dataoptim_field()){
				$man->add_data_field($f);	
			}
		}
		return $man;
		
	}
	function set_js_ui_events($cods,$uijsname="ui",$eventvarname="e"){
		if(is_string($cods)){
			$cods=explode(",",$cods);	
		}
		if(!is_array($cods)){
			return false;	
		}
		$r=array();
		foreach($cods as $cod){
			if($cod=trim($cod)){
				if($fnc=$this->set_js_ui_event($cod,$uijsname,$eventvarname)){
					$r[$cod]=$fnc;	
				}
			}
				
		}
		return $r;
	}
	function set_js_ui_event($cod,$uijsname="ui",$eventvarname="e"){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;	
		}
		if(!$cod=trim($cod)){
			return false;	
		}
		$uifnc_cod=$this->get_js_event_cod_with_pref($cod);
		$fnc=new mwmod_mw_jsobj_function($uijsname.".".$uifnc_cod."(".$eventvarname.")");
		$fnc->add_fnc_arg("e");
		$this->set_js_event($cod,$fnc);
		return $fnc;

			
	}
	function set_js_event($cod,$jsfnc){
		$cod=$this->get_js_event_cod($cod);
		$this->js_props->set_prop($cod,$jsfnc);
	}
	function get_js_event_cod($cod){
		//return $cod;
		
		//habilitar si se actualiza versión de librería
		return $this->get_js_event_cod_with_pref($cod);
	}
	function get_js_event_cod_with_pref($cod){
		return "on".strtoupper(substr($cod,0,1)).substr($cod,1);
	}
	//nueva
	function mw_helper_js_set_rdata_mode_from_ui($ui,$gridhelper=false){
		if(!$gridhelper){
			$gridhelper=$this->new_mw_helper_js("mw_devextreme_datagrid_man_rdata");	
		}
		$gridhelper->set_fnc_name("mw_devextreme_datagrid_man_rdata");
		if($url=$ui->get_exec_cmd_sxml_url_ifEnabled("loaddata")){
			$gridhelper->set_prop("dataSourceMan.loadDataURL",$url);
		}
		if($url=$ui->get_exec_cmd_sxml_url_ifEnabled("newitem")){
			//$gridhelper->set_prop("dataSourceMan.newItemURL",$url);
			$gridhelper->set_prop("newItemURL",$url);

		}
		if($url=$ui->get_exec_cmd_sxml_url_ifEnabled("deleteitem")){
			//$gridhelper->set_prop("dataSourceMan.deleteItemURL",$url);
			$gridhelper->set_prop("deleteItemURL",$url);
		}
		if($url=$ui->get_exec_cmd_sxml_url_ifEnabled("saveitem")){
			//$gridhelper->set_prop("dataSourceMan.saveItemURL",$url);
			$gridhelper->set_prop("saveItemURL",$url);
		}
		return $gridhelper;

		
	}
	
	function mw_helper_js_set_editrow_mode_from_ui($ui,$gridhelper=false,$insert=true,$delete=true,$update=true){
		if(!$gridhelper){
			$gridhelper=$this->new_mw_helper_js("mw_devextreme_datagrid_man_editrowmode");	
		}
		$gridhelper->set_fnc_name("mw_devextreme_datagrid_man_editrowmode");
		if($insert){
			$url=$ui->get_exec_cmd_sxml_url("newitem");
			$gridhelper->set_prop("newItemURL",$url);
		}
		if($delete){
			$url=$ui->get_exec_cmd_sxml_url("deleteitem");
			$gridhelper->set_prop("deleteItemURL",$url);
		}
		if($update){
			$url=$ui->get_exec_cmd_sxml_url("saveitem");
			$gridhelper->set_prop("saveItemURL",$url);
		}
		return $gridhelper;

		
	}
	function new_mw_helper_js($classname="mw_devextreme_datagrid_man",$set_now_rewrite_options=true){
		if($set_now_rewrite_options){
			$this->dont_rewrite_columnsProp=true;	
			//$this->dont_rewrite_dataSourceProp=true;	
		}
		//$man= new mwmod_mw_jsobj_newobject($classname);
		$man= new mwmod_mw_devextreme_widget_datagrid_helper_dgman($this,$classname);
		$man->set_prop("gridname",$this->container_id);
		$man->set_prop("gridoptions",$this->js_props);
		return $man;
		
	}

	function prepare_js_props(){
		if(!$this->dont_rewrite_columnsProp){

			$cols=new mwmod_mw_jsobj_array();
			$this->__get_priv_columns();
			if($items=$this->columns->get_items()){
				foreach($items as $cod=>$item){
					$cols->add_data($item->get_ready_js_data());	
				}
			}
			$this->js_props->set_prop("columns",$cols);	
		}
		if(!$this->dont_rewrite_dataSourceProp){
			$datajs=$this->get_data_as_js_array();
			$this->js_props->set_prop("dataSource",$datajs);	
		}
		$this->js_props->set_prop("syncLookupFilterValues",false);

		///$this->js_props->set_prop("toolbar.items.addRowButton.options.text",$this->lng_get_msg_txt("createNew","Crear nuevo"));

		
		//$datagrid->js_props->set_prop("syncLookupFilterValues",false);

			
	}
	function get_data_as_doptim(){
		$datajs= new mwmod_mw_jsobj_dataoptim();	
		if(!$data_list=$this->get_data()){
			return $datajs;
		}
		$this->__get_priv_columns();
		if(!$columns=$this->columns->get_items()){
			return $datajs;
		}
		foreach($data_list as $d){
			if(is_array($d)){
				$new=array();
				reset($columns);
				foreach($columns as $cod=>$col){
					$new[$cod]=$col->get_value($d[$cod]);	
				}
				$datajs->add_data($new);
			}
		}
		return $datajs;
	
		
	}
	function get_data_as_js_array(){
		$datajs=new mwmod_mw_jsobj_array();
		if(!$data_list=$this->get_data()){
			return $datajs;
		}
		$this->__get_priv_columns();
		if(!$columns=$this->columns->get_items()){
			return $datajs;
		}
		foreach($data_list as $d){
			if(is_array($d)){
				$new=array();
				reset($columns);
				foreach($columns as $cod=>$col){
					$new[$cod]=$col->get_value($d[$cod]);	
				}
				$datajs->add_data($new);
			}
		}
		return $datajs;
	
		
	}
	
	function setColumnFixing(){
		$this->js_props->set_prop("columnFixing.enabled",true);	
		$this->js_props->set_prop("columnFixing.texts.fix",$this->lng_get_msg_txt("fasten","Fijar"));	
		$this->js_props->set_prop("columnFixing.texts.unfix",$this->lng_get_msg_txt("unfix","Desfijar"));	
		$this->js_props->set_prop("columnFixing.texts.leftPosition",$this->lng_get_msg_txt("to_the_left","A la izquierda"));	
		$this->js_props->set_prop("columnFixing.texts.rightPosition",$this->lng_get_msg_txt("to_the_right","A la derecha"));	
		
			
	}
	
	function setGroupingMsgs(){
		$this->js_props->set_prop("grouping.groupContinuedMessage",$this->lng_get_msg_txt("groupContinuedMessage","Continúa de la página anterior"));	
		$this->js_props->set_prop("grouping.groupContinuesMessage",$this->lng_get_msg_txt("groupContinuesMessage","Continúa en la página siguiente"));	
		
			
	}
	
	function enableExport($filename="data"){
		$url=$this->devextreme_man->get_exec_cmd_url("export",false,$filename.".xlsx");
		$this->js_props->set_prop("export.enabled",true);
		$this->js_props->set_prop("export.proxyUrl",$url);
		$this->js_props->set_prop("export.fileName",$filename);
		$this->js_props->set_prop("export.texts.exportToExcel",$this->lng_get_msg_txt("exportToExcel","Exportar a Excel"));
		
		
			
			
	}
	function setDefaultJSProps($js_props){
		
		$js_props->set_prop("syncLookupFilterValues",false);
	}
	
	
	function setFilerVisible(){
		$this->js_props->set_prop("filterRow.visible",true);	
			
	}
	 /**
     * Adds a number column to the grid.
     * @param string $cod Column code
     * @param string $lbl Label for the column
     * @return mwmod_mw_devextreme_widget_datagrid_column_number
     */
	function add_column_number($cod,$lbl=false){
		$item=new mwmod_mw_devextreme_widget_datagrid_column_number($cod,$lbl);
		return $this->add_column($item);
	}
	/**
     * Adds a date column to the grid.
     * @param string $cod Column code
     * @param string|null $lbl Label for the column
     * @return mwmod_mw_devextreme_widget_datagrid_column_date
     */
	function add_column_date($cod,$lbl=false){
		$item=new mwmod_mw_devextreme_widget_datagrid_column_date($cod,$lbl);
		return $this->add_column($item);
	}
	/**
     * Adds a string/text column to the grid.
     * @param string $cod Column code
     * @param string|null $lbl Label for the column
     * @return mwmod_mw_devextreme_widget_datagrid_column_string
     */
	function add_column_string($cod,$lbl=false){
		$item=new mwmod_mw_devextreme_widget_datagrid_column_string($cod,$lbl);
		return $this->add_column($item);
	}
	
    /**
     * Adds a boolean (true/false) column to the grid.
     * @param string $cod Column code
     * @param string|null $lbl Label for the column
     * @return mwmod_mw_devextreme_widget_datagrid_column_boolean
     */
	function add_column_boolean($cod,$lbl=false){
		$item=new mwmod_mw_devextreme_widget_datagrid_column_boolean($cod,$lbl);
		return $this->add_column($item);
	}
	 /**
     * Adds a boolean column to the grid that shows textual representation instead of true/false.
     * @param string $cod Column code
     * @param string|null $lbl Label for the column
     * @return mwmod_mw_devextreme_widget_datagrid_column_booleantxt
     */
	function add_column_boolean_txt($cod,$lbl=false){
		$item=new mwmod_mw_devextreme_widget_datagrid_column_booleantxt($cod,$lbl);
		return $this->add_column($item);
	}
	/**
     * Adds an HTML column to the grid.
     * @param string $cod Column code
     * @param string|null $lbl Label for the column
     * @return mwmod_mw_devextreme_widget_datagrid_column_html
     */
	function add_column_html($cod,$lbl=false){
		$item=new mwmod_mw_devextreme_widget_datagrid_column_html($cod,$lbl);
		return $this->add_column($item);
	}
	/**
     * Adds a menu column to the grid (usually for action buttons).
     * @param string $cod Column code
     * @param string|null $lbl Label for the column
     * @return mwmod_mw_devextreme_widget_datagrid_column_mnu
     */
	function add_column_mnu($cod,$lbl=false){
		$item=new mwmod_mw_devextreme_widget_datagrid_column_mnu($cod,$lbl);
		//$item=new mwmod_mw_devextreme_widget_datagrid_column_string($cod,$lbl);
		return $this->add_column($item);
	}
	/**
     * Adds a band column to the grid, useful for grouping other columns visually.
     * @param string $cod Column code
     * @param string|null $lbl Label for the column
     * @return mwmod_mw_devextreme_widget_datagrid_column_band
     */
	function add_column_band($cod,$lbl=false){
		$item=new mwmod_mw_devextreme_widget_datagrid_column_band($cod,$lbl);
		
		return $this->add_column($item);
	}
	//20210217
	function get_column_index($itemOrCod){
		if(is_object($itemOrCod)){
			$cod=$itemOrCod->cod;	
		}else{
			$cod=$itemOrCod;	
		}
		$this->__get_priv_columns();
		if($columns=$this->columns->get_items()){
			$x=0;
			foreach($columns as $c=>$col){
				if($col->cod==$cod){
					return $x;
				}
				$x++;	
			}
		}
		return -1;
	}
	/**
     * Adds a new column object to the grid.
     * @param mwmod_mw_devextreme_widget_datagrid_column $item
     * @return mwmod_mw_devextreme_widget_datagrid_column
     */
	function add_column($item){
		$this->__get_priv_columns();
		$index=0;
		if($columns=$this->columns->get_items()){
			$index=sizeof($columns);
		}
		$item->dataGrid=$this;
		$item->index=$index;

		return $this->columns->add_item($item);
	}
	function add_data_from_list($data){
		if(!is_array($data)){
			return false;	
		}
		foreach($data as $d){
			$this->add_data($d);	
		}
			
	}
	final function get_data(){
		return $this->_data;
	}
	final function add_data($data){
		if(!is_array($data)){
			return false;	
		}
		$this->_data[]=$data;
		return true;
		/*
		$this->__get_priv_columns();
		if($items=$this->columns->get_items()){
				
		}
		*/
	}
	function get_js_widget_fnc_name(){
		return "dxDataGrid";	
	}
	function setColsAutoHidingPriority($colsCods,$reverse=true){
		if(!is_array($colsCods)){
			$colsCods=explode(",",$colsCods);	
		}
		if(!is_array($colsCods)){
			return false;	
		}
		if($reverse){
			$colsCods=array_reverse($colsCods);	
		}
		
		foreach($colsCods as $cod){
			if($cod=trim($cod)){
				$this->setColHidingPriority($cod);
				
			}
				
		}
		return true;
		
	}
	function setColHidingPriority($col,$priority=false){
		if(!$col){
			return false;
		}
		if(is_string($col)){
			$col=$this->get_col($col);	
		}
		
		if(!is_object($col)){
			return false;	
		}
		if($priority===false){
			$priority=$this->nextHidingPriority;	
			$this->nextHidingPriority++;
		}
		$col->js_data->set_prop("hidingPriority",$priority);
		return true;
		
	}

	function get_col($cod){
		$this->__get_priv_columns();
		
		return $this->columns->get_item($cod);
	}
	
	final function __get_priv_columns(){
		if(!isset($this->columns)){
			$this->columns=new mwmod_mw_devextreme_elemslist();	
		}
		return $this->columns;
	}
	
	
}
?>