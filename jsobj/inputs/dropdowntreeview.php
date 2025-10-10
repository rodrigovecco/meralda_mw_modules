<?php
class mwmod_mw_jsobj_inputs_dropdowntreeview extends mwmod_mw_jsobj_inputs_input{
	
	function __construct($cod,$objclass=false){
		$this->def_js_class="mw_datainput_item_DX_dropDownTreeView";
		$this->init_js_input($cod,$objclass);
		
	}
	function add_select_option($cod,$name,$level=0,$parentId=null){

		$valueExpr=$this->get_prop("DXOptions.valueExpr");
		if(!$valueExpr){
			$valueExpr="id";
			$this->set_prop("DXOptions.valueExpr",$valueExpr);
		}
		$displayExpr=$this->get_prop("DXOptions.displayExpr");
		if(!$displayExpr){
			$displayExpr="name";
			$this->set_prop("DXOptions.displayExpr",$displayExpr);
		}
		$list=$this->get_array_prop("DXOptions.dataSource");
		
		return $list->add_data(array($valueExpr=>$cod,$displayExpr=>$name,"level"=>$level,"parentId"=>$parentId));
		
	}
	function setOnlyLeafSelectable($v=true){
		$this->set_prop("DXOptions.onlyLeafSelectable",$v);
		
	}
	function setLevel($level=0){
		$this->set_prop("DXOptions.level",$level);
	}
	function setDataSource($datasourceObj,$valueExpr="id",$displayExpr="name"){
		// Datos para el DropDownTreeView
		if(is_array($datasourceObj)){
			$plainlist=$datasourceObj;
			$datasourceObj = new mwmod_mw_jsobj_array();
			foreach($plainlist as $id=>$d){
				
				$dd=new mwmod_mw_jsobj_obj();
				$dd->set_prop($valueExpr, $id);
				$dd->set_prop($displayExpr, $dd);
				$dd->set_prop("level", 0);

				$datasourceObj->add_data($dd);
			}

			





		}
		
		if(!$datasourceObj){
			$datasourceObj = new mwmod_mw_jsobj_array();
			return;
		}


		$this->set_prop("DXOptions.dataSource", $datasourceObj);
		$this->set_prop("DXOptions.valueExpr",$valueExpr);
		$this->set_prop("DXOptions.displayExpr",$displayExpr);
		return $datasourceObj;
	
	}
	
	function setMultipleMode(){
		$this->set_prop("DXOptions.treeViewOptions.selectionMode","multiple");	
		$this->set_prop("DXOptions.treeViewOptions.showCheckBoxesMode","normal");
		$this->set_prop("DXOptions.treeViewOptions.selectNodesRecursive",true);
		$this->set_prop("DXOptions.treeViewOptions.expandAllEnabled",true);
		//level???
	}
	function setLevelIndex($index=0){
		//no probado, verificar con js
		$this->set_prop("DXOptions.levelIndex",$index);
	}
	function setPlaceholder($txt){
		$this->set_prop("DXOptions.placeholder",$txt);
	}
	
}
?>