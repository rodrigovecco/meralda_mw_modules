<?php
class mwmod_mw_devextreme_widget_datagrid_helper_dgman extends mwmod_mw_jsobj_newobject{
	var $datagrid;
	function __construct($datagrid,$classname="mw_devextreme_datagrid_man"){
		$this->datagrid=$datagrid;
		$this->set_fnc_name($classname);
		$this->setDefOptions();
	}
	function setDefOptions(){
		$this->set_prop("toolbar.itemsDef.showText","allways");
		$this->set_prop("toolbar.items.addRowButton.options.text",$this->lng_get_msg_txt("createNew","Crear nuevo"));

	}
	//nuevas
	/*
	function addEventListener($cod,$fnc=false){
		
		if(!$fnc){
			$fnc=new mwmod_mw_jsobj_functionext();
			$fnc->add_fnc_arg("e");	
		}
		
		$this->set_prop("events.$cod",$fnc);
		return $fnc;
	}
	*/
	
	
	///
	function addLookupDOptim($cod){
		$list=$this->get_array_prop("lookupsDoptimList");
		$lookup= new mwmod_mw_jsobj_dataoptim($cod);
		$list->add_data($lookup);
		return $lookup;
	
	}

	
	function addColumnsFromWidget($datagrid=false){
		if(!$datagrid){
			$datagrid=$this->datagrid;	
		}
		if(!$datagrid){
			return false;	
		}
		if(!$datagrid->columns){
			return false;	
		}
		if(!$columns=$datagrid->columns->get_items()){
			return false;	
		}
		$list=$this->get_array_prop("columns");
		foreach($columns as $col){
			$coljs=$col->get_mw_js_colum_obj();
			$list->add_data($coljs);
			
		}
		return $list;

		
	}
	
	
	
}
?>