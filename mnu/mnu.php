<?php

class mwmod_mw_mnu_mnu extends mw_apsubbaseobj{
	private $items=array();
	var $cod;
	public $elemsIDpref="MNU";
	var $vertical=false;
	var $allow_sub_menus=false;
	function __construct($cod="mnu"){
		$this->set_mainap();	
		$this->set_cod($cod);	
	}
	function addToHtmlTopNavULSbadmin($navUL){
		if($items=$this->get_items_allowed()){
			
			foreach ($items as $cod=>$item){
				$item->addToHtmlTopNavULSbadmin($navUL,0);
			}
		}
	}
	function addToHtmlSideNav($navDIV,$level=0){
		if($items=$this->get_items_allowed()){
			foreach ($items as $cod=>$item){
				$item->addToHtmlSideNav($navDIV,$level);
			}
		}
		
	}

	function getElemID($cod=""){
		$c=$this->cod;
		if($cod){
			$c.="-".$cod;	
		}
		return $this->elemsIDpref."-".$c;
	}

	
	function allow_sub_menus(){
		return $this->allow_sub_menus;	
	}
	function get_debug_data(){
		$r=array();
		$r["class"]=get_class($this);
		if($items=$this->get_items()){
			$r["items"]=array();
			foreach ($items as $cod=>$item){
				$r["items"][$cod]=$item->get_debug_data();
			}
		}
		return $r;
		
			
	}
	var $allow_display_no_items=false;
	function can_display(){
		if($this->allow_display_no_items){
			return true;	
		}
		return $this->get_allowed_items_num();	
	}
	var $html_list_id=false;
	
	function get_html_as_nav_current_ui(){
		if(!$this->can_display()){
			return false;	
		}
		

		$r="<ul class='navbar-nav mr-auto' >\n";
		if($items=$this->get_items_allowed()){
			foreach ($items as $item){
				
				$r.=$item->get_html_as_navlist_item();	
			}
		}
		
		$r.="</ul>";
		return $r;
			
	}
	
	
	
	function get_html_as_nav($class="nav"){
		if(!$this->can_display()){
			return false;	
		}
		$idhtml="";
		if($id=$this->html_list_id){
			$idhtml=" id='$id' ";
		}
		
		$r="<ul class='$class' $idhtml>\n";
		if($items=$this->get_items_allowed()){
			foreach ($items as $item){
				$r.=$item->get_html_as_nav_child();	
			}
		}
		
		$r.="</ul>";
		return $r;
			
	}
	function set_cod($cod="mnu"){
		if(!$cod){
			$cod="mnu";	
		}
		$this->cod=$cod;
	}
	function get_html_as_topbar_inner(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		$r="";
		foreach ($items as $item){
			//$r.="<li>";	
			$r.=$item->get_html_as_topbarlist_item();	
			//$r.="</li>";	
		}
		return $r;
			
	}
	function get_html_as_navlist_inner(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		$r="";
		foreach ($items as $item){
			//$r.="<li>";	
			$r.=$item->get_html_as_navlist_item();	
			//$r.="</li>";	
		}
		return $r;
	}
	function get_html_as_list_inner(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		$r="";
		foreach ($items as $item){
			//$r.="<li>";	
			$r.=$item->get_html_as_list_item();	
			//$r.="</li>";	
		}
		return $r;
	}

	function get_html_for_tbl(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		$r="";
		foreach ($items as $item){
			$r.=$item->get_html_for_tbl();	
		}
		return $r;
	}
	
	function get_html(){
		if(!$items=$this->get_items_allowed()){
			return false;	
		}
		$r="";
		foreach ($items as $item){
			if($this->vertical){
				$r.="<div>";	
			}
			$r.=$item->get_html();	
			if($this->vertical){
				$r.="</div>";	
			}
		}
		return $r;
	}
	function get_allowed_items_num(){
		if(!$items=$this->get_items_allowed()){
			return 0;	
		}
		return sizeof($items);
			
	}
	final function get_items(){
		return $this->items;
	}
	function get_items_allowed(){
		$r=array();
		if(!$items=$this->get_items()){
			return false;	
		}
		$endItem=false;
		
		foreach ($items as $cod=>$item){
			$item->isEnd=false;
			if($item->is_allowed()){
				$r[$cod]=$item;
				$endItem=$item;
			}
		}
		if($endItem){
			$endItem->isEnd=true;
		}
		if(sizeof($r)<1){
			return false;
		}
		return $r;
	}
	function add_new_item($cod,$etq,$url=false){
		if($i=$this->create_item($cod,$etq,$url)){
			return $this->add_item($cod,$i);	
		}
	}
	function add_item_by_item($item){
		return $this->add_item($item->cod,$item);
	}
	final function add_item($cod,$item){
		if(!is_array($this->items)){
			$this->items=array();	
		}
		$this->items[$cod]=$item;
		$item->on_add($this);
		
		return $this->items[$cod];
	}
	function create_item($cod,$etq,$url=false){
		$i=	new mwmod_mw_mnu_mnuitem($cod,$etq,$this,$url);
		return $i;
	}
	function set_auto_target($val=false){
		$this->auto_target=$val;	
	}


}

?>