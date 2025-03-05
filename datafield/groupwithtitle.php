<?php
class mwmod_mw_datafield_groupwithtitle extends mwmod_mw_datafield_group{
	
	
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		$this->set_title_mode();
	}
	function new_js_mw_datainput(){
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_groupwithtitle");	
		$jsinput->set_prop("cod",$this->get_cod());
		$jsinput->set_prop("lbl",$this->get_lbl());
		if(is_array($this->items)){
			reset($this->items);
			$children=new mwmod_mw_jsobj_array();
			$jsinput->set_prop("childrenList",$children);
			
			foreach ($this->items as $i){
				if($ch=$i->get_js_mw_datainput()){
					$children->add_data($ch);	
				}

			}
			reset($this->items);
			
		}
		//$jsinput->set_prop("lbl",$this->get_lbl());
		return $jsinput;
		
	}


	
}
?>