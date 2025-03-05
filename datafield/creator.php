<?php

class mwmod_mw_datafield_creator extends mw_apsubbaseobj{
	public $items;
	public $frm;
	public $items_pref;
	function __construct(&$items=array()){
		$this->set_mainap();
		$this->init($items);
	}
	function prepare_js_bootstrap($frm){
		if($items=$this->get_items()){
			foreach($items as $item){
				$item->prepare_js_bootstrap($frm);	
			}
		}
	}
	final function init(&$items=array()){
		if(!is_array($items)){
			$items=array();	
		}
		$this->items=$items;
	}
	function get_items(){
		return $this->items;	
	}
	/*
	function get_js_init_code_for_frm($frm=false){
		
		if(is_array($this->items)){
			foreach ($this->items as $i){
				$r[]=$i->get_js_init_code_for_frm($frm);	
			}
			reset($this->items);
			return $r;
		}
	}
	*/
	
	//data
	function set_value($value){
		return $this->set_data($value);	
	}
	function set_data($value){
		if(!is_array($value)){
			$value=array();	
		}
		if(!is_array($this->items)){
			return false;
		}
		foreach ($this->items as $cod=>$item){
			$item->set_value($value[$cod]);	
		}
	}
	//items
	var $btns_group;
	function get_btns_group(){
		if($this->btns_group){
			return $this->btns_group;	
		}
		$this->btns_group=new mwmod_mw_datafield_btnsgroup("_btns");
		$this->add_item($this->btns_group);
		
		return $this->btns_group;
		
	}
	
	function add_btn($btn=false,$cod=false){
		if($btn){
			if(is_string($btn)){
				$lbl=$btn;
				$btn=false;
			}
			if(!is_object($btn)){
				$btn=false;	
			}
		}
		if(!$btn){
			if(!$cod){
				$cod="_btn";	
			}
			$btn= new mwmod_mw_datafield_btn($cod,$lbl);
		}
		if($gr=$this->get_btns_group()){
			$gr->add_item($btn);
			return $btn;
		}
		
	}
	function add_cancel($url=false){
		$lbl=$this->lng_get_msg_txt("cancel","Cancelar");	
		$i=new mwmod_mw_datafield_btn("_cancel",$lbl);
		$btnelem=$i->get_btn_html_elem();
		$btnelem->set_display_mode("warning");
		if($url){
			$btnelem->set_att("onclick","window.location='$url'");	
		}
		return $this->add_btn($i);
		/*
		if($gr=$this->get_btns_group()){
			$gr->add_item($i);	
		}else{
			$this->add_item($i);
		}
		return $i;
		*/
	}
	
	function add_submit($lbl=false){
		if(!$lbl){
			$lbl=$this->lng_get_msg_txt("save","Guardar");	
		}
		$i=new mwmod_mw_datafield_submit("_submit",$lbl);
		if($gr=$this->get_btns_group()){
			$gr->add_item($i);	
		}else{
			$this->add_item($i);
		}
		return $i;
	}
	
	function add_sub_item_by_dot_cod($item,$parentdotcod=false){
		if(!$parentdotcod){
			return $this->add_item($item);
		}
		if(!$pitem=$this->get_or_add_groupitem_by_dot_cod($parentdotcod)){
			return false;	
		}
		return $pitem->add_item($item);
	}
	function get_or_add_groupitem_by_dot_cod($cod){
		if(empty($cod)){
			return false;
		}
		$coda=explode(".",$cod);
		$cod1=array_shift($coda);
		$cod2=false;
		if(sizeof($coda)){
			$cod2=implode(".",$coda);	
		}
		
		if(!$pitem=$this->get_item($cod1)){
			$ni=new mwmod_mw_datafield_group($cod1);
			$pitem=$this->add_item($ni);
		}
		if(!$pitem){
			return false;	
		}
		if(!$cod2){
			return 	$pitem;
		}else{
			return $pitem->get_or_add_groupitem_by_dot_cod($cod2);
		}
		
		
		
	}
	function get_item_by_dot_cod($cod){
		if(empty($cod)){
			return false;
		}
		$coda=explode(".",$cod);
		$cod1=array_shift($coda);
		$cod2=false;
		if(sizeof($coda)){
			$cod2=implode(".",$coda);	
		}
		
		if($i=$this->get_item($cod1)){
			if($cod2){
				return $i->get_item_by_dot_cod($cod2);	
			}
			return $i;
		}
		
	}
	function get_item($cod){
		if(empty($cod)){
			return false;
		}
		if($this->items[$cod]??false){
			return 	$this->items[$cod];
		}
	}

	function add_items($items,$setcreator=true){
		if(!$items){
			return false;
		}
		if(is_a($items,"mwmod_mw_datafield_creator")){
			return 	$this->add_items($items->items,false);
		}
		if(is_array($items)){
			foreach ($items as $cod=>$i){
				if($this->add_item($i,$setcreator)){
					$r[$cod]=$i;	
				}
			}
			return $r;
		}
		
	}

	function add_item($item,$setcreator=true){
		if(!$item){
			return false;
		}
		if(!is_a($item,"mwmod_mw_datafield_datafielabs")){
			return false;	
		}
		if($cod=$item->get_cod()){
			$this->items[$cod]=	$item;
			if($setcreator){
				$this->items[$cod]->set_datafield_creator($this);
			}
			return $this->items[$cod];
		}
	}
	
	
	function get_html_items_names_debug(){
		
		if(is_array($this->items)){
			$r.="<ul>";
			foreach ($this->items as $i){
				
				$r.="<li>".$i->get_html_items_names_debug()."</li>";	
			}
			$r.="</ul>";
		}
		
		return $r;
			
	}

	
	//frm
	function set_frm($frm){
		$this->frm=$frm;
		/*
		if($this->frm->subinterface){
			$this->set_sub_interface($this->frm->subinterface);
		}elseif($this->frm->maininterface){
			$this->set_main_interface($this->frm->maininterface);	
		}
		*/
	}
	
	
	//por verificar
	function add2jsreqclasseslist(&$list){
		if(is_array($this->items)){
			
			foreach ($this->items as $i){
				$i->add2jsreqclasseslist($list);	
			}
			reset($this->items);
			
		}

		
	}
	//js
	function get_js_init_code_for_frm($frm=false){
		
		if(is_array($this->items)){
			foreach ($this->items as $i){
				$r[]=$i->get_js_init_code_for_frm($frm);	
			}
			reset($this->items);
			return $r;
		}
	}

	
	//
	
	function get_frm_field_name_pref_for_children(){
		return $this->items_pref;	
	}
	
	function get_inputs_html(){
	
		if(is_array($this->items)){
			foreach ($this->items as $i){
				$r.=$i->get_full_input_html();	
			}
		}
		
		return $r;
		
	}
	function set_main_interface($maininterface){
		$this->maininterface=$maininterface;
			
	}
	function set_sub_interface($subinterface){
		$this->subinterface=$subinterface;
		$this->set_main_interface($this->subinterface->maininterface);
	}
	function get_input_template(){
		if($this->subinterface){
			if($r=$this->subinterface->get_input_template()){
				return $r;	
			}
		}
		if($this->maininterface){
			if($r=$this->maininterface->get_input_template()){
				return $r;	
			}
		}
		return $this->mainap->get_input_template();
	}

}
?>