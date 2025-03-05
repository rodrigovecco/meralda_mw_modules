<?php
class mwmod_mw_devextreme_widget_datagrid_column extends mwmod_mw_devextreme_elem{
	var $lookup;
	var $lookup_ds;
	var $mw_js_colum_class="mw_devextreme_datagrid_column";
	var $mw_js_colum_obj;
	var $mw_js_colum_obj_params;
	var $lookupOptionsList;
	public $dataGrid;
	
	public $index=0;
	function __construct($cod,$lbl=false,$dataType=false){
		$this->init_column($cod,$dataType,$lbl);
	}
	function create_optionslist($options=false){
		if($options){
			if(is_a($options,"mwmod_mw_listmanager_listman")){
				$this->lookupOptionsList= $options; 	
				return 	$this->lookupOptionsList;
			}
		}
		
		$this->lookupOptionsList= new mwmod_mw_listmanager_listman($options); 	
		return 	$this->lookupOptionsList;
	}
	
	
	function params_set_prop($cod,$val){
		$this->get_mw_js_colum_obj_params();
		$this->mw_js_colum_obj_params->set_prop($cod,$val);	
	}
	function set_link_mode($baseurl,$varargs=array(),$args=array(),$target=false,$enable=true,$class="mw_devextreme_datagrid_column_link"){
		$this->get_mw_js_colum_obj_params();
		$this->mw_js_colum_obj_params->set_prop("link_mode.url",$baseurl);
		$this->mw_js_colum_obj_params->set_prop("link_mode.varargs",$varargs);
		$this->mw_js_colum_obj_params->set_prop("link_mode.args",$args);
		$this->mw_js_colum_obj_params->set_prop("link_mode.target",$target);
		$this->mw_js_colum_obj_params->set_prop("link_mode.enable",$enable);
		$this->set_mw_js_colum_class($class);
	}
	function set_mw_js_colum_class($class){
		$this->mw_js_colum_class=$class;
		if(isset($this->mw_js_colum_obj)){
			$this->mw_js_colum_obj->set_fnc_name($class);
			
		}
		
	}
	function get_mw_js_colum_obj(){
		if(isset($this->mw_js_colum_obj)){
			return $this->mw_js_colum_obj;	
		}
		$this->get_mw_js_colum_obj_params();
		$p=array(
			"cod"=>$this->cod,
			"params"=>$this->mw_js_colum_obj_params,
			
		);
		$this->mw_js_colum_obj=new mwmod_mw_jsobj_newobject($this->mw_js_colum_class,$p,true);
		return $this->mw_js_colum_obj;
	}
	function get_mw_js_colum_obj_params(){
		if(isset($this->mw_js_colum_obj_params)){
			return $this->mw_js_colum_obj_params;	
		}
		
		$this->mw_js_colum_obj_params=new mwmod_mw_jsobj_obj();
		$this->mw_js_colum_obj_params->set_prop("options",$this->js_data);
		return $this->mw_js_colum_obj_params;	
	}
	
	function new_dataoptim_field(){
		$f=new mwmod_mw_jsobj_dataoptim_field($this->cod);
		$this->set_dataoptim_field($f);
		return $f;	
	}
	function set_dataoptim_field($field){
			
	}
	function set_lookup_from_man($cod,$valueExpr,$displayExpr){
		$this->get_mw_js_colum_obj_params();
		$this->mw_js_colum_obj_params->set_prop("lookupFromMan.cod",$cod);
		$this->mw_js_colum_obj_params->set_prop("lookupFromMan.valueExpr",$valueExpr);
		$this->mw_js_colum_obj_params->set_prop("lookupFromMan.displayExpr",$displayExpr);
		
	}
	function set_lookup_from_man_filter_for_edit_compareDataMode($cod,$datacod,$valueExpr="cod",$displayExpr="name"){
		$fnc=$this->set_lookup_from_man_filter_for_edit($cod,$valueExpr,$displayExpr);
		$fnc->add_cont('if(data["'.$datacod.'"]==optionData["'.$datacod.'"]){return true}else{return false}');
		return $fnc;
	}
	function set_lookup_from_man_filter_for_edit_compareDataModeAndSelected($cod,$datacod,$valueExpr="cod",$displayExpr="name"){
		$fnc=$this->set_lookup_from_man_filter_for_edit($cod,$valueExpr,$displayExpr);
		
		$ifiscurrent="";
		$fnc->add_cont('if(data["'.$datacod.'"]==optionData["'.$datacod.'"]){return true}'."\n");
		$fnc->add_cont('if(data["'.$valueExpr.'"]==optionData["'.$valueExpr.'"]){return true}'."\n");
		$fnc->add_cont('return false'."\n");
		return $fnc;
	}
	function set_lookup_from_man_filter_for_edit($cod,$valueExpr="cod",$displayExpr="name"){
		$this->set_mw_js_colum_class("mw_devextreme_datagrid_column_with_filter_lookup");
		
		$this->set_lookup_from_man($cod,$valueExpr,$displayExpr);
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_fnc_arg("data");
		$fnc->add_fnc_arg("optionData");
		$this->mw_js_colum_obj_params->set_prop("lookupFromMan.filterForEdit",$fnc);
		return $fnc;
	}

	
	function set_lookup_and_add_options($list,$valueExpr="cod",$displayExpr="name",$extrakeys=false){
		$lu=$this->set_lookup($valueExpr,$displayExpr);
		$oplist=$this->create_optionslist($list);
		$oplist->add_items_to_js_array($lu,$valueExpr,$displayExpr);
		
	}
	function set_lookup($valueExpr,$displayExpr){
		$this->lookup=new mwmod_mw_jsobj_obj();
		$this->lookup->set_prop("valueExpr",$valueExpr);
		$this->lookup->set_prop("displayExpr",$displayExpr);

		$this->lookup_ds=new mwmod_mw_jsobj_array();
		$this->lookup->set_prop("dataSource",$this->lookup_ds);
		$this->js_data->set_prop("lookup",$this->lookup);	
		return $this->lookup_ds;
		
		
	}
	function get_value($val){
		return $val;	
	}
	function init_column($cod,$dataType=false,$lbl=false){
		$this->set_cod($cod);
		$this->js_data->set_prop("dataField",$cod);	
		if($dataType){
			$this->js_data->set_prop("dataType",$dataType);	
		}
		if($lbl){
			$this->js_data->set_prop("caption",$lbl);	
		}
		$this->js_data->set_prop("name",$cod);
	}
	
	
	
}
?>