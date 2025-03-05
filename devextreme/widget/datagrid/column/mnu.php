<?php
class mwmod_mw_devextreme_widget_datagrid_column_mnu extends mwmod_mw_devextreme_widget_datagrid_column{
	function __construct($cod,$lbl=false){
		$this->init_column($cod,"string",$lbl);
		$this->mw_js_colum_class="mw_devextreme_datagrid_column_mnu";
		$this->setDefOptions();
	}
	function setDefOptions(){
		$this->get_mw_js_colum_obj_params();
		
		$this->js_data->set_prop("width",30);
		$this->js_data->set_prop("allowFiltering",false);
		$this->js_data->set_prop("allowSorting",false);
		$this->js_data->set_prop("allowHiding",false);
		$this->js_data->set_prop("allowReordering",false);
		$this->js_data->set_prop("allowEditing",false);
		
		$this->js_mnu_items_list=new mwmod_mw_jsobj_array();
		$this->mw_js_colum_obj_params->set_prop("mnuitems",$this->js_mnu_items_list);
	
	}
	function add_mnu_item($item){
		$this->js_mnu_items_list->add_data($item);
	}
	function new_mnu_item_edit($url,$urlvarname="iditem",$urlvalkey="id",$cod="edit",$jsclass="mw_devextreme_datagrid_column_mnu_item"){
		$iconclass="glyphicon glyphicon-pencil";
		$lnghelper=new mwmod_mw_lng_helper();
		$lbl=$lnghelper->lng_common_get_msg_txt("edit","Editar");
		$item= $this->new_mnu_item_def($cod,$iconclass,$lbl,$jsclass);
		$item->set_prop("href",$url);
		$item->set_prop("hrefvariables.".$urlvarname,$urlvalkey);
		
		
		
		return $item;	
	}
	
	function new_mnu_item_def($cod,$iconclass,$lbl,$jsclass="mw_devextreme_datagrid_column_mnu_item"){
		$item= $this->new_mnu_item($cod,$jsclass);
		$item->set_prop("iconClass",$iconclass);	
		$item->set_prop("lbl",$lbl);
		
		return $item;
	}

	
	function new_mnu_item($cod,$jsclass="mw_devextreme_datagrid_column_mnu_item"){
		$item= new mwmod_mw_jsobj_newobjectwidthcod($cod,$jsclass);
		return $item;	
	}

	
}
?>