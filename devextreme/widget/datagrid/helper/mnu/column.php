<?php
class mwmod_mw_devextreme_widget_datagrid_helper_mnu_column extends mwmod_mw_devextreme_widget_datagrid_helper_column{
	var $js_mnu_items_list;
	function __construct($cod="mnu",$lbl=" ",$dataField=false,$dataType="string"){
		$this->mw_js_column_class="mw_devextreme_datagrid_column_mnu";
		$this->init_column($cod,$lbl,$dataType,$dataField);
		$this->set_option("width",30);
		$this->set_option("allowFiltering",false);
		$this->set_option("allowSorting",false);
		$this->set_option("allowHiding",false);
		$this->set_option("allowReordering",false);
		$this->set_option("allowEditing",false);
		
		$this->js_mnu_items_list=new mwmod_mw_jsobj_array();
		$this->set_param("mnuitems",$this->js_mnu_items_list);
		
		

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