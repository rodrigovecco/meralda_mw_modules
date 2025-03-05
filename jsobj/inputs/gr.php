<?php
class mwmod_mw_jsobj_inputs_gr extends mwmod_mw_jsobj_inputs_def{
	function __construct($cod,$type=false,$def_js_class_pref=false){
		$this->def_js_class="mw_datainput_item_group";
		$this->def_js_class_type="group";
		if($def_js_class_pref){
			$this->setJSClassPref($def_js_class_pref);
		}
		$this->init_js_input_type_mode($cod,$type);
	}
	function setTitleMode($lbl=false,$type="groupwithtitle"){
		if(!$type){
			$type="groupwithtitle";	
		}
		$this->set_js_type($type);
		if($lbl){
			$this->set_prop("lbl",$lbl);	
		}
		
	}
	//20240204
	function distributeChildrenInGrid($colsNum=2,$type="group_btGrid"){
		$colSpan=12/$colsNum;
		if($type){
			$this->set_js_type($type);
		}
		
		$rows=$this->get_array_prop("btGrid.rows");
		
		$rowIndex=1;
		$colIndex=1;
		$row=$rows->add_data_obj();
		$cols=$row->get_array_prop("cols");
		if($inputs=$this->get_children()){
			foreach($inputs as $input){
				if($colIndex>$colsNum){
					$colIndex=1;
					$rowIndex++;
					$row=$rows->add_data_obj();
					$cols=$row->get_array_prop("cols");

				}
				$col=$cols->add_data_obj();
				$col->set_prop("colSpan",$colSpan);

				$input->set_prop("parentGrid.row",$rowIndex);
				$input->set_prop("parentGrid.col",$colIndex);
				$colIndex++;
			}
		}

		
	


	}
}
?>