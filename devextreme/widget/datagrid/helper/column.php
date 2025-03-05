<?php
class mwmod_mw_devextreme_widget_datagrid_helper_column extends mwmod_mw_jsobj_newobject{
	//var $lookup;
	var $cod;
	var $mw_js_column_class="mw_devextreme_datagrid_column";
	var $column_params;
	var $column_options;
	
	function __construct($cod,$lbl=false,$dataType=false,$dataField=NULL){
		$this->init_column($cod,$lbl,$dataType,$dataField);
	}
	function set_param($cod,$val){
		return $this->column_params->set_prop($cod,$val);
		
	}
	function set_option($cod,$val){
		return $this->column_options->set_prop($cod,$val);
		
	}
	
	
	function init_column($cod,$lbl=false,$dataType=false,$dataField=NULL){
		$this->cod=$cod;
		if(is_null($dataField)){
			$dataField=$cod;	
		}
		$this->set_fnc_name($this->mw_js_column_class);
		$this->column_params=new mwmod_mw_jsobj_obj();
		$this->column_options=new mwmod_mw_jsobj_obj();
		if($dataField){
			$this->column_options->set_prop("dataField",$dataField);		
		}
		if($dataType){
			$this->column_options->set_prop("dataType",$dataType);	
		}
		if($lbl){
			$this->column_options->set_prop("caption",$lbl);	
		}
		$this->column_params->set_prop("options",$this->column_options);
		$this->args_as_list=true;
		$p=array(
			"cod"=>$this->cod,
			"params"=>$this->column_params,
			
		);
		$this->set_props($p);	
		
		
	}
	
	
	
}
?>