<?php
class mwmod_mw_listmanager_listman extends mw_apsubbaseobj{
	public $has_null=true;
	public $items=array();
	public $items_by_value=array();
	function __construct($options=false){
		if($options){
			$this->add_items($options);	
		}
	}
	function get_debug_data(){
		$r["items"]=array();
		foreach ($this->items as $item){
			$r["items"][]=$item->get_debug_data();
		}
		return $r;
	
	}
	function get_item_others(){
		return 	$this->item_others;
	}
	function set_item_others($item=false){
		$this->item_others=$item;	
	}
	function add_items($options){
		if(!is_array($options)){
			return false;	
		}
		//complete_name
		foreach($options as $i=>$o){
			if(is_object($o)){
				if(is_a($o,"mwmod_mw_listmanager_item")){
					$this->add_item($o);
				}elseif(method_exists($o,"get_lbl_for_options_list")){
					if($lbl=$o->get_lbl_for_options_list()){
						$this->add_item_from_value_lbl($i,$lbl);	
					}
				}elseif(method_exists($o,"get_name")){
					if($lbl=$o->get_name()){
						$this->add_item_from_value_lbl($i,$lbl);	
					}
				}
			}else{
				$this->add_item_from_value_lbl($i,$o);
			}
		}
	}
	function add_item_from_value_lbl($value,$lbl=false,$data=array()){
		$item=new mwmod_mw_listmanager_item($value,$lbl,$data);
		return $this->add_item($item);	
	}
	function get_first_item_by_data($cod,$val){
		
		foreach($this->items as $i){
			
			if($i->is_data_val($cod,$val)){
				return $i;	
			}
		}
	}
	function get_item($id){
		if((is_string($id))or(is_numeric($id))){
			return $this->items[$id]??null;
		}
	
		return false;	
		
	}
	function get_item_by_value($id){
		if((is_string($id))or(is_numeric($id))){
			return $this->items_by_value[$id];
		}
	
		return false;	
		
	}
	function get_sel_option_html($value=NULL){
		if($item=$this->get_item_by_value($value)){
			return $item->get_lbl();
		}
		return "";
	}
	
	function get_options_html($value=NULL){
		$r="";
		if($this->has_null){
			$r.="<option value=''></option>\n";	
		}
		foreach ($this->items as $item){
			$r.=	$item->get_options_html($value);
		}
		return $r;
	}
	function add_items_to_js_array($jsarray=false,$codkey="cod",$namekey="name",$extrakeys=false){
		if(!$jsarray){
			$jsarray =new mwmod_mw_jsobj_array();
		}
		if($items=$this->get_items_unassoc()){
			foreach($items as $item){
				if($d=$item->get_data_for_js_array($codkey,$namekey,$extrakeys)){
					$jsarray->add_data($d);	
				}
			}
		}
		return $jsarray;
		
	}
	function get_items_unassoc(){
		return $this->items;	
	}
	
	final function _add_items_by_value($items){
		if(!is_array($items)){
			return false;	
		}
		foreach ($items as $id=>$item){
			$this->items_by_value[$id]=$item;	
		}
	}
	final function _add_item($item){
		$index=$item->get_value();
		$this->items[$index]=$item;
		$this->_add_items_by_value($item->get_items_by_value());
		return $item;
		
	}
	function add_item($item){
		return $this->_add_item($item);
	}
}
?>